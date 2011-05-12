<?php

class Unl_Core_Model_Convert_Adapter_Product
    extends Mage_Catalog_Model_Convert_Adapter_Product
{
    /* Overrides the logic of
     * @see Mage_Catalog_Model_Convert_Adapter_Product::load()
     * by adding a source_store filter and auto-applying scope filter
     */
    public function load()
    {
        $attrFilterArray = array();
        $attrFilterArray ['name']           = 'like';
        $attrFilterArray ['sku']            = 'startsWith';
        $attrFilterArray ['type']           = 'eq';
        $attrFilterArray ['attribute_set']  = 'eq';
        $attrFilterArray ['visibility']     = 'eq';
        $attrFilterArray ['status']         = 'eq';
        $attrFilterArray ['price']          = 'fromTo';
        $attrFilterArray ['qty']            = 'fromTo';
        $attrFilterArray ['store_id']       = 'eq';
        $attrFilterArray ['source_store_view'] = 'eq';

        $attrToDb = array(
            'type'          => 'type_id',
            'attribute_set' => 'attribute_set_id'
        );

        $filters = $this->_parseVars();

        if ($qty = $this->getFieldValue($filters, 'qty')) {
            $qtyFrom = isset($qty['from']) ? (float) $qty['from'] : 0;
            $qtyTo   = isset($qty['to']) ? (float) $qty['to'] : 0;

            $qtyAttr = array();
            $qtyAttr['alias']       = 'qty';
            $qtyAttr['attribute']   = 'cataloginventory/stock_item';
            $qtyAttr['field']       = 'qty';
            $qtyAttr['bind']        = 'product_id=entity_id';
            $qtyAttr['cond']        = "{{table}}.qty between '{$qtyFrom}' AND '{$qtyTo}'";
            $qtyAttr['joinType']    = 'inner';

            $this->setJoinField($qtyAttr);
        }

        parent::setFilter($attrFilterArray, $attrToDb);

        if ($price = $this->getFieldValue($filters, 'price')) {
            $this->_filter[] = array(
                'attribute' => 'price',
                'from'      => $price['from'],
                'to'        => $price['to']
            );
            $this->setJoinAttr(array(
                'alias'     => 'price',
                'attribute' => 'catalog_product/price',
                'bind'      => 'entity_id',
                'joinType'  => 'LEFT'
            ));
        }

        if ($scope = Mage::helper('unl_core')->getAdminUserScope()) {
            $this->_filter[] = array(
                'attribute' => 'source_store_view',
                'in' => $scope
            );
        }

        return Mage_Eav_Model_Convert_Adapter_Entity::load();
    }

    /* Overrides the logic of
     * @see Mage_Catalog_Model_Convert_Adapter_Product::saveRow()
     * by adding product link support, source values matching, and scope checks
     */
    public function saveRow(array $importData)
    {
        $product = $this->getProductModel()
            ->reset();

        if (empty($importData['store'])) {
            if (!is_null($this->getBatchParams('store'))) {
                $store = $this->getStoreById($this->getBatchParams('store'));
            } else {
                $message = Mage::helper('catalog')->__(
                    'Skipping import row, required field "%s" is not defined.',
                    'store'
                );
                Mage::throwException($message);
            }
        }
        else {
            $store = $this->getStoreByCode($importData['store']);
        }

        if ($store === false) {
            $message = Mage::helper('catalog')->__(
                'Skipping import row, store "%s" field does not exist.',
                $importData['store']
            );
            Mage::throwException($message);
        }

        if (empty($importData['sku'])) {
            $message = Mage::helper('catalog')->__('Skipping import row, required field "%s" is not defined.', 'sku');
            Mage::throwException($message);
        }
        $product->setStoreId($store->getId());
        $productId = $product->getIdBySku($importData['sku']);

        if ($productId) {
            $product->load($productId);
            // adds additional scope check
            if (!Mage::helper('unl_core')->isAdminUserAllowedProductEdit($product)) {
                $message = Mage::helper('catalog')->__('Skipping import row, permission denied');
                Mage::throwException($message);
            }
        }
        else {
            $productTypes = $this->getProductTypes();
            $productAttributeSets = $this->getProductAttributeSets();

            /**
             * Check product define type
             */
            if (empty($importData['type']) || !isset($productTypes[strtolower($importData['type'])])) {
                $value = isset($importData['type']) ? $importData['type'] : '';
                $message = Mage::helper('catalog')->__(
                    'Skip import row, is not valid value "%s" for field "%s"',
                    $value,
                    'type'
                );
                Mage::throwException($message);
            }
            $product->setTypeId($productTypes[strtolower($importData['type'])]);
            /**
             * Check product define attribute set
             */
            if (empty($importData['attribute_set']) || !isset($productAttributeSets[$importData['attribute_set']])) {
                $value = isset($importData['attribute_set']) ? $importData['attribute_set'] : '';
                $message = Mage::helper('catalog')->__(
                    'Skip import row, the value "%s" is invalid for field "%s"',
                    $value,
                    'attribute_set'
                );
                Mage::throwException($message);
            }
            $product->setAttributeSetId($productAttributeSets[$importData['attribute_set']]);

            foreach ($this->_requiredFields as $field) {
                $attribute = $this->getAttribute($field);
                if (!isset($importData[$field]) && $attribute && $attribute->getIsRequired()) {
                    $message = Mage::helper('catalog')->__(
                        'Skipping import row, required field "%s" for new products is not defined.',
                        $field
                    );
                    Mage::throwException($message);
                }
            }
        }

        $this->setProductTypeInstance($product);

        /**
         * Handle configurable products and linking of products
         */
        if (isset($importData['type']) && $importData['type'] == 'configurable') {
            $product->setCanSaveConfigurableAttributes(true);
            $configAttributeCodes = $this->userCSVDataAsArray($importData['config_attributes']);
            $usingAttributeIds = array();
            foreach($configAttributeCodes as $attributeCode) {
                $attribute = $product->getResource()->getAttribute($attributeCode);
                if ($product->getTypeInstance()->canUseAttribute($attribute)) {
                    if (!$productId) {
                        $usingAttributeIds[] = $attribute->getAttributeId();
                    }
                }
            }
            if (!empty($usingAttributeIds)) {
                $product->getTypeInstance()->setUsedProductAttributeIds($usingAttributeIds);
                $attributes_array = $product->getTypeInstance()->getConfigurableAttributesAsArray();
                foreach ($attributes_array as $key => $attribute_value) {
                    $attributes_array[$key]['label'] = $attribute_value['frontend_label'];
                }
                $product->setConfigurableAttributesData($attributes_array);
                $product->setCanSaveConfigurableAttributes(true);
                $product->setCanSaveCustomOptions(true);
            }
            if (isset($importData['associated'])) {
                $product->setConfigurableProductsData($this->skusToIds($importData['associated'], $product));
            }
        }

        /**
         * Init product links data (related, upsell, crosssell, grouped)
         */
        if (isset($importData['related'])) {
            $linkIds = $this->skusToIds($importData['related'], $product);
            if (!empty($linkIds)) {
                $product->setRelatedLinkData($linkIds);
            }
        }
        if (isset($importData['upsell'])) {
            $linkIds = $this->skusToIds($importData['upsell'], $product);
            if (!empty($linkIds)) {
                $product->setUpSellLinkData($linkIds);
            }
        }
        if (isset($importData['crosssell'])) {
            $linkIds = $this->skusToIds($importData['crosssell'], $product);
            if (!empty($linkIds)) {
                $product->setCrossSellLinkData($linkIds);
            }
        }
        if (isset($importData['grouped'])) {
            $linkIds = $this->skusToIds($importData['grouped'], $product);
            if (!empty($linkIds)) {
                $product->setGroupedLinkData($linkIds);
            }
        }

        if (isset($importData['category_ids'])) {
            $product->setCategoryIds($importData['category_ids']);
        }

        foreach ($this->_ignoreFields as $field) {
            if (isset($importData[$field])) {
                unset($importData[$field]);
            }
        }

        if ($store->getId() != 0) {
            $websiteIds = $product->getWebsiteIds();
            if (!is_array($websiteIds)) {
                $websiteIds = array();
            }
            if (!in_array($store->getWebsiteId(), $websiteIds)) {
                $websiteIds[] = $store->getWebsiteId();
            }
            $product->setWebsiteIds($websiteIds);
        }

        if (isset($importData['websites'])) {
            $websiteIds = $product->getWebsiteIds();
            if (!is_array($websiteIds) || !$store->getId()) {
                $websiteIds = array();
            }
            $websiteCodes = explode(',', $importData['websites']);
            foreach ($websiteCodes as $websiteCode) {
                try {
                    $website = Mage::app()->getWebsite(trim($websiteCode));
                    if (!in_array($website->getId(), $websiteIds)) {
                        $websiteIds[] = $website->getId();
                    }
                }
                catch (Exception $e) {}
            }
            $product->setWebsiteIds($websiteIds);
            unset($websiteIds);
        }

        foreach ($importData as $field => $value) {
            if (in_array($field, $this->_inventoryFields)) {
                continue;
            }
            if (is_null($value)) {
                continue;
            }

            $attribute = $this->getAttribute($field);
            if (!$attribute) {
                continue;
            }

            $isArray = false;
            $setValue = $value;

            if ($attribute->getFrontendInput() == 'multiselect') {
                $value = explode(self::MULTI_DELIMITER, $value);
                $isArray = true;
                $setValue = array();
            }

            if ($value && $attribute->getBackendType() == 'decimal') {
                $setValue = $this->getNumber($value);
            }

            if ($attribute->usesSource()) {
                $options = $attribute->getSource()->getAllOptions(false);

                if ($isArray) {
                    foreach ($options as $item) {
                        // adds support for optgroups and value matching
                        if (is_array($item['value'])) {
                            foreach ($item['value'] as $subitem) {
                                if (in_array($subitem['label'], $value) || in_array($subitem['value'], $value)) {
                                    $setValue[] = $subitem['value'];
                                }
                            }
                        } else {
                            if (in_array($item['label'], $value) || in_array($item['value'], $value)) {
                                $setValue[] = $item['value'];
                            }
                        }
                    }
                } else {
                    $setValue = false;
                    foreach ($options as $item) {
                        // adds support for optgroups and value matching
                        if (is_array($item['value'])) {
                            foreach ($item['value'] as $subitem) {
                                if ($subitem['label'] == $value || $subitem['value'] == $value) {
                                    $setValue = $subitem['value'];
                                }
                            }
                        } else {
                            if ($item['label'] == $value || $item['value'] == $value) {
                                $setValue = $item['value'];
                            }
                        }
                    }
                }
            }

            $product->setData($field, $setValue);
        }

        if (!$product->getVisibility()) {
            $product->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE);
        }

        $stockData = array();
        $inventoryFields = isset($this->_inventoryFieldsProductTypes[$product->getTypeId()])
            ? $this->_inventoryFieldsProductTypes[$product->getTypeId()]
            : array();
        foreach ($inventoryFields as $field) {
            if (isset($importData[$field])) {
                if (in_array($field, $this->_toNumber)) {
                    $stockData[$field] = $this->getNumber($importData[$field]);
                }
                else {
                    $stockData[$field] = $importData[$field];
                }
            }
        }
        $product->setStockData($stockData);

        $mediaGalleryBackendModel = $this->getAttribute('media_gallery')->getBackend();

        $arrayToMassAdd = array();

        foreach ($product->getMediaAttributes() as $mediaAttributeCode => $mediaAttribute) {
            if (isset($importData[$mediaAttributeCode])) {
                $file = trim($importData[$mediaAttributeCode]);
                if (!empty($file) && !$mediaGalleryBackendModel->getImage($product, $file)) {
                    $arrayToMassAdd[] = array('file' => trim($file), 'mediaAttribute' => $mediaAttributeCode);
                }
            }
        }

        $addedFilesCorrespondence = $mediaGalleryBackendModel->addImagesWithDifferentMediaAttributes(
            $product,
            $arrayToMassAdd, Mage::getBaseDir('media') . DS . 'import',
            false,
            false
        );

        foreach ($product->getMediaAttributes() as $mediaAttributeCode => $mediaAttribute) {
            $addedFile = '';
            if (isset($importData[$mediaAttributeCode . '_label'])) {
                $fileLabel = trim($importData[$mediaAttributeCode . '_label']);
                if (isset($importData[$mediaAttributeCode])) {
                    $keyInAddedFile = array_search($importData[$mediaAttributeCode],
                        $addedFilesCorrespondence['alreadyAddedFiles']);
                    if ($keyInAddedFile !== false) {
                        $addedFile = $addedFilesCorrespondence['alreadyAddedFilesNames'][$keyInAddedFile];
                    }
                }

                if (!$addedFile) {
                    $addedFile = $product->getData($mediaAttributeCode);
                }
                if ($fileLabel && $addedFile) {
                    $mediaGalleryBackendModel->updateImage($product, $addedFile, array('label' => $fileLabel));
                }
            }
        }

        $product->setIsMassupdate(true);
        $product->setExcludeUrlRewrite(true);

        $product->save();

        return true;
    }

    protected function userCSVDataAsArray($data) {
        return explode(',', str_replace(" ", "", $data));
    }

    protected function skusToIds($userData,$product) {
        $productIds = array();
        foreach ($this->userCSVDataAsArray($userData) as $oneSku) {
            if (($a_sku = (int)$product->getIdBySku($oneSku)) > 0) {
                parse_str("position=", $productIds[$a_sku]);
            }
        }
        return $productIds;
    }

}

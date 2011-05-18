<?php

class Unl_Core_Model_ImportExport_Import_Entity_Product extends Mage_ImportExport_Model_Import_Entity_Product
{
    const ERROR_ACCESS_DENIED = 'accessDenied';

    const COL_SOURCE_STORE = 'source_store_view';

    public function __construct()
    {
        parent::__construct();

        $this->_messageTemplates[self::ERROR_ACCESS_DENIED] = 'Access denied for product';
    }

    /* Overrides
     * @see Mage_ImportExport_Model_Import_Entity_Product::_initSkus()
     * by adding source store to sku array
     */
    protected function _initSkus()
    {
        $collection = Mage::getResourceModel('catalog/product_collection')
            ->addAttributeToSelect('source_store_view');
        foreach ($collection as $product) {
            $typeId = $product->getTypeId();
            $sku = $product->getSku();
            $this->_oldSku[$sku] = array(
                'type_id'           => $typeId,
                'attr_set_id'       => $product->getAttributeSetId(),
                'entity_id'         => $product->getId(),
                'supported_type'    => isset($this->_productTypeModels[$typeId]),
                'source_store_view' => $product->getSouceStoreView(),
            );
        }
        return $this;
    }

    /* Overrides
     * @see Mage_ImportExport_Model_Import_Entity_Product::validateRow()
     * by adding permissions checks
     */
    public function validateRow(array $rowData, $rowNum)
    {
        static $sku = null; // SKU is remembered through all product rows

        if (isset($this->_validatedRows[$rowNum])) { // check that row is already validated
            return !isset($this->_invalidRows[$rowNum]);
        }
        $this->_validatedRows[$rowNum] = true;

        if (isset($this->_newSku[$rowData[self::COL_SKU]])) {
            $this->addRowError(self::ERROR_DUPLICATE_SKU, $rowNum);
            return false;
        }
        $rowScope = $this->getRowScope($rowData);
        $userScope = Mage::helper('unl_core')->getAdminUserScope();

        // BEHAVIOR_DELETE use specific validation logic
        if (Mage_ImportExport_Model_Import::BEHAVIOR_DELETE == $this->getBehavior()) {
            if (self::SCOPE_DEFAULT == $rowScope) {
                if (!isset($this->_oldSku[$rowData[self::COL_SKU]])) {
                    $this->addRowError(self::ERROR_SKU_NOT_FOUND_FOR_DELETE, $rowNum);
                    return false;
                } elseif ($userScope && !in_array($this->_oldSku[$rowData[self::COL_SKU]][self::COL_SOURCE_STORE], $userScope)) {
                    $this->addRowError(self::ERROR_ACCESS_DENIED, $rowNum);
                    return false;
                }
            }
            return true;
        }
        // common validation
        $this->_isProductWebsiteValid($rowData, $rowNum);
        $this->_isProductCategoryValid($rowData, $rowNum);
        $this->_isTierPriceValid($rowData, $rowNum);

        if (self::SCOPE_DEFAULT == $rowScope) { // SKU is specified, row is SCOPE_DEFAULT, new product block begins
            $this->_processedEntitiesCount ++;

            $sku = $rowData[self::COL_SKU];

            if (isset($this->_oldSku[$sku])) { // can we get all necessary data from existant DB product?
                // check for supported type of existing product
                if (isset($this->_productTypeModels[$this->_oldSku[$sku]['type_id']])) {
                    // check for permissions
                    if ($userScope && !in_array($this->_oldSku[$sku][self::COL_SOURCE_STORE], $userScope)) {
                        $this->addRowError(self::ERROR_ACCESS_DENIED, $rowNum);
                        $sku = false;
                    } else {
                        $this->_newSku[$sku] = array(
                            'entity_id'     => $this->_oldSku[$sku]['entity_id'],
                            'type_id'       => $this->_oldSku[$sku]['type_id'],
                            'attr_set_id'   => $this->_oldSku[$sku]['attr_set_id'],
                            'attr_set_code' => $this->_attrSetIdToName[$this->_oldSku[$sku]['attr_set_id']]
                        );
                    }
                } else {
                    $this->addRowError(self::ERROR_TYPE_UNSUPPORTED, $rowNum);
                    $sku = false; // child rows of legacy products with unsupported types are orphans
                }
            } else { // validate new product type and attribute set
                if (!isset($rowData[self::COL_TYPE])
                    || !isset($this->_productTypeModels[$rowData[self::COL_TYPE]])
                ) {
                    $this->addRowError(self::ERROR_INVALID_TYPE, $rowNum);
                } elseif (!isset($rowData[self::COL_ATTR_SET])
                          || !isset($this->_attrSetNameToId[$rowData[self::COL_ATTR_SET]])
                ) {
                    $this->addRowError(self::ERROR_INVALID_ATTR_SET, $rowNum);
                } elseif (!isset($this->_newSku[$sku])) {
                    $this->_newSku[$sku] = array(
                        'entity_id'     => null,
                        'type_id'       => $rowData[self::COL_TYPE],
                        'attr_set_id'   => $this->_attrSetNameToId[$rowData[self::COL_ATTR_SET]],
                        'attr_set_code' => $rowData[self::COL_ATTR_SET]
                    );
                }
                if (isset($this->_invalidRows[$rowNum])) {
                    // mark SCOPE_DEFAULT row as invalid for future child rows if product not in DB already
                    $sku = false;
                }
            }
        } else {
            if (null === $sku) {
                $this->addRowError(self::ERROR_SKU_IS_EMPTY, $rowNum);
            } elseif (false === $sku) {
                $this->addRowError(self::ERROR_ROW_IS_ORPHAN, $rowNum);
            } elseif (self::SCOPE_STORE == $rowScope && !isset($this->_storeCodeToId[$rowData[self::COL_STORE]])) {
                $this->addRowError(self::ERROR_INVALID_STORE, $rowNum);
            }
        }
        if (!isset($this->_invalidRows[$rowNum])) {
            // set attribute set code into row data for followed attribute validation in type model
            $rowData[self::COL_ATTR_SET] = $this->_newSku[$sku]['attr_set_code'];

            $rowAttributesValid = $this->_productTypeModels[$this->_newSku[$sku]['type_id']]->isRowValid(
                $rowData, $rowNum, !isset($this->_oldSku[$sku])
            );
            if (!$rowAttributesValid && self::SCOPE_DEFAULT == $rowScope && !isset($this->_oldSku[$sku])) {
                $sku = false; // mark SCOPE_DEFAULT row as invalid for future child rows if product not in DB already
            }
        }
        return !isset($this->_invalidRows[$rowNum]);
    }
}

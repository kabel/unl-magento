<?php

class Unl_Core_Model_Bundle_Product_Type extends Mage_Bundle_Model_Product_Type
{
/**
     * Initialize product(s) for add to cart process
     *
     * @param   Varien_Object $buyRequest
     * @param Mage_Catalog_Model_Product $product
     * @return  unknown
     */
    public function prepareForCart(Varien_Object $buyRequest, $product = null)
    {
        $result = Mage_Catalog_Model_Product_Type_Abstract::prepareForCart($buyRequest, $product);

        if (is_string($result)) {
            return $result;
        }

        $selections = array();

        $product = $this->getProduct($product);

        $_appendAllSelections = false;
        if ($product->getSkipCheckRequiredOption()) {
            $_appendAllSelections = true;
        }

        if ($options = $buyRequest->getBundleOption()) {
            $qtys = $buyRequest->getBundleOptionQty();
            foreach ($options as $_optionId => $_selections) {
                if (empty($_selections)) {
                    unset($options[$_optionId]);
                }
            }
            $optionIds = array_keys($options);

            if (empty($optionIds)) {
                return Mage::helper('bundle')->__('Please select options for product.');
            }

            //$optionsCollection = $this->getOptionsByIds($optionIds, $product);
            $product->getTypeInstance(true)->setStoreFilter($product->getStoreId(), $product);
            $optionsCollection = $this->getOptionsCollection($product);
            if (!$this->getProduct($product)->getSkipCheckRequiredOption()) {
                foreach ($optionsCollection->getItems() as $option) {
                    if ($option->getRequired() && !isset($options[$option->getId()])) {
                        return Mage::helper('bundle')->__('Required options not selected.');
                    }
                }
            }
            $selectionIds = array();

            foreach ($options as $optionId => $selectionId) {
                if (!is_array($selectionId)) {
                    if ($selectionId != '') {
                        $selectionIds[] = $selectionId;
                    }
                } else {
                    foreach ($selectionId as $id) {
                        if ($id != '') {
                            $selectionIds[] = $id;
                        }
                    }
                }
            }

            $selections = $this->getSelectionsByIds($selectionIds, $product);

            /**
             * checking if selections that where added are still on sale
             */
            foreach ($selections->getItems() as $key => $selection) {
                if (!$selection->isSalable()) {
                    $_option = $optionsCollection->getItemById($selection->getOptionId());
                    if (is_array($options[$_option->getId()]) && count($options[$_option->getId()]) > 1){
                        $moreSelections = true;
                    } else {
                        $moreSelections = false;
                    }
                    if ($_option->getRequired() && (!$_option->isMultiSelection() || ($_option->isMultiSelection() && !$moreSelections))) {
                        return Mage::helper('bundle')->__('Selected required options not available.');
                    }
                }
            }

            $optionsCollection->appendSelections($selections, false, $_appendAllSelections);

            $selections = $selections->getItems();
        } else {
            $product->getTypeInstance(true)->setStoreFilter($product->getStoreId(), $product);

            $optionCollection = $product->getTypeInstance(true)->getOptionsCollection($product);

            $optionIds = $product->getTypeInstance(true)->getOptionsIds($product);
            $selectionIds = array();

            $selectionCollection = $product->getTypeInstance(true)
                ->getSelectionsCollection(
                    $product->getTypeInstance(true)->getOptionsIds($product),
                    $product
                );

            $options = $optionCollection->appendSelections($selectionCollection, false, $_appendAllSelections);

            foreach ($options as $option) {
                if ($option->getRequired() && count($option->getSelections()) == 1) {
                    $selections = array_merge($selections, $option->getSelections());
                } else {
                    $selections = array();
                    break;
                }
            }
        }
        if (count($selections) > 0) {
            $uniqueKey = array($product->getId());
            $selectionIds = array();

            /*
             * shaking selection array :) by option position
             */
            usort($selections, array($this, "shakeSelections"));

            foreach ($selections as $selection) {
                if ($selection->getSelectionCanChangeQty() && isset($qtys[$selection->getOptionId()])) {
                    if (is_array($qtys[$selection->getOptionId()])) {
                        if (isset($qtys[$selection->getOptionId()][$selection->getSelectionId()])) {
                            $qty = $qtys[$selection->getOptionId()][$selection->getSelectionId()] > 0 ? $qtys[$selection->getOptionId()][$selection->getSelectionId()] : 1;
                        } else {
                            $qty = $selection->getSelectionQty() ? $selection->getSelectionQty() : 1;
                        }
                    } else {
                        $qty = $qtys[$selection->getOptionId()] > 0 ? $qtys[$selection->getOptionId()] : 1;
                    }
                    
                } else {
                    $qty = $selection->getSelectionQty() ? $selection->getSelectionQty() : 1;
                }

                $product->addCustomOption('selection_qty_' . $selection->getSelectionId(), $qty, $selection);
                $selection->addCustomOption('selection_id', $selection->getSelectionId());

                if ($customOption = $product->getCustomOption('product_qty_' . $selection->getId())) {
                    $customOption->setValue($customOption->getValue() + $qty);
                } else {
                    $product->addCustomOption('product_qty_' . $selection->getId(), $qty, $selection);
                }

                /*
                 * creating extra attributes that will be converted
                 * to product options in order item
                 * for selection (not for all bundle)
                 */
                $price = $product->getPriceModel()->getSelectionPrice($product, $selection, $qty);
                $attributes = array(
                    'price' => Mage::app()->getStore()->convertPrice($price),
                    'qty' => $qty,
                    'option_label' => $selection->getOption()->getTitle(),
                    'option_id' => $selection->getOption()->getId()
                );

                //if (!$product->getPriceType()) {
                $_result = $selection->getTypeInstance(true)->prepareForCart($buyRequest, $selection);
                if (is_string($_result) && !is_array($_result)) {
                    return $_result;
                }

                if (!isset($_result[0])) {
                    return Mage::helper('checkout')->__('Can not add item to shopping cart');
                }

                $result[] = $_result[0]->setParentProductId($product->getId())
                    ->addCustomOption('bundle_option_ids', serialize($optionIds))
                    ->addCustomOption('bundle_selection_attributes', serialize($attributes))
                    ->setCartQty($qty);
                //}

                $selectionIds[] = $_result[0]->getSelectionId();
                $uniqueKey[] = $_result[0]->getSelectionId();
                $uniqueKey[] = $qty;
            }
            /**
             * "unique" key for bundle selection and add it to selections and bundle for selections
             */
            $uniqueKey = implode('_', $uniqueKey);
            foreach ($result as $item) {
                $item->addCustomOption('bundle_identity', $uniqueKey);
            }
            $product->addCustomOption('bundle_option_ids', serialize($optionIds));
            $product->addCustomOption('bundle_selection_ids', serialize($selectionIds));

            return $result;
        }
        return $this->getSpecifyOptionMessage();
    }
}
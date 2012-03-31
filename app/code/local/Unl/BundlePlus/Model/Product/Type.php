<?php

class Unl_BundlePlus_Model_Product_Type extends Mage_Bundle_Model_Product_Type
{
    /* Overrides
     * @see Mage_Bundle_Model_Product_Type::_prepareProduct($buyRequest, $product, $processMode)
     * by allowing each bundle option selection to have a user-defined qty
     */
protected function _prepareProduct(Varien_Object $buyRequest, $product, $processMode)
    {
        // completely bypass the parent:: call
        $result = Mage_Catalog_Model_Product_Type_Abstract::_prepareProduct($buyRequest, $product, $processMode);

        if (is_string($result)) {
            return $result;
        }

        $selections = array();
        $product = $this->getProduct($product);
        $isStrictProcessMode = $this->_isStrictProcessMode($processMode);
        $_appendAllSelections = (bool)$product->getSkipCheckRequiredOption();

        $options = $buyRequest->getBundleOption();
        if (is_array($options)) {
            $options = array_filter($options, 'intval');
            $qtys = $buyRequest->getBundleOptionQty();
            foreach ($options as $_optionId => $_selections) {
                if (empty($_selections)) {
                    unset($options[$_optionId]);
                }
            }
            $optionIds = array_keys($options);

            if (empty($optionIds) && $isStrictProcessMode) {
                return Mage::helper('bundle')->__('Please select options for product.');
            }

            $product->getTypeInstance(true)->setStoreFilter($product->getStoreId(), $product);
            $optionsCollection = $this->getOptionsCollection($product);
            if (!$this->getProduct($product)->getSkipCheckRequiredOption() && $isStrictProcessMode) {
                foreach ($optionsCollection->getItems() as $option) {
                    if ($option->getRequired() && !isset($options[$option->getId()])) {
                        return Mage::helper('bundle')->__('Required options are not selected.');
                    }
                }
            }
            $selectionIds = array();

            foreach ($options as $optionId => $selectionId) {
                if (!is_array($selectionId)) {
                    if ($selectionId != '') {
                        $selectionIds[] = (int)$selectionId;
                    }
                } else {
                    foreach ($selectionId as $id) {
                        if ($id != '') {
                            $selectionIds[] = (int)$id;
                        }
                    }
                }
            }
            // If product has not been configured yet then $selections array should be empty
            if (!empty($selectionIds)) {
                $selections = $this->getSelectionsByIds($selectionIds, $product);

                // Check if added selections are still on sale
                foreach ($selections->getItems() as $key => $selection) {
                    if (!$selection->isSalable()) {
                        $_option = $optionsCollection->getItemById($selection->getOptionId());
                        if (is_array($options[$_option->getId()]) && count($options[$_option->getId()]) > 1) {
                            $moreSelections = true;
                        } else {
                            $moreSelections = false;
                        }
                        if ($_option->getRequired()
                            && (!$_option->isMultiSelection() || ($_option->isMultiSelection() && !$moreSelections))
                        ) {
                            return Mage::helper('bundle')->__('Selected required options are not available.');
                        }
                    }
                }

                $optionsCollection->appendSelections($selections, false, $_appendAllSelections);

                $selections = $selections->getItems();
            } else {
                $selections = array();
            }
        } else {
            $product->setOptionsValidationFail(true);
            $product->getTypeInstance(true)->setStoreFilter($product->getStoreId(), $product);

            $optionCollection = $product->getTypeInstance(true)->getOptionsCollection($product);

            $optionIds = $product->getTypeInstance(true)->getOptionsIds($product);
            $selectionIds = array();

            $selectionCollection = $product->getTypeInstance(true)
                ->getSelectionsCollection(
                    $optionIds,
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
        if (count($selections) > 0 || !$isStrictProcessMode) {
            $uniqueKey = array($product->getId());
            $selectionIds = array();

            // Shuffle selection array by option position
            usort($selections, array($this, 'shakeSelections'));

            foreach ($selections as $selection) {
                if ($selection->getSelectionCanChangeQty() && isset($qtys[$selection->getOptionId()])) {
                    // allow options to have multiple qtys (for each selection)
                    if (is_array($qtys[$selection->getOptionId()])) {
                        if (isset($qtys[$selection->getOptionId()][$selection->getSelectionId()])) {
                            $qty = (float)$qtys[$selection->getOptionId()][$selection->getSelectionId()] > 0
                                ? $qtys[$selection->getOptionId()][$selection->getSelectionId()] : 1;
                        } else {
                            $qty = (float)$selection->getSelectionQty() ? $selection->getSelectionQty() : 1;
                        }
                    } else {
                        // original logic
                        $qty = (float)$qtys[$selection->getOptionId()] > 0 ? $qtys[$selection->getOptionId()] : 1;
                    }

                } else {
                    $qty = (float)$selection->getSelectionQty() ? $selection->getSelectionQty() : 1;
                }
                $qty = (float)$qty;

                $product->addCustomOption('selection_qty_' . $selection->getSelectionId(), $qty, $selection);
                $selection->addCustomOption('selection_id', $selection->getSelectionId());

                $beforeQty = 0;
                $customOption = $product->getCustomOption('product_qty_' . $selection->getId());
                if ($customOption) {
                    $beforeQty = (float)$customOption->getValue();
                }
                $product->addCustomOption('product_qty_' . $selection->getId(), $qty + $beforeQty, $selection);

                /*
                 * Create extra attributes that will be converted to product options in order item
                 * for selection (not for all bundle)
                 */
                $price = $product->getPriceModel()->getSelectionPrice($product, $selection, $qty);
                $attributes = array(
                    'price'         => Mage::app()->getStore()->convertPrice($price),
                    'qty'           => $qty,
                    'option_label'  => $selection->getOption()->getTitle(),
                    'option_id'     => $selection->getOption()->getId()
                );

                $_result = $selection->getTypeInstance(true)->prepareForCart($buyRequest, $selection);
                if (is_string($_result) && !is_array($_result)) {
                    return $_result;
                }

                if (!isset($_result[0])) {
                    return Mage::helper('checkout')->__('Cannot add item to the shopping cart.');
                }

                $result[] = $_result[0]->setParentProductId($product->getId())
                    ->addCustomOption('bundle_option_ids', serialize(array_map('intval', $optionIds)))
                    ->addCustomOption('bundle_selection_attributes', serialize($attributes));

                if ($isStrictProcessMode) {
                    $_result[0]->setCartQty($qty);
                }

                $selectionIds[] = $_result[0]->getSelectionId();
                $uniqueKey[] = $_result[0]->getSelectionId();
                $uniqueKey[] = $qty;
            }

            // "unique" key for bundle selection and add it to selections and bundle for selections
            $uniqueKey = implode('_', $uniqueKey);
            foreach ($result as $item) {
                $item->addCustomOption('bundle_identity', $uniqueKey);
            }
            $product->addCustomOption('bundle_option_ids', serialize(array_map('intval', $optionIds)));
            $product->addCustomOption('bundle_selection_ids', serialize($selectionIds));

            return $result;
        }

        return $this->getSpecifyOptionMessage();
    }
}

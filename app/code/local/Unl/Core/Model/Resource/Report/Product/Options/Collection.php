<?php

class Unl_Core_Model_Resource_Report_Product_Options_Collection extends Varien_Data_Collection
{
    /**
     * @var Mage_Sales_Model_Resource_Order_Item_Collection
     */
    protected $internalCollection;

    protected $internalFields = array();

    protected $loadedOptions = array();

    protected $loadedBundleOptions = array();

    public function __construct()
    {
        parent::__construct();

        $product = Mage::registry('current_product');

        $itemCollection = Mage::getResourceModel('sales/order_item_collection');
        $itemCollection->addFilterToMap('order_number', 'order.increment_id');
        $itemCollection->addFilterToMap('order_date', 'order.created_at');
        $itemCollection->addFilterToMap('order_status', 'order.status');
        $itemCollection->addFieldToFilter('product_id', $product->getId());
        $itemCollection->join(
            array('order' => 'sales/order'),
            "(main_table.order_id = order.entity_id AND order.state != '" . Mage_Sales_Model_Order::STATE_CANCELED ."')",
            array('order_number' => 'increment_id', 'order_date' => 'created_at', 'order_status' => 'status')
        );
        $itemCollection->addFilterToMap('qty_adjusted', '(qty_ordered - qty_canceled)');
        $itemCollection->addExpressionFieldToSelect('qty_adjusted', '({{ordered}} - {{canceled}})', array(
            'ordered'  => 'qty_ordered',
            'canceled' => 'qty_canceled'
        ));
        $itemCollection->addFieldToFilter('qty_adjusted', array('gt' => 0));

        $itemCollection->addOrder('order_date', 'desc');

        $this->internalFields = array(
            'order_number',
            'order_date',
            'order_status',
            'qty_adjusted',
        );

        $this->internalCollection = $itemCollection;
    }

    protected function isInternalField($field)
    {
        return in_array($field, $this->internalFields);
    }

    public function addFieldToFilter($field, $condition = null)
    {
        if ($this->isInternalField($field)) {
            if (!$this->internalCollection->isLoaded()) {
                $this->internalCollection->addFieldToFilter($field, $condition);
            }
        } else {
            $this->addFilter($field, $condition);
        }
    }

    public function clearIncludingFilters()
    {
        $this->_filters = array();
        $this->clear();

        return $this;
    }

    protected function getOrderOptions(Mage_Sales_Model_Order_Item $item)
    {
        $result = array();
        if ($options = $item->getProductOptions()) {
            if (isset($options['options'])) {
                $result = array_merge($result, $options['options']);
            }
            if (isset($options['additional_options'])) {
                $result = array_merge($result, $options['additional_options']);
            }
            if (!empty($options['attributes_info'])) {
                $result = array_merge($options['attributes_info'], $result);
            }
        }
        return $result;
    }

    protected function _renderOrders()
    {
        uasort($this->_items, array($this, 'compareItems'));

        return $this;
    }

    protected function isOptionField($field)
    {
        return strpos($field, 'option_') === 0;
    }

    /**
     * @param string $field
     * @param Varien_Object $item
     * @return mixed
     */
    protected function getFieldOptionValueForFilter($field, $item)
    {
        if (!$this->isOptionField($field)) {
            return $item->getData($field);
        }

        $optionModel = Mage::getModel('catalog/product_option');
        $option = $item->getData($field . '_option');

        if (!$option) {
            return null;
        }

        $value = $option['value'];
        if (isset($option['option_type'])) {
            $optionModel->setType($option['option_type']);
            $groupType = $optionModel->getGroupByType();

            if ($groupType == Mage_Catalog_Model_Product_Option::OPTION_GROUP_DATE) {
                $value = strtotime($option['option_value']);
            } else if ($groupType == Mage_Catalog_Model_Product_Option::OPTION_GROUP_SELECT) {
                $group = $optionModel->groupFactory();
                $group->setOption($optionModel);
                $value = $group->prepareOptionValueForRequest($option['option_value']);
            }
        }

        return $value;
    }

    /**
     * @param string $field
     * @param Varien_Object $item
     * @return mixed
     */
    protected function getFieldOptionValueForCompare($field, $item)
    {
        if (!$this->isOptionField($field)) {
            $value = $item->getData($field);

            if ($field == 'order_date') {
                return strtotime($value);
            }

            return $value;
        }

        $optionModel = Mage::getModel('catalog/product_option');
        $option = $item->getData($field . '_option');

        if (!$option) {
            return null;
        }

        $value = $option['value'];
        if (isset($option['option_type'])) {
            $groupType = $optionModel->getGroupByType($option['option_type']);
            if ($groupType == Mage_Catalog_Model_Product_Option::OPTION_GROUP_DATE) {
                $value = strtotime($option['option_value']);
            }
        }

        return $value;
    }

    protected function _renderFilters()
    {
        $this->_items = array_filter($this->_items, array($this, 'filterItem'));

        return $this;
    }

    /**
     * @param Varien_Object $item
     * @return bool
     */
    protected function filterItem($item)
    {
        foreach ($this->_filters as $filter) {
            $fieldValue = $this->getFieldOptionValueForFilter($filter['field'], $item);
            $condition = $filter['value'];

            if (!$this->isFieldConditionMet($fieldValue, $condition)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param mixed $fieldValue
     * @param mixed $condition
     * @return boolean
     */
    protected function isFieldConditionMet($fieldValue, $condition)
    {
        $conditionKeyMap = array_fill_keys(array(
            'eq',
            'neq',
            'like',
            'nlike',
            'in',
            'nin',
            'is',
            'notnull',
            'null',
            'gt',
            'lt',
            'gteq',
            'lteq',
            'finset',
            'regexp',
            'from',
            'to',
            'seq',
            'sneq',
        ), null);

        if (is_array($condition)) {
            $key = key(array_intersect_key($condition, $conditionKeyMap));

            if (isset($condition['from']) || isset($condition['to'])) {
                $key = 'from';
                if (isset($condition[$key])) {
                    $value = $condition[$key];
                    $from  = $this->prepareDateLikeCondition($condition, $value);

                    if ($fieldValue < $from) {
                        return false;
                    }
                }

                $key = 'to';
                if (isset($condition[$key])) {
                    $value = $condition[$key];
                    $to = $this->prepareDateLikeCondition($condition, $value);

                    if ($fieldValue > $to) {
                        return false;
                    }
                }
            } elseif (array_key_exists($key, $conditionKeyMap)) {
                $value = $condition[$key];

                if (($key == 'seq') || ($key == 'sneq')) {
                    if (!$value) {
                        $key = ($key == 'seq') ? 'null' : 'notnull';
                    } else {
                        $key = ($key == 'seq') ? 'eq' : 'neq';
                    }
                }

                $result = true;
                $applyNot = false;

                switch ($key) {
                    case 'eq':
                    case 'neq':
                    case 'finset':
                        $applyNot = $key == 'neq';

                        if ($key == 'finset' && !is_array($fieldValue)) {
                            $fieldValue = explode(',', $fieldValue);
                        }

                        if (is_array($fieldValue)) {
                            $result = in_array($value, $fieldValue);
                        } else {
                            $result = $fieldValue == $value;
                        }
                        break;
                    case 'like':
                    case 'nlike':
                    case 'regexp':
                        $applyNot = $key == 'nlike';

                        if ($key == 'like' || $key == 'nlike') {
                            $value = preg_quote($value);
                            $value = preg_replace('/(?<!\\\\)%/', '.*', $value);
                            $value = preg_replace('/(?<!\\\\)_/', '.', $value);
                            $value = '/' . $value . '/';
                        } else {
                            $value = '/' . addcslashes($value, '/') . '/';
                        }

                        $result = preg_match($value, $fieldValue);
                        break;
                    case 'in':
                    case 'nin':
                        $applyNot = $key == 'nin';
                        $value = (array) $value;

                        if (is_array($fieldValue)) {
                            $result = false;
                            foreach ($value as $innerValue) {
                                $result = in_array($innerValue, $fieldValue);

                                // if ANY match
                                if ($result) {
                                    break;
                                }
                            }
                        } else {
                            $result = in_array($fieldValue, $value);
                        }
                        break;
                    case 'is':
                    case 'null':
                    case 'notnull':
                        $applyNot = $key == 'notnull';

                        if ($key != 'is') {
                            $value = null;
                        }

                        $result = $fieldValue === $value;
                        break;
                    case 'gt':
                        $result = $fieldValue > $value;
                        break;
                    case 'gteq':
                        $result = $fieldValue >= $value;
                        break;
                    case 'lt':
                        $result = $fieldValue < $value;
                        break;
                    case 'lteq':
                        $result = $fieldValue <= $value;
                        break;
                }

                $result = ($applyNot) ? !$result : $result;

                if (!$result) {
                    return false;
                }
            } else {
                $result = false;
                foreach ($condition as $orCondition) {
                    $result = $this->isFieldConditionMet($fieldValue, $orCondition);

                    // if ANY match
                    if ($result) {
                        break;
                    }
                }

                if (!$result) {
                    return false;
                }
            }
        } else {
            if ($fieldValue != $condition) {
                return false;
            }
        }

        return true;
    }

    /**
     * Converts date and datetime condition values into timestamps.
     * Passes the value for all other conditions.
     *
     * @param array $condition
     * @param mixed $value
     * @return mixed
     */
    protected function prepareDateLikeCondition($condition, $value)
    {
        $result = $value;

        if (!empty($condition['date']) || !empty($condition['datetime'])) {
            $result = Varien_Date::toTimestamp($value);
        }

        return $result;
    }

    /**
     * @param Varien_Object $a
     * @param Varien_Object $b
     */
    protected function compareItems($a, $b)
    {
        foreach ($this->_orders as $field => $dir) {
            $difference = 0;
            $aField = $this->getFieldOptionValueForCompare($field, $a);
            $bField = $this->getFieldOptionValueForCompare($field, $b);

            if ($aField == $bField) {
                continue;
            }

            if ($dir != self::SORT_ORDER_DESC) {
                $difference = ($aField < $bField) ? -1 : 1;
            } else {
                $difference = ($aField > $bField) ? -1 : 1;
            }

            if ($difference) {
                return $difference;
            }
        }

        return 0;
    }

    protected function getExcludedLoadedOptionsByType($type, $excludedOptionIds)
    {
        if (!in_array($type, array(
            'loadedOptions',
            'loadedBundleOptions',
        ))) {
            throw new RuntimeException('Invalid loaded option type');
        }

        $this->load();
        return array_diff_key($this->$type, array_fill_keys($excludeOptionIds, null));

    }

    public function getExcludedLoadedOptions($excludeOptionIds)
    {
        return $this->getExcludedLoadedOptionsByType('loadedOptions', $excludedOptionIds);
    }

    public function getExcludedLoadedBundleOptions($excludeOptionIds)
    {
        return $this->getExcludedLoadedOptionsByType('loadedBundleOptions', $excludedOptionIds);
    }

    protected function addLoadedOption($optionId, $option, $override = false)
    {
        if (isset($this->loadedOptions[$optionId]) && !$override) {
            return $this;
        }

        $this->loadedOptions[$optionId] = $option;

        return $this;
    }

    protected function addLoadedBundleOption($optionId, $option, $override = false)
    {
        if (isset($this->loadedBundleOptions[$optionId]) && !$override) {
            return $this;
        }

        $this->loadedBundleOptions[$optionId] = $option;

        return $this;
    }

    public function clear()
    {
        $this->loadedOptions = array();
        $this->loadedBundleOptions = array();
        return parent::clear();
    }

    public function loadData($printQuery = false, $logQuery = false)
    {
        if ($this->isLoaded()) {
            return $this;
        }

        $this->clear();

        /* @var $item Mage_Sales_Model_Order_Item */
        foreach ($this->internalCollection as $item) {
            $itemOptions = $this->getOrderOptions($item);
            $bundleOptions = array();

            if ($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
                $bundleOptions = $item->getProductOptionByCode('bundle_options');
            }

            if (!$itemOptions && !$bundleOptions) {
                continue;
            }

            $collectionItem = $this->getNewEmptyItem();
            $collectionItem->setData(array(
                'id' => $item->getId(),
                'order_item' => $item,
                'order_id' => $item->getOrderId(),
                'order_date' => $item->getOrderDate(),
                'order_status' => $item->getOrderStatus(),
                'order_number' => $item->getOrderNumber(),
                'status' => $item->getStatusId(),
                'qty_adjusted' => $item->getQtyAdjusted(),
                'base_total' => $item->getBaseRowTotal() + $item->getBaseTaxAmount() + $item->getBaseHiddenTaxAmount() + Mage::helper('weee')->getBaseRowWeeeAmountAfterDiscount($item) - $item->getBaseDiscountAmount(),
            ));

            foreach ($itemOptions as $option) {
                $optionId = isset($option['option_id']) ? $option['option_id'] : Mage::helper('core')->uniqHash();
                $optionValue = $option['value'];

                $this->addLoadedOption($optionId, array_intersect_key($option, array_fill_keys(array(
                    'label',
                    'option_type',
                    'option_id',
                ), null)));

                if (isset($option['option_type'], $option['custom_view']) && $option['custom_view']) {
                    try {
                        $group = Mage::getModel('catalog/product_option')->groupFactory($option['option_type']);
                        $optionValue = $group->getCustomizedView($option);
                    } catch (Exception $e) {}
                }

                $collectionItem->setData('option_' . $optionId, $optionValue);
                $collectionItem->setData('option_' . $optionId . '_option', $option);
            }

            foreach ($bundleOptions as $option) {
                $optionId = $option['option_id'];
                $optionValue = (array) $option['value'];

                $this->addLoadedBundleOption($optionId, array_intersect_key($option, array_fill_keys(array(
                    'label',
                    'option_id',
                ), null)));

                $value = array();
                foreach ($optionValue as $selection) {
                    $value[] = $selection['qty'] . 'x ' . $selection['title'];
                }

                $collectionItem->setData('bundle_option_' . $optionId, implode("\n", $value));
                $collectionItem->setData('bundle_option_' . $optionId . '_option', $option);
            }

            $this->addItem($collectionItem);
        }

        $this
            ->_renderFilters()
            ->_renderOrders();

        $this->_setIsLoaded();

        return $this;
    }
}

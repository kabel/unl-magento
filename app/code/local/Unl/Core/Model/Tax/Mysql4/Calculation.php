<?php

class Unl_Core_Model_Tax_Mysql4_Calculation  extends Mage_Tax_Model_Mysql4_Calculation
{
    /* Overrides the logic of
     * @see Mage_Tax_Model_Mysql4_Calculation::_getRates()
     * to ensure NE addresses have full zip+4
     */
    protected function _getRates($request)
    {
        // Extract params that influence our SELECT statement and use them to create cache key
        $storeId = Mage::app()->getStore($request->getStore())->getId();
        $customerClassId = $request->getCustomerClassId();
        $countryId = $request->getCountryId();
        $regionId = $request->getRegionId();

        // Lookup that Zip+4 for NE addresses
        if (preg_match('/^(\d{5})(?:-(\d{4}))?$/', $request->getPostcode(), $matches)) {
            $address = $request->getFullAddress();
            if (empty($matches[2]) && !empty($address)) {
                $region = Mage::getModel('directory/region');
                $region->load($request->getRegionId());
                if ($region->getCountryId() == 'US' && $region->getCode() == 'NE') {
                    $boundry = Mage::getModel('unl_core/tax_boundary');
                    $zip = $boundry->getZipFromAddress($address);
                    if (!empty($zip)) {
                        $request->setPostcode($zip);
                    }
                }
            }
        }
        $postcode = $request->getPostcode();

        // Process productClassId as it can be array or usual value. Form best key for cache.
        $productClassId = $request->getProductClassId();
        $ids = is_array($productClassId) ? $productClassId : array($productClassId);
        foreach ($ids as $key => $val) {
            $ids[$key] = (int) $val; // Make it integer for equal cache keys even in case of null/false/0 values
        }
        $ids = array_unique($ids);
        sort($ids);
        $productClassKey = implode(',', $ids);

        // Form cache key and either get data from cache or from DB
        $cacheKey = implode('|', array($storeId, $customerClassId, $productClassKey, $countryId, $regionId, $postcode));

        if (!isset($this->_ratesCache[$cacheKey])) {
            // Make SELECT and get data
            $select = $this->_getReadAdapter()->select();
            $select
                ->from(array('main_table'=>$this->getMainTable()))
                ->where('customer_tax_class_id = ?', $customerClassId);
            if ($productClassId) {
                $select->where('product_tax_class_id IN (?)', $productClassId);
            }

            $select->join(
                array('rule'=>$this->getTable('tax/tax_calculation_rule')),
                'rule.tax_calculation_rule_id = main_table.tax_calculation_rule_id',
                array('rule.priority', 'rule.position')
            );
            $select->join(
                array('rate'=>$this->getTable('tax/tax_calculation_rate')),
                'rate.tax_calculation_rate_id = main_table.tax_calculation_rate_id',
                array(
                    'value'=>'rate.rate', 'rate.tax_country_id', 'rate.tax_region_id', 'rate.tax_postcode',
                    'rate.tax_calculation_rate_id', 'rate.code'
                )
            );

            $select->joinLeft(
                array('title_table'=>$this->getTable('tax/tax_calculation_rate_title')),
                "rate.tax_calculation_rate_id = title_table.tax_calculation_rate_id "
                . "AND title_table.store_id = '{$storeId}'",
                array('title'=>'IFNULL(title_table.value, rate.code)')
            );

            $select
                ->where("rate.tax_country_id = ?", $countryId)
                ->where("rate.tax_region_id in ('*', '', ?)", $regionId);

            $selectClone = clone $select;

            $select
                ->where("rate.zip_is_range IS NULL");
            $selectClone
                ->where("rate.zip_is_range IS NOT NULL");

            if ($postcode != '*') {
                $select
                    ->where("rate.tax_postcode in ('*', '', ?)", $this->_createSearchPostCodeTemplates($postcode));
                $selectClone
                    ->where("? BETWEEN rate.zip_from AND rate.zip_to", $postcode);
            }

            /**
             * @see ZF-7592 issue http://framework.zend.com/issues/browse/ZF-7592
             */
            $select = $this->_getReadAdapter()->select()->union(array('(' . $select . ')', '(' . $selectClone . ')'));
            $order = array('priority ASC', 'tax_calculation_rule_id ASC', 'tax_country_id DESC', 'tax_region_id DESC',
                'tax_postcode DESC', 'value DESC'
            );
            $select->order($order);

            $this->_ratesCache[$cacheKey] = $this->_getReadAdapter()->fetchAll($select);
        }

        return $this->_ratesCache[$cacheKey];
    }
}

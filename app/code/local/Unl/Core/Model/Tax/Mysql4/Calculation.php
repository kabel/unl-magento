<?php

class Unl_Core_Model_Tax_Mysql4_Calculation  extends Mage_Tax_Model_Mysql4_Calculation
{
    protected function _getRates($request)
    {
        $storeId = Mage::app()->getStore($request->getStore())->getId();

        $select = $this->_getReadAdapter()->select();
        $select
            ->from(array('main_table'=>$this->getMainTable()))
            ->where('customer_tax_class_id = ?', $request->getCustomerClassId())
            ->where('product_tax_class_id = ?', $request->getProductClassId());

        $select->join(array('rule'=>$this->getTable('tax/tax_calculation_rule')), 'rule.tax_calculation_rule_id = main_table.tax_calculation_rule_id', array('rule.priority', 'rule.position'));
        $select->join(array('rate'=>$this->getTable('tax/tax_calculation_rate')), 'rate.tax_calculation_rate_id = main_table.tax_calculation_rate_id', array('value'=>'rate.rate', 'rate.tax_country_id', 'rate.tax_region_id', 'rate.tax_postcode', 'rate.tax_calculation_rate_id', 'rate.code'));

        $select
            ->where("rate.tax_country_id = ?", $request->getCountryId())
            ->where("rate.tax_region_id in ('*', '', ?)", $request->getRegionId());
        
        $in = array("'*'", "''", "?");
        if (preg_match('/^(\d{5})(?:-(\d{4}))?$/', $request->getPostcode(), $matches)) {
            $in[] = "'{$matches[1]}*'";
            $address = $request->getFullAddress();
            if (empty($matches[2]) && !empty($address)) {
                $region = Mage::getModel('directory/region');
                $region->load($request->getRegionId());
                if ($region->getCountryId() == 'US' && $region->getCode() == 'NE') {
                    $boundry = Mage::getModel('unl_core/tax_boundary');
                    $zip = $boundry->getZipFromAddress($request->getFullAddress());
                    if (!empty($zip)) {
                        $request->setPostcode($zip);
                    }
                }
            }
        }
        $select->where("rate.tax_postcode in (" . implode(',', $in) . ")", $request->getPostcode());

        $select->joinLeft(array('title_table'=>$this->getTable('tax/tax_calculation_rate_title')), "rate.tax_calculation_rate_id = title_table.tax_calculation_rate_id AND title_table.store_id = '{$storeId}'", array('title'=>'IFNULL(title_table.value, rate.code)'));

        $order = array('rule.priority ASC', 'rule.tax_calculation_rule_id ASC', 'rate.tax_country_id DESC', 'rate.tax_region_id DESC', 'rate.tax_postcode DESC', 'rate.rate DESC');
        $select->order($order);

        return $this->_getReadAdapter()->fetchAll($select);
    }
}

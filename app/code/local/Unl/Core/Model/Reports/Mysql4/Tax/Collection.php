<?php

class Unl_Core_Model_Reports_Mysql4_Tax_Collection extends Mage_Reports_Model_Mysql4_Tax_Collection
{
    public function setDateRange($from, $to)
    {
        $this->_reset();

        $this->addAttributeToFilter('created_at', array('from' => $from, 'to' => $to))
            ->addExpressionAttributeToSelect('orders', 'COUNT(DISTINCT({{entity_id}}))', array('entity_id'))
            ->getSelect()
            ->join(array('tax_table' => $this->getTable('sales/order_tax')), 'e.entity_id = tax_table.order_id')
            ->from('', array('code' => "CASE WHEN tax_table.code LIKE '%-CountyFips-%' OR tax_table.code LIKE '%-CityFips-%' THEN CONCAT('US-NE-', RIGHT(tax_table.code, 14)) ELSE tax_table.code END"))
            ->group("CASE WHEN tax_table.code LIKE '%-CountyFips-%' OR tax_table.code LIKE '%-CityFips-%' THEN RIGHT(tax_table.code, 14) ELSE tax_table.code END")
            ->order(array('process', 'priority'));

        return $this;
    }
    
    //TODO: Override setStoreIds() to join order_item and filter on the source_store_view
}
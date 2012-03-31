<?php

/**
 * Overrides
 * @see Mage_Sales_Model_Resource_Report_Order_Updatedat
 * by rewriting the inheritance to UNL's parent class
 */
class Unl_Core_Model_Sales_Resource_Report_Order_Updatedat extends Unl_Core_Model_Sales_Resource_Report_Order_Createdat
{
    /**
     * Model initialization
     *
     */
    protected function _construct()
    {
        $this->_init('sales/order_aggregated_updated', 'id');
    }

    /**
     * Aggregate Orders data by order updated at
     *
     * @param mixed $from
     * @param mixed $to
     * @return Mage_Sales_Model_Resource_Report_Order_Updatedat
     */
    public function aggregate($from = null, $to = null)
    {
        return $this->_aggregateByField('updated_at', $from, $to);
    }
}

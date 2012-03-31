<?php

/**
 * Overrides
 * @see Mage_Tax_Model_Resource_Report_Tax_Updatedat
 * by rewriting the inheritance to UNL's parent class
 */
class Unl_Core_Model_Tax_Resource_Report_Tax_Updatedat extends Unl_Core_Model_Tax_Resource_Report_Tax_Createdat
{
    /**
     * Resource initialization
     */
    protected function _construct()
    {
        $this->_init('tax/tax_order_aggregated_updated', 'id');
    }

    /**
     * Aggregate Tax data by order updated at
     *
     * @param mixed $from
     * @param mixed $to
     * @return Mage_Tax_Model_Resource_Report_Tax_Updatedat
     */
    public function aggregate($from = null, $to = null)
    {
        return $this->_aggregateByOrder('updated_at', $from, $to);
    }
}

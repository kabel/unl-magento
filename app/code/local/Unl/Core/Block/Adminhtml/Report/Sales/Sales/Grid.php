<?php

class Unl_Core_Block_Adminhtml_Report_Sales_Sales_Grid extends Mage_Adminhtml_Block_Report_Sales_Sales_Grid
{
    /* Extends
     * @see Mage_Adminhtml_Block_Report_Sales_Sales_Grid::_prepareColumns()
     * by changing default columns
     */
    protected function _prepareColumns()
    {
        parent::_prepareColumns();

        $this->getColumn('orders_count')->setTotal(false);

        return $this;
    }
}

<?php

class Unl_Core_Block_Adminhtml_Report_Customer_Orderaddress_Grid_Renderer_Address
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $address = $this->_factoryAddress($row);
        return $address->format('html');
    }

    public function renderExport(Varien_Object $row)
    {
        $address = $this->_factoryAddress($row);
        return $address->format('text');
    }

    protected function _factoryAddress(Varien_Object $row)
    {
        $address = Mage::getModel('sales/order_address');
        $address->setData($row->getData());
        return $address;
    }
}

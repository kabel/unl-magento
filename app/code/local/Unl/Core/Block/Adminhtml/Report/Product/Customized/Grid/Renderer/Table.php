<?php

class Unl_Core_Block_Adminhtml_Report_Product_Customized_Grid_Renderer_Table
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        $orderItem = $this->_getOrderItem($row);
        /* @var $template Mage_Adminhtml_Block_Template */
        $template = $this->getLayout()->createBlock('adminhtml/template');
        $template->setTemplate('unl/report/column/table.phtml');
        $template->setItem($orderItem);

        return $template->toHtml();
    }

    public function renderExport(Varien_Object $row)
    {
        $data = array();
        $options = $this->_getOrderItem($row)->getProductOptionByCode('options');
        foreach($options as $option) {
            $data[] = $option['label'];
            $data[] = $option['print_value'];
        }

        return $data;
    }

    protected function _getOrderItem($row)
    {
        $orderItem = Mage::getModel('sales/order_item');
        $orderItem->setData($row->getData());

        return $orderItem;
    }
}

<?php

class Unl_Core_Block_Adminhtml_Report_Product_Customized_Grid_Renderer_Table
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    public function render(Varien_Object $row)
    {
        /* @var $template Mage_Adminhtml_Block_Template */
        $template = $this->getLayout()->createBlock('adminhtml/template');
        $template->setTemplate('unl/report/column/table.phtml');
        $template->setItem($row);

        return $template->toHtml();
    }

    public function renderExport(Varien_Object $row)
    {
        $data = array();
        $options = $row->getProductOptionByCode('options');
        foreach($options as $option) {
            $data[] = $option['label'];
            $data[] = $option['print_value'];
        }

        return $data;
    }
}

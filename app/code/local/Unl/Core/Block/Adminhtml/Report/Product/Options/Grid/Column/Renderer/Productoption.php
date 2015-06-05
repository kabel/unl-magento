<?php

class Unl_Core_Block_Adminhtml_Report_Product_Options_Grid_Column_Renderer_Productoption
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    protected function _getValue(Varien_Object $row)
    {
        $value = parent::_getValue($row);
        return nl2br($value);
    }
}

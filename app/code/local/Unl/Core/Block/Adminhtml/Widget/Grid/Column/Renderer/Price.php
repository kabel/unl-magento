<?php

class Unl_Core_Block_Adminhtml_Widget_Grid_Column_Renderer_Price
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Price
{
    /* Overrides
     * @see Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract::renderExport()
     * by not including currency symbol info
     */
    public function renderExport(Varien_Object $row)
    {
        if ($data = $row->getData($this->getColumn()->getIndex())) {
            $data = floatval($data) * $this->_getRate($row);
            $data = sprintf("%f", $data);
            return $data;
        }
        return $this->getColumn()->getDefault();
    }
}

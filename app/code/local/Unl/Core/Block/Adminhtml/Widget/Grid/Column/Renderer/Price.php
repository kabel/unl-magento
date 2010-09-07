<?php

class Unl_Core_Block_Adminhtml_Widget_Grid_Column_Renderer_Price extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Price
{
    /**
     * Render column for export
     *
     * @param Varien_Object $row
     * @return string
     */
    public function renderExport($row)
    {
        if ($data = $row->getData($this->getColumn()->getIndex())) {
            $currency_code = $this->_getCurrencyCode($row);

            if (!$currency_code) {
                return $data;
            }

            $data = floatval($data) * $this->_getRate($row);
            $data = sprintf("%f", $data);
            return $data;
        }
        return $this->getColumn()->getDefault();
    }
}
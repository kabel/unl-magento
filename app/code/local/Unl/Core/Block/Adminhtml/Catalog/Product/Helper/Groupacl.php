<?php

class Unl_Core_Block_Adminhtml_Catalog_Product_Helper_Groupacl extends Varien_Data_Form_Element_Multiselect
{
	/**
     * Retrieve Element HTML fragment
     *
     * @return string
     */
    public function getElementHtml()
    {
        $disabled = false;
        if (!$this->getValue()) {
            $this->setData('disabled', 'disabled');
            $disabled = true;
        }
        $html = parent::getElementHtml();
        $htmlId = 'use_default_' . $this->getHtmlId();
        $html .= '<input id="'.$htmlId.'" name="use_default[]" value="' . $this->getId() . '"';
        $html .= ($disabled ? ' checked="checked"' : '');

        if ($this->getReadonly()) {
            $html .= ' disabled="disabled"';
        }

        $html .= ' onclick="toggleValueElements(this, this.parentNode);" class="checkbox" type="checkbox"/>';

        $html .= ' <label for="'.$htmlId.'" class="normal">'
            . Mage::helper('adminhtml')->__('Use No Restriction').'</label>';
        $html .= '<script type="text/javascript">toggleValueElements($(\''.$htmlId.'\'), $(\''.$htmlId.'\').parentNode);</script>';

        return $html;
    }
}

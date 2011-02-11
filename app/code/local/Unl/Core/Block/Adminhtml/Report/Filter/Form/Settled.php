<?php

class Unl_Core_Block_Adminhtml_Report_Filter_Form_Settled extends Mage_Adminhtml_Block_Report_Filter_Form
{
    protected function _prepareForm()
    {
        parent::_prepareForm();
        $form = $this->getForm();
        /** @var Varien_Data_Form_Element_Fieldset $fieldset */
        $fieldset = $this->getForm()->getElement('base_fieldset');

        if (is_object($fieldset) && $fieldset instanceof Varien_Data_Form_Element_Fieldset) {
            $fieldset->removeField('report_type');
        }

        return $this;
    }
}
<?php

class Unl_Core_Block_Adminhtml_Report_Product_Options_Params extends Mage_Adminhtml_Block_Widget_Form
{
    protected function _prepareForm()
    {
        $actionUrl = $this->getUrl('*/*/*', array('_current' => true));
        $form = new Varien_Data_Form(
            array('id' => 'params_form', 'action' => $actionUrl, 'method' => 'get')
        );
        $htmlIdPrefix = 'product_report_';
        $form->setHtmlIdPrefix($htmlIdPrefix);
        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('reports')->__('Report Parameters')));

        $fieldset->addField('show_removed', 'select', array(
            'name' => 'show_removed',
            'options' => array(
                '1' => Mage::helper('reports')->__('Yes'),
                '0' => Mage::helper('reports')->__('No')
            ),
            'label' => Mage::helper('reports')->__('Show Removed Options'),
        ));

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _initFormValues()
    {
        $params = $this->getRequest()->getParam('params');
        if (is_string($params)) {
            $params = $this->helper('adminhtml')->prepareFilterString($params);
        }

        $this->getForm()->addValues($params);
        return parent::_initFormValues();
    }
}

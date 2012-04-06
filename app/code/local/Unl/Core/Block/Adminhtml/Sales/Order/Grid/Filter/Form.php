<?php

class Unl_Core_Block_Adminhtml_Sales_Order_Grid_Filter_Form extends Mage_Adminhtml_Block_Widget_Form
    implements Unl_Core_Block_Adminhtml_Widget_Form_AdvfilterInterface
{
	/**
     * Add fieldset with general fields
     *
     * @return Unl_Core_Block_Adminhtml_Customer_Grid_Filter_Form
     */
    protected function _prepareForm()
    {
        $actionUrl = $this->getUrl('*/*/*');
        $form = new Varien_Data_Form(
            array('id' => 'adv_filter_form', 'action' => $actionUrl, 'method' => 'get')
        );
        $htmlIdPrefix = 'order_grid_filter_';
        $form->setHtmlIdPrefix($htmlIdPrefix);
        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('adminhtml')->__('Advanced Filters')));

        $this->_prepareBaseFieldset($fieldset);

        $paymentOptions = Mage::helper('unl_core')->getActivePaymentMethodOptions();
        $fieldset->addField('payment_method', 'select', array(
            'name'      => 'payment_method',
            'options'   => $paymentOptions,
            'label'     => Mage::helper('adminhtml')->__('Payment Method'),
        ));

        $fieldset->addField('shipping_method', 'text', array(
            'name'      => 'shipping_method',
            'label'     => Mage::helper('adminhtml')->__('Shipping Method'),
        ));

        $fieldset->addField('can_ship', 'select', array(
            'name'      => 'can_ship',
            'options'   => array(
                ''  => '',
                '0' => Mage::helper('unl_core')->__('No'),
                '1' => Mage::helper('unl_core')->__('Yes')
            ),
            'label'     => Mage::helper('unl_core')->__('Can Ship')
        ));

        $fieldset->addField('item_sku', 'text', array(
            'name'      => 'item_sku',
            'label'     => Mage::helper('adminhtml')->__('Contains Item SKU'),
        ));

        $fieldset->addField('source_store', 'select', array(
            'name'      => 'source_store',
            'options'   => Mage::getModel('unl_core/store_source_filter')->getAllOptions(),
            'label'     => Mage::helper('unl_core')->__('For Store')
        ));

        $fieldset->addField('has_tax', 'select', array(
            'name'      => 'has_tax',
            'options'   => array(
                ''  => '',
                '0' => Mage::helper('unl_core')->__('No'),
                '1' => Mage::helper('unl_core')->__('Yes')
            ),
            'label'     => Mage::helper('unl_core')->__('Has Tax')
        ));

        $fieldset->addField('apply', 'button', array(
            'class'   => 'form-button',
            'value'   => 'Apply Filters',
            'onclick' => 'advFilterFormSubmit()',
        ));

        $form->setUseContainer(false);
        $this->setForm($form);

        return parent::_prepareForm();
    }

    protected function _prepareBaseFieldset($fieldset)
    {
        Mage::helper('unl_core')->prepareAdvfilterFieldset('order', $this, $fieldset);
        return $this;
    }

    /**
     * Initialize form fileds values
     * Method will be called after prepareForm and can be used for field values initialization
     *
     * @return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _initFormValues()
    {
        $this->getForm()->addValues($this->getFilterData()->getData());
        return parent::_initFormValues();
    }

    public function getFilterData()
    {
        $param = Mage::helper('unl_core')->getAdvancedGridFilters('order');

        if (empty($param)) {
            return new Varien_Object();
        }
        return $param;
    }
}

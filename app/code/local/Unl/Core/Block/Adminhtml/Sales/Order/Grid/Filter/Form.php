<?php

class Unl_Core_Block_Adminhtml_Sales_Order_Grid_Filter_Form extends Mage_Adminhtml_Block_Widget_Form
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

        $fieldset->getRenderer()->setTemplate('widget/grid/advanced/renderer/fieldset.phtml');
        $fieldset->setFilterUrl($this->getUrl('unl/sales_order/applyfilter'));
        $fieldset->setAdvFiltersUrl($this->getUrl('unl/sales_order/currentfilters'));
        $fieldset->setGridJsObject('sales_order_gridJsObject');

        $fieldset->addField('payment_method', 'select', array(
            'name'    => 'payment_method',
            'options' => (array('' => '') + Mage::helper('payment')->getPaymentMethodList()),
            'label'     => Mage::helper('adminhtml')->__('Payment Method'),
        ));

        $fieldset->addField('shipping_method', 'text', array(
            'name'      => 'shipping_method',
            'label'     => Mage::helper('adminhtml')->__('Shipping Method'),
        ));

        $fieldset->addField('item_sku', 'text', array(
            'name'      => 'item_sku',
            'label'     => Mage::helper('adminhtml')->__('Contains Item SKU'),
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

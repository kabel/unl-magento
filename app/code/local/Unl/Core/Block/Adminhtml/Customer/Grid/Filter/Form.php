<?php

class Unl_Core_Block_Adminhtml_Customer_Grid_Filter_Form extends Mage_Adminhtml_Block_Widget_Form
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
        $htmlIdPrefix = 'customer_grid_filter_';
        $form->setHtmlIdPrefix($htmlIdPrefix);
        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>Mage::helper('adminhtml')->__('Advanced Filters')));

        $dateFormatIso = Mage::app()->getLocale()->getDateFormat(Mage_Core_Model_Locale::FORMAT_TYPE_SHORT);

        $this->prepareBaseFieldset($fieldset);

        $fieldset->addField('from_store', 'select', array(
            'name'    => 'from_store',
            'options' => Mage::getModel('unl_core/store_source_filter')->getAllOptions(),
            'label'     => Mage::helper('adminhtml')->__('Purchased Store Item'),
        ));

        $fieldset->addField('item_sku', 'text', array(
            'name'      => 'item_sku',
            'label'     => Mage::helper('adminhtml')->__('Purchased Item SKU'),
        ));

        $fieldset->addField('purchase_from', 'date', array(
            'name'      => 'purchase_from',
            'format'    => $dateFormatIso,
            'image'     => $this->getSkinUrl('images/grid-cal.gif'),
            'label'     => Mage::helper('adminhtml')->__('Purchased From'),
            'title'     => Mage::helper('adminhtml')->__('Purchased From'),
        ));

        $fieldset->addField('purchase_to', 'date', array(
            'name'      => 'purchase_to',
            'format'    => $dateFormatIso,
            'image'     => $this->getSkinUrl('images/grid-cal.gif'),
            'label'     => Mage::helper('adminhtml')->__('Purchased To'),
            'title'     => Mage::helper('adminhtml')->__('Purchased To'),
        ));

        $fieldset->addField('is_repeat', 'select', array(
            'name'      => 'is_repeat',
            'options'   => array(
                '' => '',
                1 => Mage::helper('adminhtml')->__('Yes'),
            ),
            'label'     => Mage::helper('adminhtml')->__('Repeat Customer?'),
        ));

        $fieldset->addField('is_subscriber', 'select', array(
            'name'      => 'is_subscriber',
            'options'   => array(
                '' => '',
                1 => Mage::helper('adminhtml')->__('Yes'),
            ),
            'label'     => Mage::helper('adminhtml')->__('Newsletter Subscriber'),
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

    public function prepareBaseFieldset($fieldset)
    {
        Mage::helper('unl_core')->prepareAdvfilterFieldset('customer', $this, $fieldset);
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
        $param = Mage::helper('unl_core')->getAdvancedGridFilters('customer');

        if (empty($param)) {
            return new Varien_Object();
        }
        return $param;
    }
}

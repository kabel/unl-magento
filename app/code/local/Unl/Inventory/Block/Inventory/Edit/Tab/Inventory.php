<?php

class Unl_Inventory_Block_Inventory_Edit_Tab_Inventory extends Mage_Adminhtml_Block_Widget_Form
{
    public function initForm()
    {
        $form = new Varien_Data_Form();
        $form->setHtmlIdPrefix('adjust_');
        $form->setFieldNameSuffix('adjust');

        $product = Mage::registry('current_product');

        $fieldset = $form->addFieldset('base_fieldset',
            array('legend'=>Mage::helper('unl_inventory')->__('Update Inventory'))
        );

        $fieldset->addField('type', 'select', array(
            'name'     => 'type',
            'label'    => Mage::helper('unl_inventory')->__('Update Type'),
            'required' => true,
            'options'  => Mage::getModel('unl_inventory/source_audittype')->toOptionHash(),
        ));

        $fieldset->addField('adj_type', 'select', array(
            'name'     => 'adj_type',
            'label'    => Mage::helper('unl_inventory')->__('Adjustment Type'),
            'required' => true,
            'options'  => Mage::getModel('unl_inventory/source_adjustmenttype')->toOptionHash(),
        ));

        $fieldset->addField('qty', 'text', array(
            'name'     => 'qty',
            'label'    => Mage::helper('unl_inventory')->__('Qty'),
            'required' => true,
            'class'    => 'validate-number',
        ));

        $fieldset->addField('amount', 'text', array(
            'name'     => 'amount',
            'label'    => Mage::helper('unl_inventory')->__('Cost'),
            'required' => true,
            'class'    => 'validate-zero-or-greater',
            'note'     => Mage::helper('unl_inventory')->__('Please enter the full amount of the purchase.'),
        ));

        $fieldset->addField('note', 'textarea', array(
            'name'     => 'note',
            'label'    => Mage::helper('unl_inventory')->__('Note'),
        ));

        $this->setForm($form);
        return $this;
    }

    protected function _prepareLayout()
    {
        $depends = $this->getLayout()->createBlock('adminhtml/widget_form_element_dependence', 'form_after')
            ->addFieldMap("adjust_type", 'type')
            ->addFieldMap("adjust_adj_type", 'adj_type')
            ->addFieldMap("adjust_amount", 'amount')
            ->addFieldDependence('amount', 'type', (string)Unl_Inventory_Model_Audit::TYPE_PURCHASE)
            ->addFieldDependence('adj_type', 'type', (string)Unl_Inventory_Model_Audit::TYPE_ADJUSTMENT);

        $this->append($depends);
        return parent::_prepareLayout();
    }
}
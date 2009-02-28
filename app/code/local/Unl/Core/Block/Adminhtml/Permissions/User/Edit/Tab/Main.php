<?php

class Unl_Core_Block_Adminhtml_Permissions_User_Edit_Tab_Main extends Mage_Adminhtml_Block_Permissions_User_Edit_Tab_Main
{
    protected function _prepareForm()
    {
        parent::_prepareForm();
        
        $form = $this->getForm();
        $fieldset = $form->getElement('base_fieldset');
        
        $fieldset->addField('store', 'select', array(
            'name'    => 'store',
            'label'   => Mage::helper('adminhtml')->__('Store'),
            'id'      => 'store',
            'title'   => Mage::helper('adminhtml')->__('Operating Store'),
            'class'   => 'input-select',
            'values' => Mage::getModel('unl_core/store_source_switcher')->getAllOptions()
        ));
        
        $model = Mage::registry('permissions_user');
        $data = $model->getData();
        
        unset($data['password']);

        $form->setValues($data);
        
        return $this;
    }
}
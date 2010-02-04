<?php

class Unl_Core_Model_Observer
{
    protected $_wysiwygFields = array(
        'Mage_Adminhtml_Block_Cms_Block_Edit_Form' => array(
            'base_fieldset' => array(
                'content' => array()
            )
        ),
        'Mage_Adminhtml_Block_Cms_Page_Edit_Tab_Main' => array(
            'base_fieldset' => array(
                'content' => array()
            )
        ),
        'Mage_Adminhtml_Block_System_Email_Template_Edit_Form' => array(
            'base_fieldset' => array(
                'template_text' => array('disableCss' => true)
            )
        )
    );
    
    public function setupWysiwyg($observer)
    {
        $block = $observer->getEvent()->getBlock();
        
        //Do actions based on block type
        
        foreach (array_keys($this->_wysiwygFields) as $type) {
            if ($block instanceof $type) {
                /* @var $form Varien_Data_Form */
                $form = $block->getForm();
                foreach ($this->_wysiwygFields[$type] as $fieldset => $fields) {
                    $collection = $form;
                    if (is_string($fieldset)) {
                        $collection = $form->getElement($fieldset);
                    }
                    
                    foreach ($fields as $id => $def) {
                        $element = $collection->getElements()->searchById($id);
                        $element->setWysiwyg(true);
                        $element->setType('wysiwyg');
                        $element->setExtType('wysiwyg');
                        
                        if (!empty($def['disableCss'])) {
                            $element->setDisableCss(true);
                        }
                    }
                }
                break;
            }
        }
    }
    
    public function descriptionWysiwyg($observer)
    {
        $form = $observer->getEvent()->getForm();
        
        if ($element = $form->getElement('description')) {
            /* @var $element Varien_Data_Form_Element_Textarea */
            $data = $element->getData();
            $data['wysiwyg'] = true;
            $data['type'] = 'wysiwyg';
            $data['ext_type'] = 'wysiwyg';
            
            $fieldset = $element->getContainer();
            $fieldset->removeField($element->getId());
            $fieldset->addField('description', 'editor', $data);
        }
        
        if ($element = $form->getElement('short_description')) {
            $data = $element->getData();
            $data['wysiwyg'] = true;
            $data['type'] = 'wysiwyg';
            $data['ext_type'] = 'wysiwyg';
            
            $fieldset = $element->getContainer();
            $fieldset->removeField($element->getId());
            $fieldset->addField('short_description', 'editor', $data);
        }
    }
}

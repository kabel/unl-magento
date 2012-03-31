<?php

class Unl_Core_Model_Adminhtml_System_Config_Source_Catalog_ListMode extends Mage_Adminhtml_Model_System_Config_Source_Catalog_ListMode
{
    public function toOptionArray()
    {
        $stdOptions = parent::toOptionArray();

        return array_merge($stdOptions, array(
            array('value'=>'text', 'label'=>Mage::helper('adminhtml')->__('Text Only')),
            array('value'=>'grid-list-text', 'label'=>Mage::helper('adminhtml')->__('Grid (default) / List / Text')),
            array('value'=>'list-grid-text', 'label'=>Mage::helper('adminhtml')->__('List (default) / Grid / Text')),
            array('value'=>'text-grid-list', 'label'=>Mage::helper('adminhtml')->__('Text (default) / Grid / List')),
        ));
    }
}

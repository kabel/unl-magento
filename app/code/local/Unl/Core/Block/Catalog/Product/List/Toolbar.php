<?php

class Unl_Core_Block_Catalog_Product_List_Toolbar extends Mage_Catalog_Block_Product_List_Toolbar
{
    protected function _construct()
    {
        parent::_construct();
        // only needs to check the added modes
        switch (Mage::getStoreConfig('catalog/frontend/list_mode')) {
            case 'text':
                $this->_availableMode = array('text' => $this->__('Text Only'));
                break;
            case 'grid-list-text':
                $this->_availableMode = array(
                	'grid' => $this->__('Grid'),
                	'list' =>  $this->__('List'),
                	'text' => $this->__('Text Only'),
                );
                break;
            case 'list-grid-text':
                $this->_availableMode = array(
                	'list' =>  $this->__('List'),
                	'grid' => $this->__('Grid'),
                	'text' => $this->__('Text Only'),
                );
                break;
            case 'text-grid-list':
                $this->_availableMode = array(
                    'text' => $this->__('Text Only'),
                	'grid' => $this->__('Grid'),
                	'list' =>  $this->__('List'),
                );
                break;
        }
    }

    public function getDefaultPerPageValue()
    {
        if ($this->getCurrentMode() == 'text') {
            if ($default = $this->getDefaultTextPerPage()) {
                return $default;
            }
            return Mage::getStoreConfig('catalog/frontend/text_per_page');
        }

        return parent::getDefaultPerPageValue();
    }

    public function getAvailableLimit()
    {
        $currentMode = $this->getCurrentMode();
        if ($currentMode == 'text') {
            return $this->_getAvailableLimit($currentMode);
        } else {
            return parent::getAvailableLimit();
        }
    }
}

<?php

class Unl_Core_Model_Store_Source_Switcher extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    /**
     * Builds the general option array for stores
     *
     * @param boolean $withEmpty Include the empty option
     * @return array
     */
    public function getAllOptions($withEmpty = true)
    {
        if (is_null($this->_options)) {
            $options = array();
            $_websiteCollection = $this->getWebsiteCollection();
            
            foreach ($_websiteCollection as $_website) {
                $showWebsite = false;
                foreach ($this->getGroupCollection($_website) as $_group) {
                    $showGroup = false;
                    $optGroup = array();
                    foreach ($this->getStoreCollection($_group) as $_store) {
                        if ($showWebsite == false) {
                            $showWebsite = true;
                            $options[] = array('label' => $_website->getName(), 'value' => array());
                        }
                        if ($showGroup == false) {
                            $showGroup = true;
                            $optGroup['label'] = '-' . $_group->getName();
                        }
                        $optGroup['value'][] = array('label' => '--' . $_store->getName(), 'value' => $_store->getId());
                    }
                    if ($showGroup) {
                        $options[] = $optGroup;
                    }
                }
            }
            
            $this->_options = $options;
        }
        
        //$this->getAttribute()->setDefaultValue(Mage::app()->getRequest()->getParam('store'));
        
        $options = $this->_options;
        if ($withEmpty) {
            array_unshift($options, array('value'=>'', 'label'=>Mage::helper('core')->__('-- Please Select --')));
        }
        
        return $options;
    }
    
    public function getOptionText($value)
    {
        $options = $this->getAllOptions(false);
        
        foreach ($options as $item) {
            if (is_array($item['value'])) {
                foreach ($item['value'] as $subItem) {
                    if ($subItem['value'] == $value) {
                        return $subItem['label'];
                    }
                }
            }
            if ($item['value'] == $value) {
                return $item['label'];
            }
        }
        
        return false;
    }
    
    public function getOptionArray()
    {
        return $this->getAllOptions(false);
    }
    
    public function toOptionArray()
    {
        return $this->getAllOptions(false);
    }
    
    public function toFormOptionArray()
    {
        $options = array();
        foreach ($this->getAllOptions() as $opt) {
            if (is_array($opt['value'])) {
                $subOpt = array();
                foreach ($opt['value'] as $item) {
                    $subOpt[$item['value']] = $item['label'];
                }
                $options[] = $subOpt;
            } else {
                $options[$opt['value']] = $opt['label'];
            }
        }
        
        return $options;
    }
    
    public function getWebsiteCollection()
    {
        $collection = Mage::getModel('core/website')->getResourceCollection();

        /*$websiteIds = $this->getWebsiteIds();
        if (!is_null($websiteIds)) {
            $collection->addIdFilter($this->getWebsiteIds());
        }*/

        return $collection->load();
    }

    public function getGroupCollection($website)
    {
        if (!$website instanceof Mage_Core_Model_Website) {
            $website = Mage::getModel('core/website')->load($website);
        }
        return $website->getGroupCollection();
    }

    public function getStoreCollection($group)
    {
        if (!$group instanceof Mage_Core_Model_Store_Group) {
            $group = Mage::getModel('core/store_group')->load($group);
        }
        $stores = $group->getStoreCollection();
        /*if (!empty($this->_storeIds)) {
            $stores->addIdFilter($this->_storeIds);
        }*/
        return $stores;
    }
}
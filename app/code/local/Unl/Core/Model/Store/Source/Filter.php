<?php

class Unl_Core_Model_Store_Source_Filter
{
    protected $_options;

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
            $scope = array();
            if (Mage::getSingleton('admin/session')->getUser()->getScope()) {
                $scope = explode(',', Mage::getSingleton('admin/session')->getUser()->getScope());
            }

            foreach ($this->getWebsiteCollection() as $_website) {
                foreach ($this->getGroupCollection($_website) as $_group) {
                    foreach ($this->getStoreCollection($_group) as $_store) {
                        if (empty($scope) || in_array($_store->getId(), $scope)) {
                            $options[$_store->getId()] = $_store->getName();
                        }
                    }
                }
            }

            $this->_options = $options;
        }

        $options = array();
        if ($withEmpty) {
            $options[''] = '';
        }

        return $options + $this->_options;
    }

    public function getOptionArray()
    {
        return $this->getAllOptions(false);
    }

    public function toOptionArray()
    {
        return $this->getAllOptions(false);
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
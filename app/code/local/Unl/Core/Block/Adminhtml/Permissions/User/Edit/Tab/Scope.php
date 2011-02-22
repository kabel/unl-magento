<?php

class Unl_Core_Block_Adminhtml_Permissions_User_Edit_Tab_Scope extends Mage_Adminhtml_Block_Widget_Form implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
/**
     * Get tab label
     *
     * @return string
     */
    public function getTabLabel()
    {
        return Mage::helper('adminhtml')->__('User Scope');
    }

    /**
     * Get tab title
     *
     * @return string
     */
    public function getTabTitle()
    {
        return $this->getTabLabel();
    }

    /**
     * Whether tab is available
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Whether tab is visible
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    public function __construct()
    {
        parent::__construct();

        $model = Mage::registry('permissions_user');

        $scope = $model->getScope();
        if (!empty($scope)) {
            $selStores = explode(',', $scope);
        } else {
            $selStores = array();
        }
        $this->setSelectedScope($selStores);

        $scope = $model->getWarehouseScope();
        if (!empty($scope)) {
            $selIds = explode(',', $scope);
        } else {
            $selIds = array();
        }
        $this->setSelectedWarehouses($selIds);

        $this->setTemplate('permissions/userscope.phtml');
    }

    public function getEverythingAllowed()
    {
        $selStores = $this->getSelectedScope();
        return empty($selStores);
    }

    public function getNoneSelected()
    {
        $selIds = $this->getSelectedWarehouses();
        return empty($selIds);
    }

    public function getWebsiteCollection()
    {
        $collection = Mage::getModel('core/website')->getResourceCollection();
        return $collection->load();
    }

    public function getGroupCollection($website)
    {
        if (!$website instanceof Mage_Core_Model_Website) {
            $website = Mage::getModel('core/website')->load($website);
        }
        return $website->getGroupCollection();
    }

    public function isGroupSelected($group)
    {
        $selStores = $this->getSelectedScope();
        foreach ($group->getStoreCollection() as $store) {
            if (in_array($store->getId(), $selStores)) {
                return true;
            }
        }

        return false;
    }

    public function getSelectionValue($group)
    {
        $value = array();
        foreach ($group->getStoreCollection() as $store) {
            $value[] = $store->getId();
        }

        return implode(',', $value);
    }

    public function getWarehouseCollection()
    {
        $collection = Mage::getModel('unl_core/warehouse')->getResourceCollection();
        return $collection->load();
    }

    public function isWarehouseSelected($warehouse)
    {
        $selIds = $this->getSelectedWarehouses();
        return in_array($warehouse->getId(), $selIds);
    }
}
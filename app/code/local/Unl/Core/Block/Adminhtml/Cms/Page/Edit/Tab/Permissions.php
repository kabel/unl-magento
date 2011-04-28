<?php

class Unl_Core_Block_Adminhtml_Cms_Page_Edit_Tab_Permissions
    extends Mage_Adminhtml_Block_Widget_Form
    implements Mage_Adminhtml_Block_Widget_Tab_Interface
{
    public function __construct()
    {
        parent::__construct();

        $model = Mage::registry('cms_page');

        $scope = $model->getPermissions();
        if (!empty($scope)) {
            $selStores = explode(',', $scope);
        } else {
            $selStores = array();
        }
        $this->setSelectedScope($selStores);

        $this->setTemplate('cms/permissions/stores.phtml');
    }

    public function getEverythingAllowed()
    {
        $selStores = $this->getSelectedScope();
        return empty($selStores);
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

    public function getTabLabel()
    {
        return Mage::helper('cms')->__('Page Permissions');
    }

    public function getTabTitle()
    {
        return Mage::helper('cms')->__('Page Permissions');
    }

    public function canShowTab()
    {
        return Mage::getSingleton('admin/session')->isAllowed('cms/page/permissions');
    }

    public function isHidden()
    {
        return false;
    }
}

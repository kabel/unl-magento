<?php

class Unl_Core_Block_Page_Html_Directory extends Mage_Page_Block_Switch
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('page/html/directory.phtml');
    }

    /**
     * @return Mage_Cms_Helper_Wysiwyg_Images
     */
    protected function _getHelper()
    {
        return Mage::helper('cms/wysiwyg_images');
    }

    protected function _getStoreIconPath($code)
    {
        $cmsRoot = $this->_getCmsStorageRoot();
        foreach (array('.png', '.jpg') as $ext) {
            $path = 'Home' . DS . 'icons' . DS . $code . '_icon' . $ext;

            if (file_exists($cmsRoot . $path)) {
                return $path;
            }
        }

        return false;
    }

    protected function _getCmsStorageRoot()
    {
        return $this->_getHelper()->getStorageRoot();
    }

    public function getStoreIconUrl($code)
    {
        $path = $this->_getStoreIconPath($code);

        if (!$path) {
            return false;
        }

        $path = str_replace(Mage::getBaseDir('media'), '', $this->_getCmsStorageRoot()) . $path;
        $path = trim($path, DS);

        return Mage::getBaseUrl('media') . $this->_getHelper()->convertPathToUrl($path);
    }

    public function _filterStoreGroup($group)
    {
        return !$group->getIsHidden() && !in_array('default', $group->getStoreCodes());
    }

    public function getGroups()
    {
        $groups = array_filter(parent::getGroups(), array($this, '_filterStoreGroup'));

        if (count($groups > 1)) {
            if ($this->hasData('shuffle')) {
                shuffle($groups);
            } else {
                $helper = Mage::helper('unl_core');
                usort($groups, array($helper, 'compareStoreGroups'));
            }
        }

        return $groups;
    }
}

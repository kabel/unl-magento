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
        return 'Home' . DS . 'icons' . DS . $code . '_icon.png';
    }

    protected function _getCmsStorageRoot()
    {
        return $this->_getHelper()->getStorageRoot();
    }

    public function hasStoreIcon($code)
    {
        return file_exists($this->_getCmsStorageRoot() . $this->_getStoreIconPath($code));
    }

    public function getStoreIconUrl($code)
    {
        $path = str_replace(Mage::getBaseDir('media'), '', $this->_getCmsStorageRoot());
        $path .= $this->_getStoreIconPath($code);
        $path = trim($path, DS);

        return Mage::getBaseUrl('media') . $this->_getHelper()->convertPathToUrl($path);
    }
}

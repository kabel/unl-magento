<?php

class Unl_Core_Helper_Cms_Wysiwyg_Images extends Mage_Cms_Helper_Wysiwyg_Images
{
    /**
     * Images Storage root directory
     * @var string
     */
    protected $_storageRoot;

    /* Overrides
     * @see Mage_Cms_Helper_Wysiwyg_Images::getStorageRoot()
     * to match MAGE_PATCH SUPEE-2677
     */
    public function getStorageRoot()
    {
        if (!$this->_storageRoot) {
            $this->_storageRoot = realpath(
                    Mage::getConfig()->getOptions()->getMediaDir()
                    . DS . Mage_Cms_Model_Wysiwyg_Config::IMAGE_DIRECTORY
                ) . DS;
        }
        return $this->_storageRoot;
    }

    public function getBaseUrl()
    {
        return Mage::getBaseUrl('media') . Mage_Cms_Model_Wysiwyg_Config::IMAGE_DIRECTORY . '/';
    }

    /* Overrides
     * @see Mage_Cms_Helper_Wysiwyg_Images::getCurrentPath()
     * to match MAGE_PATCH SUPEE-2677
     */
    public function getCurrentPath()
    {
        if (!$this->_currentPath) {
            $currentPath = $this->getStorageRoot();
            $node = $this->_getRequest()->getParam($this->getTreeNodeName());
            if ($node) {
                $path = realpath($this->convertIdToPath($node));
                if (is_dir($path) && false !== stripos($path, $currentPath)) {
                    $currentPath = $path;
                }
            }
            $io = new Varien_Io_File();
            if (!$io->isWriteable($currentPath) && !$io->mkdir($currentPath)) {
                $message = Mage::helper('cms')->__('The directory %s is not writable by server.',$currentPath);
                Mage::throwException($message);
            }
            $this->_currentPath = $currentPath;
        }
        return $this->_currentPath;
    }

    /* Overrides
     * @see Mage_Cms_Helper_Wysiwyg_Images::getCurrentUrl()
     * to match MAGE_PATCH SUPEE-2677
     */
    public function getCurrentUrl()
    {
        if (!$this->_currentUrl) {
            $path = str_replace(realpath(Mage::getConfig()->getOptions()->getMediaDir()), '', $this->getCurrentPath());
            $path = trim($path, DS);
            $this->_currentUrl = Mage::app()->getStore($this->_storeId)->getBaseUrl('media') .
            $this->convertPathToUrl($path) . '/';
        }
        return $this->_currentUrl;
    }
}

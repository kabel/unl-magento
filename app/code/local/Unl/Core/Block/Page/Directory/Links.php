<?php

class Unl_Core_Block_Page_Directory_Links extends Mage_Core_Block_Template
{
    protected $_urlCache = array();

    public function getCacheKeyInfo()
    {
        $info = parent::getCacheKeyInfo();
        if (in_array('cms_index_index', $this->getLayout()->getUpdate()->getHandles())) {
            $info[] = 'CMS_HOME';
        }

        return $info;
    }

    public function addStoreLink($label, $title = '', $position = null, $store = 'default')
    {
        $url = $this->getUrl('/', array('_store' => $store));
        if (isset($this->_urlCache[$url])) {
            return $this;
        }
        $this->_urlCache[$url] = true;

        $parentBlock = $this->getParentBlock();

        if ($parentBlock && Mage::helper('core')->isModuleOutputEnabled('Unl_Core')) {
            $parentBlock->addLink($label, $url, $title, false, null, $position);
        }

        return $this;
    }

    public function removeStoreLink($store = 'default')
    {
        $url = $this->getUrl('/', array('_store' => $store));
        unset($this->_urlCache[$url]);

        $parentBlock = $this->getParentBlock();

        $parentBlock->removeLinkByUrl($url);

        return $this;
    }

    public function addHomeLink($label, $title = '', $position = null)
    {
        return $this->addStoreLink($label, $title, $position, Mage::app()->getStore());
    }

    public function removeHomeLink()
    {
        return $this->removeStoreLink(Mage::app()->getStore());
    }
}

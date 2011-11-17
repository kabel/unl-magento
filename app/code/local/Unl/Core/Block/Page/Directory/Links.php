<?php

class Unl_Core_Block_Page_Directory_Links extends Mage_Core_Block_Template
{
    public function addStoreLink($label, $title = '', $position = null, $store = 'default')
    {
        $url = $this->getUrl('/', array('_store' => $store));
        $parentBlock = $this->getParentBlock();

        if ($parentBlock && Mage::helper('core')->isModuleOutputEnabled('Unl_Core')) {
            $parentBlock->addLink($label, $url, $title, false, null, $position);
        }

        return $this;
    }

    public function removeStoreLink($store = 'default')
    {
        $url = $this->getUrl('/', array('_store' => $store));
        $parentBlock = $this->getParentBlock();

        $parentBlock->removeLinkByUrl($url);

        return $this;
    }
}

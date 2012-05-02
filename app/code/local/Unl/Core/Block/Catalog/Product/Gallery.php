<?php

class Unl_Core_Block_Catalog_Product_Gallery extends Mage_Catalog_Block_Product_Gallery
{
    protected function _prepareLayout()
    {
        if ($headBlock = $this->getLayout()->getBlock('head')) {
            if ($this->getProduct()->getMetaTitle()) {
                $title = $this->getProduct()->getMetaTitle();
            } else {
                $title = $this->getProduct()->getName() . ' - Gallery';
            }
            $headBlock->setTitle($title);
        }
        return Mage_Core_Block_Template::_prepareLayout();
    }
}

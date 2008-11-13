<?php

class Unl_Catalog_Block_Breadcrumbs extends Mage_Catalog_Block_Breadcrumbs
{
    protected function _prepareLayout()
    {
        if ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs')) {
            $breadcrumbsBlock->addCrumb('home',
                array('label'=>Mage::helper('catalog')->__('Home'), 'title'=>Mage::helper('catalog')->__('Go to Home Page'), 'link'=>Mage::getBaseUrl())
            );

            $title = '';
            $path = Mage::helper('catalog')->getBreadcrumbPath($this->getCategory());
            foreach ($path as $name=>$breadcrumb) {
                $breadcrumbsBlock->addCrumb($name, $breadcrumb);
                if (!empty($title)) {
                    $title .= ' '.Mage::getStoreConfig('catalog/seo/title_separator').' ';
                }
                $title .= $breadcrumb['label'];
            }

            if ($headBlock = $this->getLayout()->getBlock('head')) {
                $headBlock->setTitle($title);
            }
        }
        return $this;
    }

}
<?php

class Unl_Core_Block_Catalog_Breadcrumbs extends Mage_Catalog_Block_Breadcrumbs
{
    /* Overrides
     * @see Mage_Catalog_Block_Breadcrumbs::_prepareLayout()
     * by skipping the "Home" breadcrumb and not reversing the crumbs
     */
    protected function _prepareLayout()
    {
        if ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs')) {
            $title = array();
            $path  = Mage::helper('catalog')->getBreadcrumbPath();

            foreach ($path as $name => $breadcrumb) {
                $breadcrumbsBlock->addCrumb($name, $breadcrumb);
                $title[] = $breadcrumb['label'];
            }

            if ($headBlock = $this->getLayout()->getBlock('head')) {
                $headBlock->setTitle(join($this->getTitleSeparator(), $title));
            }
        }
        return $this;
    }

}
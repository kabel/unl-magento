<?php

class Unl_Core_Block_Adminhtml_Catalog_Product_Grid extends Mage_Adminhtml_Block_Catalog_Product_Grid
{
     /**
     * Add new rss list to grid
     *
     * @param   string $url
     * @param   string $label
     * @return  Mage_Adminhtml_Block_Widget_Grid
     */
    public function addRssList($url, $label)
    {
        $this->_rssLists[] = new Varien_Object(
            array(
                'url'   => Mage::getModel('core/url')->getUrl($url, array('_store' => 'default')),
                'label' => $label
            )
        );
        return $this;
    }
}
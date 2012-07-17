<?php

class Unl_Core_Block_Adminhtml_Catalog_Product_Helper_Form_Gallery_Content
    extends Mage_Adminhtml_Block_Catalog_Product_Helper_Form_Gallery_Content
{
    protected function _prepareLayout()
    {
        $this->setChild('uploader',
            $this->getLayout()->createBlock('adminhtml/media_uploader')
        );

        $this->getUploader()->getConfig()
            ->setUrl(Mage::getModel('adminhtml/url')
                ->addSessionParam()
                ->setQueryParam('ajax', '1')
                ->getUrl('*/catalog_product_gallery/upload')
            )
            ->setFileDataName('image')
            ->setFilters(array(
                array(
                    'title' => Mage::helper('adminhtml')->__('Images (.gif, .jpg, .png)'),
                    'extensions' => 'gif,jpg,jpeg,png'
                )
            ));

        Mage::dispatchEvent('catalog_product_gallery_prepare_layout', array('block' => $this));

        return Mage_Adminhtml_Block_Widget::_prepareLayout();
    }

    protected function _beforeToHtml()
    {
        $this->getUploader()->getConfig()->setDropElement($this->getHtmlId());
        return parent::_beforeToHtml();
    }
}

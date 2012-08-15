<?php

class Unl_Core_Block_Downloadable_Adminhtml_Catalog_Product_Edit_Tab_Downloadable_Samples
    extends Mage_Downloadable_Block_Adminhtml_Catalog_Product_Edit_Tab_Downloadable_Samples
{
    public function getConfigJson()
    {
        $this->getConfig()
            ->setRuntimes('html5,flash,silverlight')
            ->setUrl(Mage::getModel('adminhtml/url')->addSessionParam()
                ->setQueryParam('ajax', '1')
                ->getUrl('*/downloadable_file/upload', array('type' => 'samples', '_secure' => true)));

        $this->getConfig()->setFlashSwfUrl($this->getSkinUrl('media/plupload.flash.swf'));
        $this->getConfig()->setSilverlightXapUrl($this->getSkinUrl('media/plupload.silverlight.xap'));

        $this->getConfig()
            ->setBrowseButtonHover('hover')
            ->setBrowseButtonActive('active');

        $this->getConfig()->setRequiredFeatures('multipart');
        $this->getConfig()->setMultipart(true);
        $this->getConfig()->setMultipartParams(array('form_key' => $this->getFormKey()));
        $this->getConfig()->setFileDataName('samples');

        $this->getConfig()->setMultiSelection(false);
        $this->getConfig()->setReplaceBrowseWithRemove(true);
        $this->getConfig()->setHideUploadButton(true);
        return Mage::helper('core')->jsonEncode($this->getConfig()->getData());
    }

    public function getBrowseButtonHtml()
    {
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label' => Mage::helper('downloadable')->__('...'),
                'title' => Mage::helper('downloadable')->__('Browse'),
                'id'    => 'downloadable_sample_{{id}}_file-browse',
                'style' => 'width:32px;'
            ));
        return $button->toHtml();
    }

    public function getRemoveButtonHtml()
    {
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label' => Mage::helper('downloadable')->__('Remove'),
                'id'    => 'downloadable_sample_{{id}}_file-remove',
                'class' => 'delete icon-btn'
            ));
        return $button->toHtml();
    }
}

<?php

class Unl_Core_Block_Adminhtml_Media_Uploader extends Mage_Adminhtml_Block_Media_Uploader
{
    public function __construct()
    {
        $this->setId($this->getId() . '_Uploader');
        $this->setTemplate('media/uploader.phtml');
        $this->getConfig()
            ->setRuntimes('html5,flash,silverlight')
            ->setBrowseButton($this->_getButtonId('browse'))
            ->setContainer($this->getId())
            ->setUrl(Mage::getModel('adminhtml/url')->addSessionParam()
                ->setQueryParam('ajax', '1')
                ->getUrl('*/*/upload'));

        $this->getConfig()->setFlashSwfUrl($this->getUploaderUrl('media/plupload.flash.swf'));
        $this->getConfig()->setSilverlightXapUrl($this->getUploaderUrl('media/plupload.silverlight.xap'));

        $this->getConfig()
            ->setBrowseButtonHover('hover')
            ->setBrowseButtonActive('active');

        $this->getConfig()->setMaxFileSize($this->getDataMaxSize());
        $this->getConfig()->setRequiredFeatures('multipart');
        $this->getConfig()->setMultipart(true);
        $this->getConfig()->setMultipartParams(array('form_key' => $this->getFormKey()));
        $this->getConfig()->setFileDataName('file');
        $this->getConfig()->setFilters(array(
            array(
                'title' => Mage::helper('adminhtml')->__('Images (.gif, .jpg, .png)'),
                'extensions' => 'gif,jpg,png'
            ),
            array(
                'title' => Mage::helper('adminhtml')->__('Media (.avi, .flv, .swf)'),
                'extensions' => 'avi,flv,swf'
            ),
            array(
                'title' => Mage::helper('adminhtml')->__('All Files'),
                'extensions' => '*'
            )
        ));
    }
}

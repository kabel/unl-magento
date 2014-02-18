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

        $this->getConfig()->setFlashSwfUrl($this->getUploaderUrl('media/Moxie.swf'));
        $this->getConfig()->setSilverlightXapUrl($this->getUploaderUrl('media/Moxie.xap'));

        $this->getConfig()
            ->setBrowseButtonHover('hover')
            ->setBrowseButtonActive('active');

        $this->getConfig()->setRequiredFeatures('send_multipart');
        $this->getConfig()->setMultipart(true);
        $this->getConfig()->setMultipartParams(array('form_key' => $this->getFormKey()));
        $this->getConfig()->setFileDataName('file');
        $this->getConfig()->setFilters(array(
            'max_file_size' => $this->getDataMaxSize(),
            'mime_types' => array(
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
                ),
            )
        ));
    }
}

<?php

require_once 'Mage/Adminhtml/controllers/Cms/WysiwygController.php';

class Unl_Core_Adminhtml_Cms_WysiwygController extends Mage_Adminhtml_Cms_WysiwygController
{
    public function directiveAction()
    {
        $directive = $this->getRequest()->getParam('___directive');
        $directive = Mage::helper('core')->urlDecode($directive);
        $url = Mage::getModel('core/email_template_filter')->filter($directive);
        $image = Varien_Image_Adapter::factory('GD2');
        $response = $this->getResponse();
        try {
            $image->open($url);
            $response->setHeader('Content-type', $image->getMimeType())->setBody($image->getImage());
        } catch (Exception $e) {
            $image->open(Mage::getSingleton('cms/wysiwyg_config')->getSkinImagePlaceholderUrl());
            $response->setHeader('Content-type', $image->getMimeType())->setBody($image->getImage());
            Mage::logException($e);
        }
    }
}

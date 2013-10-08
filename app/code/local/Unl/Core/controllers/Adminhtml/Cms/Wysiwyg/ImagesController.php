<?php

require_once 'Mage/Adminhtml/controllers/Cms/Wysiwyg/ImagesController.php';

class Unl_Core_Adminhtml_Cms_Wysiwyg_ImagesController extends Mage_Adminhtml_Cms_Wysiwyg_ImagesController
{
    public function thumbnailAction()
    {
        $this->_getSession()->unlock();

        $file = $this->getRequest()->getParam('file');
        $file = Mage::helper('cms/wysiwyg_images')->idDecode($file);
        $thumb = $this->getStorage()->resizeOnTheFly($file);
        if ($thumb !== false) {
            $size = getimagesize($thumb);
            $this->getResponse()
                ->setHeader('Content-type', $size['mime'])
                ->clearBody();

            $this->getResponse()->sendHeaders();
            readfile($thumb);
            exit;
        } else {
            // todo: genearte some placeholder
        }
    }
}

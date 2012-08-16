<?php

require_once 'Mage/Adminhtml/controllers/Cms/Wysiwyg/ImagesController.php';

class Unl_Core_Adminhtml_Cms_Wysiwyg_ImagesController extends Mage_Adminhtml_Cms_Wysiwyg_ImagesController
{
    public function thumbnailAction()
    {
        $file = $this->getRequest()->getParam('file');
        $file = Mage::helper('cms/wysiwyg_images')->idDecode($file);
        $thumb = $this->getStorage()->resizeOnTheFly($file);
        if ($thumb !== false) {
            $image = Varien_Image_Adapter::factory('GD2');
            $image->open($thumb);
            $this->getResponse()->setHeader('Content-type', $image->getMimeType())->setBody($image->getImage());
        } else {
            // todo: genearte some placeholder
        }
    }
}

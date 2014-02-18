<?php

class Unl_DownloadablePlus_Block_Adminhtml_Catalog_Product_Edit_Tab_Downloadable_Links
    extends Mage_Downloadable_Block_Adminhtml_Catalog_Product_Edit_Tab_Downloadable_Links
{
    public function getConfigJson($type = 'links')
    {
        $this->getConfig()
            ->setRuntimes('html5,flash,silverlight')
            ->setUrl(Mage::getModel('adminhtml/url')->addSessionParam()
                ->setQueryParam('ajax', '1')
                ->getUrl('*/downloadable_file/upload', array('type' => $type, '_secure' => true)));

        $this->getConfig()->setFlashSwfUrl($this->getSkinUrl('media/Moxie.swf'));
        $this->getConfig()->setSilverlightXapUrl($this->getSkinUrl('media/Moxie.xap'));

        $this->getConfig()
            ->setBrowseButtonHover('hover')
            ->setBrowseButtonActive('active');

        $this->getConfig()->setRequiredFeatures('send_multipart');
        $this->getConfig()->setMultipart(true);
        $this->getConfig()->setMultipartParams(array('form_key' => $this->getFormKey()));
        $this->getConfig()->setFileDataName($type);

        $this->getConfig()->setMultiSelection(false);
        $this->getConfig()->setReplaceBrowseWithRemove(true);
        $this->getConfig()->setHideUploadButton(true);
        return Mage::helper('core')->jsonEncode($this->getConfig()->getData());
    }

    public function getBrowseButtonHtml($type = '')
    {
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label' => Mage::helper('downloadable')->__('...'),
                'title' => Mage::helper('downloadable')->__('Browse'),
                'id'    => 'downloadable_link_{{id}}' . $type . '_file-browse',
                'style' => 'width:32px;'
            ));
        return $button->toHtml();
    }

    public function getRemoveButtonHtml($type = '')
    {
        $button = $this->getLayout()->createBlock('adminhtml/widget_button')
            ->setData(array(
                'label' => Mage::helper('downloadable')->__('Remove'),
                'id'    => 'downloadable_link_{{id}}' . $type . '_file-remove',
                'class' => 'delete icon-btn'
            ));
        return $button->toHtml();
    }
}

<?php

class Unl_DownloadablePlus_Block_Adminhtml_Catalog_Product_Edit_Tab_Downloadable_Links
    extends Mage_Downloadable_Block_Adminhtml_Catalog_Product_Edit_Tab_Downloadable_Links
{
    public function getLinkData()
    {
        $linkArr = array();
        $links = $this->getProduct()->getTypeInstance(true)->getLinks($this->getProduct());
        $priceWebsiteScope = $this->getIsPriceWebsiteScope();
        foreach ($links as $item) {
            $tmpLinkItem = array(
                'link_id' => $item->getId(),
                'title' => $this->escapeHtml($item->getTitle()),
                'price' => $this->getCanReadPrice() ? $this->getPriceValue($item->getPrice()) : '',
                'number_of_downloads' => $item->getNumberOfDownloads(),
                'is_shareable' => $item->getIsShareable(),
                'link_url' => $item->getLinkUrl(),
                'link_secret' => $item->getLinkSecret(),
                'link_type' => $item->getLinkType(),
                'sample_file' => $item->getSampleFile(),
                'sample_url' => $item->getSampleUrl(),
                'sample_type' => $item->getSampleType(),
                'sort_order' => $item->getSortOrder(),
            );
            $file = Mage::helper('downloadable/file')->getFilePath(
                Mage_Downloadable_Model_Link::getBasePath(), $item->getLinkFile()
            );

            if ($item->getLinkFile() && !is_file($file)) {
                Mage::helper('core/file_storage_database')->saveFileToFilesystem($file);
            }

            if ($item->getLinkFile() && is_file($file)) {
                $name = '<a href="'
                    . $this->getUrl('*/downloadable_product_edit/link', array(
                        'id' => $item->getId(),
                        '_secure' => true
                    )) . '">' . Mage::helper('downloadable/file')->getFileFromPathFile($item->getLinkFile()) . '</a>';
                    $tmpLinkItem['file_save'] = array(
                        array(
                            'file' => $item->getLinkFile(),
                            'name' => $name,
                            'size' => filesize($file),
                            'status' => 'old'
                        ));
            }
            $sampleFile = Mage::helper('downloadable/file')->getFilePath(
                Mage_Downloadable_Model_Link::getBaseSamplePath(), $item->getSampleFile()
            );
            if ($item->getSampleFile() && is_file($sampleFile)) {
                $tmpLinkItem['sample_file_save'] = array(
                    array(
                        'file' => $item->getSampleFile(),
                        'name' => Mage::helper('downloadable/file')->getFileFromPathFile($item->getSampleFile()),
                        'size' => filesize($sampleFile),
                        'status' => 'old'
                    ));
            }
            if ($item->getNumberOfDownloads() == '0') {
                $tmpLinkItem['is_unlimited'] = ' checked="checked"';
            }
            if ($this->getProduct()->getStoreId() && $item->getStoreTitle()) {
                $tmpLinkItem['store_title'] = $item->getStoreTitle();
            }
            if ($this->getProduct()->getStoreId() && $priceWebsiteScope) {
                $tmpLinkItem['website_price'] = $item->getWebsitePrice();
            }
            $linkArr[] = new Varien_Object($tmpLinkItem);
        }
        return $linkArr;
    }

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

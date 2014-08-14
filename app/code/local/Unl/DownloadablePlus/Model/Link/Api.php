<?php

class Unl_DownloadablePlus_Model_Link_Api extends Mage_Downloadable_Model_Link_Api
{
    public function add($productId, $resource, $resourceType, $store = null, $identifierType = null)
    {
        try {
            $this->_getValidator()->validateType($resourceType);
            $this->_getValidator()->validateAttributes($resource, $resourceType);
        } catch (Exception $e) {
            $this->_fault('validation_error', $e->getMessage());
        }

        $resource['is_delete'] = 0;
        if ($resourceType == 'link') {
            $resource['link_id'] = 0;
        } elseif ($resourceType == 'sample') {
            $resource['sample_id'] = 0;
        }

        if ($resource['type'] == 'file') {
            if (isset($resource['file'])) {
                $resource['file'] = $this->_uploadFile($resource['file'], $resourceType);
            }
            unset($resource[$resourceType.'_url']);
            unset($resource[$resourceType.'_secret']);
        } elseif (in_array($resource['type'], array('url', 'callback'))) {
            unset($resource['file']);
        }

        if (isset($resource['sample'])) {
            if ($resourceType == 'link' && $resource['sample']['type'] == 'file') {
                if (isset($resource['sample']['file'])) {
                    $resource['sample']['file'] = $this->_uploadFile($resource['sample']['file'], 'link_samples');
                }
                unset($resource['sample']['url']);
            } elseif ($resourceType == 'link' && $resource['sample']['type'] == 'url') {
                $resource['sample']['file'] = null;
            }
        }

        $product = $this->_getProduct($productId, $store, $identifierType);
        try {
            $downloadable = array($resourceType => array($resource));
            $product->setDownloadableData($downloadable);
            $product->save();
        } catch (Exception $e) {
            $this->_fault('save_error', $e->getMessage());
        }

        return true;
    }

    public function items($productId, $store = null, $identifierType = null)
    {
        $product = $this->_getProduct($productId, $store, $identifierType);

        $linkArr = array();
        $links = $product->getTypeInstance(true)->getLinks($product);
        foreach ($links as $item) {
            $tmpLinkItem = array(
                'link_id' => $item->getId(),
                'title' => $item->getTitle(),
                'price' => $item->getPrice(),
                'number_of_downloads' => $item->getNumberOfDownloads(),
                'is_shareable' => $item->getIsShareable(),
                'link_url' => $item->getLinkUrl(),
                'link_secret' => $item->getLinkSecret(),
                'link_type' => $item->getLinkType(),
                'sample_file' => $item->getSampleFile(),
                'sample_url' => $item->getSampleUrl(),
                'sample_type' => $item->getSampleType(),
                'sort_order' => $item->getSortOrder()
            );
            $file = Mage::helper('downloadable/file')->getFilePath(
                Mage_Downloadable_Model_Link::getBasePath(), $item->getLinkFile()
            );

            if ($item->getLinkFile() && !is_file($file)) {
                Mage::helper('core/file_storage_database')->saveFileToFilesystem($file);
            }

            if ($item->getLinkFile() && is_file($file)) {
                $name = Mage::helper('downloadable/file')->getFileFromPathFile($item->getLinkFile());
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
                $tmpLinkItem['is_unlimited'] = 1;
            }
            if ($product->getStoreId() && $item->getStoreTitle()) {
                $tmpLinkItem['store_title'] = $item->getStoreTitle();
            }
            if ($product->getStoreId() && Mage::helper('downloadable')->getIsPriceWebsiteScope()) {
                $tmpLinkItem['website_price'] = $item->getWebsitePrice();
            }
            $linkArr[] = $tmpLinkItem;
        }
        unset($item);
        unset($tmpLinkItem);
        unset($links);

        $samples = $product->getTypeInstance(true)->getSamples($product)->getData();
        return array('links' => $linkArr, 'samples' => $samples);
    }
}

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
        } elseif ($resource['type'] == 'url') {
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
}

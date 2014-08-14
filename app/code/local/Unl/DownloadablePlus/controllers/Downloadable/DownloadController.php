<?php

require_once "Mage/Downloadable/controllers/DownloadController.php";

class Unl_DownloadablePlus_Downloadable_DownloadController extends Mage_Downloadable_DownloadController
{
    public function linkAction()
    {
        $id = $this->getRequest()->getParam('id', 0);
        $linkPurchasedItem = Mage::getModel('downloadable/link_purchased_item')->load($id, 'link_hash');
        if (! $linkPurchasedItem->getId() ) {
            $this->_getCustomerSession()->addNotice(Mage::helper('downloadable')->__("Requested link does not exist."));
            return $this->_redirect('*/customer/products');
        }

        if (!Mage::helper('downloadable')->getIsShareable($linkPurchasedItem)) {
            $customerId = $this->_getCustomerSession()->getCustomerId();
            if (!$customerId) {
                $product = Mage::getModel('catalog/product')->load($linkPurchasedItem->getProductId());
                if ($product->getId()) {
                    $notice = Mage::helper('downloadable')->__('Please log in to download your product or purchase <a href="%s">%s</a>.', $product->getProductUrl(), $product->getName());
                } else {
                    $notice = Mage::helper('downloadable')->__('Please log in to download your product.');
                }
                $this->_getCustomerSession()->addNotice($notice);
                $this->_getCustomerSession()->authenticate($this);
                $this->_getCustomerSession()->setBeforeAuthUrl(Mage::getUrl('downloadable/customer/products/'),
                    array('_secure' => true)
                );
                return ;
            }
            $linkPurchased = Mage::getModel('downloadable/link_purchased')->load($linkPurchasedItem->getPurchasedId());
            if ($linkPurchased->getCustomerId() != $customerId) {
                $this->_getCustomerSession()->addNotice(Mage::helper('downloadable')->__("Requested link does not exist."));
                return $this->_redirect('*/customer/products');
            }
        }

        $downloadsLeft = $linkPurchasedItem->getNumberOfDownloadsBought() - $linkPurchasedItem->getNumberOfDownloadsUsed();
        $status = $linkPurchasedItem->getStatus();

        if ($status == Mage_Downloadable_Model_Link_Purchased_Item::LINK_STATUS_AVAILABLE
            && ($downloadsLeft || $linkPurchasedItem->getNumberOfDownloadsBought() == 0)
        ) {
            try {
                if ($this->_processLink($linkPurchasedItem)) {
                    return;
                }
            } catch (Exception $e) {
                $this->_getCustomerSession()->addError(
                    Mage::helper('downloadable')->__('An error occurred while getting the requested content. Please contact the store owner.')
                );
            }
        } elseif ($status == Mage_Downloadable_Model_Link_Purchased_Item::LINK_STATUS_EXPIRED) {
            $this->_getCustomerSession()->addNotice(Mage::helper('downloadable')->__('The link has expired.'));
        } elseif ($status == Mage_Downloadable_Model_Link_Purchased_Item::LINK_STATUS_PENDING
            || $status == Mage_Downloadable_Model_Link_Purchased_Item::LINK_STATUS_PAYMENT_REVIEW
        ) {
            $this->_getCustomerSession()->addNotice(Mage::helper('downloadable')->__('The link is not available.'));
        } else {
            $this->_getCustomerSession()->addError(
                Mage::helper('downloadable')->__('An error occurred while getting the requested content. Please contact the store owner.')
            );
        }

        return $this->_redirect('*/customer/products');
    }

    /**
     * Prepare the response based on link type
     *
     * @param Mage_Downloadable_Model_Link_Purchased_Item $linkPurchasedItem
     * @return Mage_Core_Controller_Varien_Action
     */
    protected function _processLink($linkPurchasedItem)
    {
        if ($linkPurchasedItem->getLinkType() == Unl_DownloadablePlus_Helper_Download::LINK_TYPE_CALLBACK) {
            $this->_sendCallback($linkPurchasedItem);
        }

        if ($linkPurchasedItem->getLinkType() == Unl_DownloadablePlus_Helper_Download::LINK_TYPE_REDIRECT) {
            $this->_useLink($linkPurchasedItem);
            $this->_redirectUrl($linkPurchasedItem->getLinkUrl());
            return true;
        }

        $resource = '';
        $resourceType = '';
        if ($linkPurchasedItem->getLinkType() == Mage_Downloadable_Helper_Download::LINK_TYPE_URL) {
            $resource = $linkPurchasedItem->getLinkUrl();
            $resourceType = Mage_Downloadable_Helper_Download::LINK_TYPE_URL;

            if ($linkPurchasedItem->getLinkSecret()) {
                Mage::helper('downloadable/download')->setUrlSecret($linkPurchasedItem->getLinkSecret());
            }
        } elseif ($linkPurchasedItem->getLinkType() == Mage_Downloadable_Helper_Download::LINK_TYPE_FILE) {
            $resource = Mage::helper('downloadable/file')->getFilePath(
                Mage_Downloadable_Model_Link::getBasePath(), $linkPurchasedItem->getLinkFile()
            );
            $resourceType = Mage_Downloadable_Helper_Download::LINK_TYPE_FILE;
        }

        $this->_processDownload($resource, $resourceType);
        $this->_useLink($linkPurchasedItem);
        exit(0);
    }

    /**
     * Forms a callback/hook request to the link URL and changes the
     * given item's URL and type to a redirect for the URI returned
     * from the hook response.
     *
     * @param Mage_Downloadable_Model_Link_Purchased_Item $linkPurchasedItem
     * @throws Zend_Uri_Exception
     * @throws Exception
     */
    protected function _sendCallback($linkPurchasedItem)
    {
        $helper = Mage::helper('downloadable/download');
        $linkPurchased = Mage::getModel('downloadable/link_purchased')->load($linkPurchasedItem->getPurchasedId());
        $order = Mage::getModel('sales/order')->load($linkPurchased->getOrderId());

        $callbackData = array(
            'customer_name' => $order->getCustomerName(),
            'customer_email' => $order->getCustomerEmail(),
            'order_id' => $order->getId(),
            'order_item_id' => $linkPurchased->getOrderItemId(),
            'product_sku' => $linkPurchased->getProductSku(),
            'product_name' => $linkPurchased->getProductName(),
            'product_id' => $linkPurchasedItem->getProductId(),
            'item_id' => $linkPurchasedItem->getId(),
            'created_at' => $linkPurchasedItem->getCreatedAt(),
            'uses' => $linkPurchasedItem->getNumberOfDownloadsUsed(),
        );

        $rawBody = http_build_query($callbackData, '', '&');

        $client = new Zend_Http_Client($linkPurchasedItem->getLinkUrl(), array(
            'maxredirects' => 0,
            'useragent' => 'Magento-Hook/' . Mage::getVersion(),
            'timeout' => 30,
            'adapter' => 'Zend_Http_Client_Adapter_Curl',
        ));
        $client->setRawData($rawBody, $client::ENC_URLENCODED);

        if ($linkPurchasedItem->getLinkSecret()) {
            $client->setHeaders('X-Hook-Signature', $helper->getHMAC($rawBody, $linkPurchasedItem->getLinkSecret()));
        }

        $response = $client->request($client::POST);

        if ($response->isSuccessful()) {
            $uriCandidate = $response->getBody();
            $linkPurchasedItem->setLinkUrl(Zend_Uri_Http::fromString($uriCandidate)->getUri());
            $linkPurchasedItem->setLinkType(Unl_DownloadablePlus_Helper_Download::LINK_TYPE_REDIRECT);
        } else {
            throw new Exception('Bad callback response');
        }
    }

    /**
     * Increments the number of link uses and updates status, if necessary
     *
     * @param Mage_Downloadable_Model_Link_Purchased_Item $linkPurchasedItem
     */
    protected function _useLink($linkPurchasedItem)
    {
        $downloadsLeft = $linkPurchasedItem->getNumberOfDownloadsBought() - $linkPurchasedItem->getNumberOfDownloadsUsed();

        $linkPurchasedItem->setNumberOfDownloadsUsed($linkPurchasedItem->getNumberOfDownloadsUsed() + 1);

        if ($linkPurchasedItem->getNumberOfDownloadsBought() != 0 && !($downloadsLeft - 1)) {
            $linkPurchasedItem->setStatus(Mage_Downloadable_Model_Link_Purchased_Item::LINK_STATUS_EXPIRED);
        }
        $linkPurchasedItem->save();
    }
}

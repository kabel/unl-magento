<?php

class Unl_Core_Block_GoogleAnalytics_Ga extends Mage_GoogleAnalytics_Block_Ga
{
    protected function _getCommandPrefix($trackerName)
    {
        if (!empty($trackerName)) {
            return $this->jsQuoteEscape($trackerName) . '.';
        }

        return '';
    }

    protected function _getVersion()
    {
        return Mage::getStoreConfig(Unl_Core_Helper_GoogleAnalytics::XML_PATH_VERSION);
    }

    protected function _augmentLegacyJs($code, $trackerName)
    {
        $cmdPrefix = $this->_getCommandPrefix($trackerName);

        $code = str_replace("_gaq.push(['_", "_gaq.push(['{$cmdPrefix}_", $code);

        return $code;
    }

    /* Overrides
     * @see Mage_GoogleAnalytics_Block_Ga::_getPageTrackingCode()
     */
    protected function _getPageTrackingCode($accountId, $trackerName = '', $domain = '')
    {
        // augment parent logic for ga.js
        if ($this->_getVersion() != Unl_Core_Helper_GoogleAnalytics::VERSION_ANALYTICS) {
            $code = parent::_getPageTrackingCode($accountId);

            if ($domain) {
                $code = str_replace("_gaq.push(['_trackPageview", "_gaq.push(['_setAllowLinker', true]);
_gaq.push(['_trackPageview", $code);
            }

            if ($trackerName) {
                $code = $this->_augmentLegacyJs($code, $trackerName);
            }

            return $code;
        }

        // New logic to support analytics.js
        $cmdPrefix = $this->_getCommandPrefix($trackerName);
        $pageName   = trim($this->getPageName());
        $optPageURL = '';
        if ($pageName && preg_match('/^\/.*/i', $pageName)) {
            $optPageURL = ", '{$this->jsQuoteEscape($pageName)}'";
        }

        $code = array();

        $optCreate = "'{$this->jsQuoteEscape($accountId)}', 'auto'";
        $optCreateJson = array();
        if (!empty($trackerName)) {
            $optCreateJson['name'] = $trackerName;
        }
        if (!empty($domain)) {
            $optCreateJson['allowLinker'] = true;
        }
        if (!empty($optCreateJson)) {
            $optCreate .= ', ' . Mage::helper('core')->jsonEncode($optCreateJson);
        }

        $code[] = "ga('create', {$optCreate});";
        $code[] = $this->_getAnonymizationCode();
        $code[] = "ga('{$cmdPrefix}send', 'pageview'{$optPageURL});";

        return implode("\n", $code);
    }

    protected function _getOrdersTrackingCode($trackerName = '')
    {
        // augment parent logic for ga.js
        if ($this->_getVersion() != Unl_Core_Helper_GoogleAnalytics::VERSION_ANALYTICS) {
            $result = parent::_getOrdersTrackingCode();

            if ($trackerName) {
                $result = $this->_augmentLegacyJs($result, $trackerName);
            }

            return $result;
        }

        // New logic to support analytics.js
        $cmdPrefix = $this->_getCommandPrefix($trackerName);
        $orderIds = $this->getOrderIds();
        if (empty($orderIds) || !is_array($orderIds)) {
            return;
        }
        $collection = Mage::getResourceModel('sales/order_collection')
            ->addFieldToFilter('entity_id', array('in' => $orderIds));

        $result = array("ga('{$cmdPrefix}require', 'ecommerce', 'ecommerce.js');");

        foreach ($collection as $order) {
            $json = Mage::helper('core')->jsonEncode(array(
                'id' => $order->getIncrementId(),
                'affiliation' => Mage::app()->getStore()->getFrontendName(),
                'revenue' => $order->getBaseGrandTotal(),
                'tax' => $order->getBaseTaxAmount(),
                'shipping' => $order->getBaseShippingAmount(),
            ));
            $result[] = "ga('{$cmdPrefix}ecommerce:addTransaction', {$json});";

            foreach ($order->getAllVisibleItems() as $item) {
                $json = Mage::helper('core')->jsonEncode(array(
                    'id' => $order->getIncrementId(),
                    'name' => $item->getName(),
                    'sku' => $item->getSku(),
                    'price' => $item->getBasePrice(),
                    'quantity' => $item->getQtyOrdered(),
                ));
                $result[] = "ga('{$cmdPrefix}ecommerce:addItem', {$json});";
            }

            $result[] = "ga('{$cmdPrefix}ecommerce:send');";
        }
        return implode("\n", $result);
    }


    protected function _getAnonymizationCode($trackerName = '')
    {
        if (!Mage::helper('googleanalytics')->isIpAnonymizationEnabled()) {
            return '';
        }

        // augment parent logic for ga.js
        if ($this->_getVersion() != Unl_Core_Helper_GoogleAnalytics::VERSION_ANALYTICS) {
            $code = parent::_getAnonymizationCode();

            if ($trackerName) {
                $code = $this->_augmentLegacyJs($code, $trackerName);
            }

            return $code;
        }

        // New logic to support analytics.js
        $cmdPrefix = $this->_getCommandPrefix($trackerName);
        return "ga('{$cmdPrefix}set', 'anonymizeIp', true);";
    }
}

<?php

class Unl_Core_Block_GoogleAnalytics_Ga extends Mage_GoogleAnalytics_Block_Ga
{
    const XML_PATH_OUTPUT_SCRIPT = 'google/analytics/output_script';

    /* Overrides
     * @see Mage_GoogleAnalytics_Block_Ga::_toHtml()
     * by conditionally outputting the GA lib code
     */
    protected function _toHtml()
    {
        if (!Mage::helper('googleanalytics')->isGoogleAnalyticsAvailable()) {
            return '';
        }
        $accountId = Mage::getStoreConfig(Mage_GoogleAnalytics_Helper_Data::XML_PATH_ACCOUNT);
        $altAccountId = Mage::getStoreConfig(Unl_Core_Helper_GoogleAnalytics::XML_PATH_ALT_ACCOUNT);
        $altDomain = Mage::getStoreConfig(Unl_Core_Helper_GoogleAnalytics::XML_PATH_ALT_DOMAIN);
        return '
<!-- BEGIN GOOGLE ANALYTICS CODE -->
<script type="text/javascript">
//<![CDATA[' . $this->_getAnalyticsScriptCode() . '
    var _gaq = _gaq || [];
' . $this->_getPageTrackingCode($accountId) . '
' . $this->_getOrdersTrackingCode() . '
' . ($altAccountId ? $this->_getPageTrackingCode($altAccountId, 'alt', $altDomain) : '') . '
//]]>
</script>
<!-- END GOOGLE ANALYTICS CODE -->';
    }

    /* Overrides
     * @see Mage_GoogleAnalytics_Block_Ga::_getPageTrackingCode()
     * by allowing a named tracker and domain to set (linker)
     */
    protected function _getPageTrackingCode($accountId, $trackerName = '', $domain = '')
    {
        $pageName   = trim($this->getPageName());
        $optPageURL = '';
        if ($pageName && preg_match('/^\/.*/i', $pageName)) {
            $optPageURL = ", '{$this->jsQuoteEscape($pageName)}'";
        }

        if (!empty($trackerName)) {
            $trackerName = $this->jsQuoteEscape($trackerName) . '.';
        }

        $code = "
_gaq.push(['{$trackerName}_setAccount', '{$this->jsQuoteEscape($accountId)}']);";

        if (!empty($domain)) {
            $code .= "
_gaq.push(['{$trackerName}_setDomainName', '{$this->jsQuoteEscape($domain)}']);
_gaq.push(['{$trackerName}_setAllowLinker', true]);";
        }

        $code .= "
_gaq.push(['{$trackerName}_trackPageview'{$optPageURL}]);
";

        return $code;
    }

    protected function _getAnalyticsScriptCode()
    {
        if (!Mage::getStoreConfigFlag(self::XML_PATH_OUTPUT_SCRIPT)) {
            return '';
        }

        return '
    (function() {
        var ga = document.createElement(\'script\'); ga.type = \'text/javascript\'; ga.async = true;
        ga.src = (\'https:\' == document.location.protocol ? \'https://ssl\' : \'http://www\') + \'.google-analytics.com/ga.js\';
        (document.getElementsByTagName(\'head\')[0] || document.getElementsByTagName(\'body\')[0]).appendChild(ga);
    })();' . "\n";
    }
}

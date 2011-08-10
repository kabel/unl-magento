<?php

class Unl_Core_Block_GoogleAnalytics_Ga extends Mage_GoogleAnalytics_Block_Ga
{
    const XML_PATH_OUTPUT_SCRIPT = 'google/analytics/output_script';
    const XML_PATH_TRACK_PAGE_LOAD = 'google/analytics/track_page_load';

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
        return '
<!-- BEGIN GOOGLE ANALYTICS CODE -->
<script type="text/javascript">
//<![CDATA[' . $this->_getAnalyticsScriptCode() . '
    var _gaq = _gaq || [];
' . $this->_getPageTrackingCode($accountId) . '
' . $this->_getPageLoadTrackingCode() . '
' . $this->_getOrdersTrackingCode() . '
//]]>
</script>
<!-- END GOOGLE ANALYTICS CODE -->';
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

    protected function _getPageLoadTrackingCode()
    {
        if (!Mage::getStoreConfigFlag(self::XML_PATH_TRACK_PAGE_LOAD)) {
            return '';
        }

        return "
_gaq.push(['_trackPageLoadTime']);
";
    }
}

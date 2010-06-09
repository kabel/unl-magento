<?php

class Unl_Core_Block_GoogleAnalytics_Ga extends Mage_GoogleAnalytics_Block_Ga
{
    /**
     * Prepare and return block's html output
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!Mage::getStoreConfigFlag('google/analytics/active')) {
            return '';
        }

        $this->addText('
<!-- BEGIN GOOGLE ANALYTICS CODE -->
<script type="text/javascript">
//<![CDATA[
    var _gaq = _gaq || [];
    _gaq.push(["_setAccount", "' . $this->getAccount() . '"]);
    _gaq.push(["_trackPageview", "'.$this->getPageName().'"]);
//]]>
</script>
<!-- END GOOGLE ANALYTICS CODE -->
        ');

        $this->addText($this->getQuoteOrdersHtml());

        if ($this->getGoogleCheckout()) {
            $protocol = Mage::app()->getStore()->isCurrentlySecure() ? 'https' : 'http';
            $this->addText('<script src="'.$protocol.'://checkout.google.com/files/digital/ga_post.js" type="text/javascript"></script>');
        }

        return Mage_Core_Block_Text::_toHtml();
    }
}
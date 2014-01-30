<?php

class Unl_Core_Block_Page_Html extends Mage_Page_Block_Html
{
    /* Overrides
     * @see Mage_Page_Block_Html::getPrintLogoUrl()
     * by using URL fetching methods to prevent mixed content warnings
     */
    public function getPrintLogoUrl ()
    {
        // load html logo
        $logo = Mage::getStoreConfig('sales/identity/logo_html');
        if (!empty($logo)) {
            $logo = 'sales/store/logo_html/' . $logo;
        }

        // load default logo
        if (empty($logo)) {
            $logo = Mage::getStoreConfig('sales/identity/logo');
            if (!empty($logo)) {
                // prevent tiff format displaying in html
                if (strtolower(substr($logo, -5)) === '.tiff' || strtolower(substr($logo, -4)) === '.tif') {
                    $logo = '';
                }
                else {
                    $logo = 'sales/store/logo/' . $logo;
                }
            }
        }

        // buld url
        if (!empty($logo)) {
            $logo = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . $logo;
        }
        else {
            $logo = '';
        }

        return $logo;
    }
}

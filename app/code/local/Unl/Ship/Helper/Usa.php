<?php

class Unl_Ship_Helper_Usa extends Mage_Usa_Helper_Data
{
    public function displayGirthValue($shippingMethod)
    {
        if (in_array($shippingMethod, array(
            'usps_0_FCLE', //First-Class Mail Large Envelope
            'usps_1', // Priority Mail
            'usps_2', // Priority Mail Express Hold For Pickup
            'usps_3', // Priority Mail Express
            'usps_4', // Standard Post
            'usps_6', // Media Mail
            'usps_INT_1', // Priority Mail Express International
            'usps_INT_2', // Priority Mail International
            'usps_INT_4', // Global Express Guaranteed (GXG)
            'usps_INT_7', // Global Express Guaranteed Non-Document Non-Rectangular
            'usps_INT_8', // Priority Mail International Flat Rate Envelope
            'usps_INT_9', // Priority Mail International Medium Flat Rate Box
            'usps_INT_10', // Priority Mail Express International Flat Rate Envelope
            'usps_INT_11', // Priority Mail International Large Flat Rate Box
            'usps_INT_12', // USPS GXG Envelopes
            'usps_INT_14', // First-Class Mail International Large Envelope
            'usps_INT_16', // Priority Mail International Small Flat Rate Box
            'usps_INT_20', // Priority Mail International Small Flat Rate Envelope
            'usps_INT_26', // Priority Mail Express International Flat Rate Boxes
        ))) {
            return true;
        } else {
            return false;
        }
    }
}

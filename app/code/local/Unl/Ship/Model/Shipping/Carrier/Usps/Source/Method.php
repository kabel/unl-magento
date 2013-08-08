<?php

class Unl_Ship_Model_Shipping_Carrier_Usps_Source_Method extends Mage_Usa_Model_Shipping_Carrier_Usps_Source_Method
{
    /* Overrides
     * @see Mage_Usa_Model_Shipping_Carrier_Usps_Source_Method::toOptionArray()
     * to match MAGE PATCH
     */
    public function toOptionArray()
    {
        $usps = Mage::getSingleton('usa/shipping_carrier_usps');
        $arr = array();
        foreach ($usps->getCode('method') as $k => $v) {
            $arr[] = array('value'=>$k, 'label'=>$v);
        }
        return $arr;
    }
}

<?php

interface Unl_Ship_Model_Shipping_Carrier_VoidInterface
{
    public function isVoidAvailable();

    public function requestToVoid($data, $quiet);
    
    public function getLastVoidResult();
}

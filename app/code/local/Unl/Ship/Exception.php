<?php

class Unl_Ship_Exception extends Zend_Exception
{
    const SHIPMENT_CREATE_SUCCESS        = 0;
    const SHIPMENT_CREATE_ERROR_NONFATAL = 1;
    const SHIPMENT_CREATE_ERROR_FATAL    = 2;
}

<?php

class Unl_Core_Model_Payment_Method_Cash extends Mage_Payment_Model_Method_Abstract
{
    protected $_code                    = 'cash';
    protected $_canUseInternal          = true;
    protected $_canUseCheckout          = false;
    protected $_canUseForMultishipping  = false;
}

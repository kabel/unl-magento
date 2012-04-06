<?php

class Unl_Cas_Model_Session extends Mage_Core_Model_Session_Abstract
{
    public function __construct()
    {
        $auth = Mage::helper('unl_cas')->getAuth();

        $this->init($auth->getSessionNamespace());
    }
}

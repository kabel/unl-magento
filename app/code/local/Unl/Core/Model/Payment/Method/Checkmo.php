<?php

class Unl_Core_Model_Payment_Method_Checkmo extends Mage_Payment_Model_Method_Checkmo
{
    public function getAllowForcePay()
    {
        return true;
    }
}

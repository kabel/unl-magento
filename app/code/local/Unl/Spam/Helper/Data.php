<?php

class Unl_Spam_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getRemoteAddr($asBinary = true)
    {
        $remoteAddr = Mage::helper('core/http')->getRemoteAddr();

        if ($asBinary) {
            return inet_pton($remoteAddr);
        }

        return $remoteAddr;
    }

    public function getCidrMask($bits, $bitLength)
    {
        $mask = str_repeat('f', $bits >> 2);
        switch ($bits & 3) {
            case 3:
                $mask .= 'e';
                break;
            case 2:
                $mask .= 'c';
                break;
            case 1:
                $mask .= '8';
                break;
        }
        $mask = str_pad($mask, $bitLength >> 2, '0');

        return pack('H*', $mask);
    }
}

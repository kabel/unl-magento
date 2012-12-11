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

    public function getCidrBits($mask)
    {
        $mask = bin2hex($mask);
        $count = substr_count($mask, 'f') << 2;

        switch ($mask[strlen($mask)-1]) {
            case 'e':
                $count += 3;
                break;
            case 'c':
                $count += 2;
                break;
            case '8':
                $count += 1;
                break;
        }

        return $count;
    }
}

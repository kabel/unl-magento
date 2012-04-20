<?php

class Unl_Core_Helper_Backup_Rsync extends Mage_Core_Helper_Abstract
{
    public function getKeyPath()
    {
        return Mage::getBaseDir('var') . DS . 'rsync_id_rsa';
    }
}

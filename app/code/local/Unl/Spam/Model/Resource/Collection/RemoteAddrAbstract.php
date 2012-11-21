<?php

abstract class Unl_Spam_Model_Resource_Collection_RemoteAddrAbstract extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    public function _afterLoad()
    {
        foreach ($this->_items as $item) {
            $item->swapRemoteAddr();
        }
        return parent::_afterLoad();
    }
}

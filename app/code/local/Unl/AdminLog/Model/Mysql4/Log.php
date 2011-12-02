<?php

class Unl_AdminLog_Model_Mysql4_Log extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
        $this->_init('unl_adminlog/log', 'log_id');
    }

    public function clean($archiveTime, $cleanTime)
    {
        if ($archiveTime > 0 && ($cleanTime == 0 || $archiveTime < $cleanTime)) {
            $this->_getWriteAdapter()->update($this->getTable('unl_adminlog/log'),
                array('is_archived' => 1),
                array(
                	'created_at < ?' => gmdate('Y-m-d H-i-s', time() - $archiveTime),
                	'is_archived = ?' => 0
                )
            );
        }

        if ($cleanTime > 0) {
            $this->_getWriteAdapter()->delete($this->getTable('unl_adminlog/log'),
                array('created_at < ?' => gmdate('Y-m-d H-i-s', time() - $cleanTime))
            );
        }

        return $this;
    }
}

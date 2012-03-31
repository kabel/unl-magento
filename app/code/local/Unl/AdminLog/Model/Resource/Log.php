<?php

class Unl_AdminLog_Model_Resource_Log extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('unl_adminlog/log', 'log_id');
    }

    public function clean($archiveTime, $cleanTime)
    {
        $writeAdapter = $this->_getWriteAdapter();

        if ($archiveTime > 0 && ($cleanTime == 0 || $archiveTime < $cleanTime)) {
            $writeAdapter->update($this->getTable('unl_adminlog/log'),
                array('is_archived' => 1),
                array(
                	'created_at < ?' => $this->formatDate(Mage::getModel('core/date')->gmtTimestamp() - $archiveTime),
                	'is_archived = ?' => 0
                )
            );
        }

        if ($cleanTime > 0) {
            $writeAdapter->delete($this->getTable('unl_adminlog/log'),
                array('created_at < ?' => $this->formatDate(Mage::getModel('core/date')->gmtTimestamp() - $cleanTime))
            );
        }

        return $this;
    }
}

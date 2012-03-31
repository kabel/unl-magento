<?php

class Unl_Core_Model_Log_Cron extends Mage_Log_Model_Cron
{
    /* Overrides
     * @see Mage_Log_Model_Cron::logClean()
     * by ensuring the exception trace is a string
     */
    public function logClean()
    {
        if (!Mage::getStoreConfigFlag(self::XML_PATH_LOG_CLEAN_ENABLED)) {
            return $this;
        }

        $this->_errors = array();

        try {
            Mage::getModel('log/log')->clean();
        }
        catch (Exception $e) {
            $this->_errors[] = $e->getMessage();
            $this->_errors[] = $e->getTraceAsString();
        }

        $this->_sendLogCleanEmail();

        return $this;
    }
}

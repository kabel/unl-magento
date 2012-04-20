<?php

class Unl_Core_Helper_Backup_Data extends Mage_Backup_Helper_Data
{
    protected $_previousMaintenanceStatus = false;

    public function turnOnMaintenanceMode()
    {
        $this->_previousMaintenanceStatus = $this->isMaintenanceOn();
        $maintenanceFlagFile = $this->getMaintenanceFlagFilePath();
        $result = true;

        if (!$_previousMaintenanceStatus) {
            $result = touch($maintenanceFlagFile);
        }

        return $result !== false;
    }

    public function turnOffMaintenanceMode($force = false)
    {
        if (!$force && $this->_previousMaintenanceStatus) {
            return;
        }

        parent::turnOffMaintenanceMode();
    }

    public function isMaintenanceOn()
    {
        return file_exists($this->getMaintenanceFlagFilePath());
    }

    protected function getMaintenanceFlagFilePath()
    {
        return Mage::getBaseDir() . DS . '.htmaint';
    }
}

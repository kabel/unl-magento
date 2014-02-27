<?php

class Unl_Core_Model_Backup_Observer extends Mage_Backup_Model_Observer
{
    const XML_PATH_RSYNC_ENABLED = 'system/rsync/enabled';
    const XML_PATH_RSYNC_RETAIN  = 'system/rsync/retain';
    const XML_PATH_RSYNC_USER    = 'system/rsync/user';
    const XML_PATH_RSYNC_HOST    = 'system/rsync/host';
    const XML_PATH_RSYNC_PATH    = 'system/rsync/path';

    protected $_tmpCronLog;

    protected $_emptyDirPath;

    /* Overrides
     * @see Mage_Backup_Model_Observer::scheduledBackup()
     * by adding rsync logic
     */
    public function scheduledBackup()
    {
        if (!Mage::getStoreConfigFlag(self::XML_PATH_BACKUP_ENABLED)) {
            return $this;
        }

        if (Mage::getStoreConfigFlag(self::XML_PATH_BACKUP_MAINTENANCE_MODE)) {
            Mage::helper('backup')->turnOnMaintenanceMode();
        }

        if (Mage::getStoreConfigFlag(self::XML_PATH_RSYNC_ENABLED)) {
            $this->_clearBackups();
        }

        $type = Mage::getStoreConfig(self::XML_PATH_BACKUP_TYPE);

        $this->_errors = array();
        try {
            $backupManager = Mage_Backup::getBackupInstance($type)
                ->setBackupExtension(Mage::helper('backup')->getExtensionByType($type))
                ->setTime(time())
                ->setBackupsDir(Mage::helper('backup')->getBackupsDir());

            Mage::register('backup_manager', $backupManager);

            if ($type != Mage_Backup_Helper_Data::TYPE_DB) {
                $backupManager->setRootDir(Mage::getBaseDir())
                    ->addIgnorePaths(Mage::helper('backup')->getBackupIgnorePaths());
            }

            $backupManager->create();
            Mage::log(Mage::helper('backup')->getCreateSuccessMessageByType($type));

            // try to rsync
            $this->_rsyncBackup();
        }
        catch (Exception $e) {
            $this->_errors[] = $e->getMessage();
            $this->_errors[] = $e->getTrace();
            Mage::log($e->getMessage(), Zend_Log::ERR);
            Mage::logException($e);
        }

        if (Mage::getStoreConfigFlag(self::XML_PATH_BACKUP_MAINTENANCE_MODE)) {
            Mage::helper('backup')->turnOffMaintenanceMode();
        }

        return $this;
    }

    /**
     * Removes all existing backup files
     *
     * @return Unl_Core_Model_Backup_Observer
     */
    protected function _clearBackups()
    {
        $baseDir = Mage::getBaseDir('var') . DS . 'backups';

        foreach (glob($baseDir . DS . '*') as $file) {
            @unlink($file);
        }

        return $this;
    }

    /**
     * Public API to perform an rsync backup
     *
     * @return Unl_Core_Model_Backup_Observer
     */
    public function forceRsync()
    {
        return $this->_rsyncBackup();
    }

    /**
     * Performs an rsync backup of the backups directory, if enabled in config
     *
     * @throws Exception
     * @return Unl_Core_Model_Backup_Observer
     */
    protected function _rsyncBackup()
    {
        $host = Mage::getStoreConfig(self::XML_PATH_RSYNC_HOST);

        if (!Mage::getStoreConfigFlag(self::XML_PATH_RSYNC_ENABLED) || empty($host)) {
            return $this;
        }

        $retainCount = intval(Mage::getStoreConfig(self::XML_PATH_RSYNC_RETAIN));
        if ($retainCount == 0) {
            $retainCount = 7;
        }
        $retain = date('z') % $retainCount;

        $keyPath = Mage::helper('unl_core/backup_rsync')->getKeyPath();
        if (!file_exists($keyPath)) {
            throw new Exception('Missing rsync ssh key during scheduled backup.');
        }

        $user = Mage::getStoreConfig(self::XML_PATH_RSYNC_USER);
        $path = rtrim(Mage::getStoreConfig(self::XML_PATH_RSYNC_PATH), '/');
        if (empty($path)) {
            $path = '.';
        }

        $baseCmd = 'rsync --delete -aze "ssh -q -o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no -o BatchMode=yes -i ' . $keyPath . '"';
        $errorRedir = ' 2>>' . $this->_getTmpCronLog();

        // clear last incremental backup
        $this->_createEmptyDirIfNotExists();
        $remotePath = "$host:$path/$retain/";
        if ($user) {
            $remotePath = "$user@" . $remotePath;
        }

        $output = null;
        $return = 0;
        exec($baseCmd . ' ' . $this->_getEmptyDirPath() . DS . ' ' . escapeshellarg(rtrim($remotePath, '/')) . $errorRedir, $output, $return);
        if ($return != 0) {
            return $this->_handleCronError();
        }

        $this->_removeEmptyDir();

        // copy backups
        exec($baseCmd . ' --exclude=".ht*" ' . Mage::getBaseDir('var') . DS . 'backups' . DS . ' ' . escapeshellarg($remotePath) . $errorRedir, $output, $return);

        return $this->_handleCronError();
    }

    /**
     * Transfers the contents of the temporary cron log (redirected resync StdErr output)
     * to the cron.log
     *
     * @return Unl_Core_Model_Backup_Observer
     */
    protected function _handleCronError()
    {
        $tmpErrorLog = $this->_getTmpCronLog();
        if (file_exists($tmpErrorLog)) {
            $errors = file_get_contents($tmpErrorLog);
            if ($errors) {
                Mage::log($errors, Zend_Log::WARN, 'cron.log');
            }
            unlink($tmpErrorLog);
        }

        return $this;
    }

    /**
     * Returns the path to the temporary cron log
     *
     * @return string
     */
    protected function _getTmpCronLog()
    {
        if (!$this->_tmpCronLog) {
            $this->_tmpCronLog = Mage::getBaseDir('var') . DS . 'log' . DS . 'tmpCron.log';
        }

        return $this->_tmpCronLog;
    }

    /**
     * Returns the path to the empty directory
     *
     * @return string
     */
    protected function _getEmptyDirPath()
    {
        if (!$this->_emptyDirPath) {
            $this->_emptyDirPath = Mage::getBaseDir('var') . DS . 'emptydir';
        }

        return $this->_emptyDirPath;
    }

    /**
     * Creates an empty directory for clearing old backup files
     *
     * @return Unl_Core_Model_Backup_Observer
     */
    protected function _createEmptyDirIfNotExists()
    {
        $dir = $this->_getEmptyDirPath();
        $ioProxy = new Varien_Io_File();
        $ioProxy->setAllowCreateFolders(true);
        $ioProxy->createDestinationDir($dir);

        return $this;
    }

    /**
     * Removes the empty directory used for clearing old backup files
     *
     * @return Unl_Core_Model_Backup_Observer
     */
    protected function _removeEmptyDir()
    {
        $dir = $this->_getEmptyDirPath();
        $ioProxy = new Varien_Io_File();
        $ioProxy->setAllowCreateFolders(true);
        $ioProxy->rmdir($dir);

        return $this;
    }
}

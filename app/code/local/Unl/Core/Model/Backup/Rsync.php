<?php

class Unl_Core_Model_Backup_Rsync
{
    const XML_PATH_RSYNC_ENABLED = 'system/rsync/enabled';
    const XML_PATH_RSYNC_USER    = 'system/rsync/user';
    const XML_PATH_RSYNC_HOST    = 'system/rsync/host';
    const XML_PATH_RSYNC_PATH    = 'system/rsync/path';

    /**
     * Generate the rsync key pair on config save
     *
     * @param   Varien_Event_Observer $observer
     * @return  Unl_Core_Model_Backup_Rsync
     */
    public function onSystemConfigSave($observer)
    {
        if (!Mage::getStoreConfigFlag(self::XML_PATH_RSYNC_ENABLED)) {
            return $this;
        }

        $keyPath = $this->_getKeyPath();
        if (!file_exists($keyPath)) {
            exec('ssh-keygen -q -b 2048 -t rsa -N "" -f ' . $keyPath);

            if (!file_exists($keyPath)) {
                Mage::throwException(Mage::helper('unl_core')->__('Unable to create rsync key pair.'));
            }

            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('unl_core')->__('Successfully created rsync key pair. You will need to add the public key to your remote user\'s authorized_keys file.'));
        }

        return $this;
    }

    public function doRsyncBackup($schedule)
    {
        $host = Mage::getStoreConfig(self::XML_PATH_RSYNC_HOST);
        if (!Mage::getStoreConfigFlag(self::XML_PATH_RSYNC_ENABLED) || empty($host)) {
            return $this;
        }

        $keyPath = $this->_getKeyPath();
        $user = Mage::getStoreConfig(self::XML_PATH_RSYNC_USER);
        $path = rtrim(Mage::getStoreConfig(self::XML_PATH_RSYNC_PATH), '/');
        $today = date('l');
        $baseCmd = 'rsync --delete -aze "ssh -o StrictHostKeyChecking=no -i ' . $keyPath . '"';

        // clear last weeks incremental backup
        $this->_createEmptyDirIfNotExists();
        $remotePath = "$host:$path/$today/";
        if ($user) {
            $remotePath = "$user@" . $remotePath;
        }
        exec($baseCmd . ' ' . $this->_getEmptyDirPath() . DS . ' ' . escapeshellarg(rtrim($remotePath, '/')));
        $this->_removeEmptyDir();

        // copy DB backups
        exec($baseCmd . ' ' . Mage::getBaseDir('var') . DS . 'backups ' . escapeshellarg($remotePath));
        // copy all media
        exec($baseCmd . ' ' . Mage::getBaseDir('media') . ' ' . escapeshellarg($remotePath));

        return $this;
    }

    protected function _getKeyPath()
    {
        return Mage::getBaseDir('var') . DS . 'rsync_id_rsa';
    }

    protected function _getEmptyDirPath()
    {
        return Mage::getBaseDir('var') . DS . 'emptydir';
    }

    protected function _createEmptyDirIfNotExists()
    {
        $dir = $this->_getEmptyDirPath();
        $ioProxy = new Varien_Io_File();
        $ioProxy->setAllowCreateFolders(true);
        $ioProxy->createDestinationDir($dir);

        return $this;
    }

    protected function _removeEmptyDir()
    {
        $dir = $this->_getEmptyDirPath();
        $ioProxy = new Varien_Io_File();
        $ioProxy->setAllowCreateFolders(true);
        $ioProxy->rmdir($dir);

        return $this;
    }
}

<?php

class Unl_Core_Model_Backup_Config_Backend_Rsync extends Mage_Core_Model_Config_Data
{
    const XML_PATH_RSYNC_ENABLED  = 'groups/rsync/fields/enabled/value';

    /**
     * Cron settings after save
     *
     * @return Mage_Adminhtml_Model_System_Config_Backend_Log_Cron
     */
    protected function _afterSave()
    {
        $enabled = $this->getData(self::XML_PATH_RSYNC_ENABLED);

        if (!$enabled) {
            return;
        }

        try {
            $keyPath = Mage::helper('unl_core/backup_rsync')->getKeyPath();

            if (!file_exists($keyPath)) {
                exec('ssh-keygen -q -b 2048 -t rsa -N "" -f ' . $keyPath);

                if (!file_exists($keyPath)) {
                    throw new Exception('ssh-keygen failed');
                }

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('unl_core')->__('Successfully created rsync key pair. You will need to add the public key to your remote user\'s authorized_keys file.'));
            }
        } catch (Exception $e) {
            Mage::throwException(Mage::helper('unl_core')->__('Unable to create rsync key pair.'));
        }
    }
}

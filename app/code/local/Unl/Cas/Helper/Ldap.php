<?php

class Unl_Cas_Helper_Ldap extends Mage_Core_Helper_Abstract
{
    const XML_PATH_ADMIN_UNL_LDAP_SERVER = 'admin/unl_ldap/server';
    const XML_PATH_ADMIN_UNL_LDAP_BASEDN = 'admin/unl_ldap/basedn';
    const XML_PATH_ADMIN_UNL_LDAP_BINDDN = 'admin/unl_ldap/binddn';
    const XML_PATH_ADMIN_UNL_LDAP_BINDPW = 'admin/unl_ldap/bindpw';

    /**
     * Searches for and attempts to bind to the given user
     * using the user's username as LDAP uid
     *
     * @param Mage_Admin_Model_User $user
     * @param string $password
     * @throws Mage_Core_Exception
     * @return Unl_Cas_Helper_Ldap
     */
    public function authenticate($user, $password)
    {
        $config = array(
            'server' => Mage::getStoreConfig(self::XML_PATH_ADMIN_UNL_LDAP_SERVER),
            'basedn' => Mage::getStoreConfig(self::XML_PATH_ADMIN_UNL_LDAP_BASEDN),
            'binddn' => Mage::getStoreConfig(self::XML_PATH_ADMIN_UNL_LDAP_BINDDN),
            'bindpw' => Mage::getStoreConfig(self::XML_PATH_ADMIN_UNL_LDAP_BINDPW),
        );

        try {
            if (empty($config['server'])) {
                Mage::throwException($this->__('LDAP Authentication Missing Server'));
            }

            $ldap = ldap_connect($config['server']);
            if (!$ldap) {
                Mage::throwException($this->__('LDAP Authentication Server Connection Error'));
            }

            if (!empty($config['binddn']) || !empty($config['bindpw'])) {
                if (!ldap_bind($ldap, $config['binddn'], $config['bindpw'])) {
                    Mage::throwException($this->__('LDAP Authentication Server Connection Error'));
                }
            }

            $search = ldap_search($ldap, $config['basedn'], "(uid={$user->getUsername()})", array('uid'));
            $sResult = ldap_get_entries($ldap, $search);

            if (!is_array($sResult) || count($sResult) <= 1) {
                Mage::throwException($this->__('Access Denied'));
            }

            if (!ldap_bind($ldap, $sResult[0]['dn'], $password)) {
                Mage::throwException($this->__('Invalid Username or Password'));
            }

            unset($sResult);
        } catch (Mage_Core_Exception $e) {
            if ($ldap) {
                ldap_close($ldap);
            }
            $user->unsetData();
            throw $e;
        }

        ldap_close($ldap);

        return $this;
    }
}

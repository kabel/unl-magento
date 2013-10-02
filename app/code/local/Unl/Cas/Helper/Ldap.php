<?php

class Unl_Cas_Helper_Ldap extends Mage_Core_Helper_Abstract
{
    const XML_PATH_CUSTOMER_UNL_LDAP_ACTIVE = 'customer/unl_ldap/active';
    const XML_PATH_CUSTOMER_UNL_LDAP_SERVER = 'customer/unl_ldap/server';
    const XML_PATH_CUSTOMER_UNL_LDAP_BASEDN = 'customer/unl_ldap/basedn';
    const XML_PATH_CUSTOMER_UNL_LDAP_BINDDN = 'customer/unl_ldap/binddn';
    const XML_PATH_CUSTOMER_UNL_LDAP_BINDPW = 'customer/unl_ldap/bindpw';
    const XML_PATH_CUSTOMER_UNL_LDAP_CACHE  = 'customer/unl_ldap/cache';

    const XML_PATH_ADMIN_UNL_LDAP_SERVER = 'admin/unl_ldap/server';
    const XML_PATH_ADMIN_UNL_LDAP_BASEDN = 'admin/unl_ldap/basedn';
    const XML_PATH_ADMIN_UNL_LDAP_BINDDN = 'admin/unl_ldap/binddn';
    const XML_PATH_ADMIN_UNL_LDAP_BINDPW = 'admin/unl_ldap/bindpw';

    const CACHE_TAG = 'UNL_LDAP';
    const CACHE_KEY_PREFIX = 'unl_ldap_data_';

    protected $_fastCache = array();

    protected $_affiliationMap = array(
        Unl_Cas_Helper_Data::CUSTOMER_TAG_STUDENT => array(
            'student'
        ),
        Unl_Cas_Helper_Data::CUSTOMER_TAG_FACULTY_STAFF => array(
            'faculty',
            'emeriti',
            'staff',
            'continue services',
            'affiliate',
            'volunteer',
        ),
    );

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
            ldap_set_option($ldap, LDAP_OPT_PROTOCOL_VERSION, 3);

            if (!$ldap || !ldap_start_tls($ldap)) {
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

    /**
     * Fetches an LDAP result using the <code>UNL_Peoplefinder</code>
     * service wrapper. The wrapper allows the use of multiple drivers,
     * so a failover is provided if LDAP isn't configured or has errors.
     *
     * @param string $uid
     * @return UNL_Peoplefinder_Record|null
     */
    public function loadLdap($uid)
    {
        $cacheKey = $this->_getCacheKey($uid);
        if ($r = $this->_loadCache($cacheKey)) {
            return $r;
        }

        if (Mage::getStoreConfigFlag(self::XML_PATH_CUSTOMER_UNL_LDAP_ACTIVE)) {
            UNL_Peoplefinder_Driver_LDAP::$ldapServer = Mage::getStoreConfig(self::XML_PATH_CUSTOMER_UNL_LDAP_SERVER);
            UNL_Peoplefinder_Driver_LDAP::$baseDN = Mage::getStoreConfig(self::XML_PATH_CUSTOMER_UNL_LDAP_BASEDN);
            UNL_Peoplefinder_Driver_LDAP::$bindDN = Mage::getStoreConfig(self::XML_PATH_CUSTOMER_UNL_LDAP_BINDDN);
            UNL_Peoplefinder_Driver_LDAP::$bindPW = Mage::getStoreConfig(self::XML_PATH_CUSTOMER_UNL_LDAP_BINDPW);

            if (substr(UNL_Peoplefinder_Driver_LDAP::$ldapServer, 0, 6) === 'ldaps:') {
                UNL_Peoplefinder_Driver_LDAP::$ldapTls = true;
            }

            $driver = new UNL_Peoplefinder_Driver_LDAP();
        } else {
            $driver = null;
        }

        do {
            $retry = false;
            $pf = new UNL_Peoplefinder($driver);
            try {
                $r = $pf->getUID($uid);
                $this->_saveCache($r, $cacheKey);
            } catch (Exception $e) {
                if ($e->getCode() != 404) {
                    Mage::logException($e);
                    if (null !== $driver) {
                        $driver = null;
                        $retry = true;
                    }
                }
                $r = null;
            }
        } while ($retry);

        return $r;
    }

    /**
     * Returns if a given uid exists in LDAP
     *
     * @param string $uid
     * @return boolean
     */
    public function isInLdap($uid)
    {
        return $this->loadLdap($uid) !== null;
    }

    /**
     * Loads LDAP data into missing values of a Varien_Object
     *
     * @param Varien_Object $data
     * @return Unl_Cas_Helper_Ldap
     */
    public function populateLdapData($data)
    {
        $uid = Mage::helper('unl_cas')->getAuth()->getUser();
        if ($r = $this->loadLdap($uid)) {
            if (empty($data['email']) && isset($r->mail)) {
                $data['email'] = (string)$r->mail;
            }

            if (empty($data['firstname'])) {
                if (isset($r->eduPersonNickname)) {
                    $data['firstname'] = (string)$r->eduPersonNickname;
                } else {
                    $data['firstname'] = (string)$r->givenName;
                }
            }

            if (empty($data['lastname'])) {
                $data['lastname'] = (string)$r->sn;
            }
        }

        return $this;
    }

    /**
     * Returns the LDAP display name for a given or authenticated user
     *
     * @param string|false $uid
     * @return string
     */
    public function getDisplayName($uid = false)
    {
        if (!$uid) {
            $uid = Mage::helper('unl_cas')->getAuth()->getUser();
        }

        if ($r = $this->loadLdap($uid)) {
            if (isset($r->displayName)) {
                return (string)$r->displayName;
            }
        }
        return $uid;
    }

    /**
     * Checks for known faculty/staff affiliations in LDAP
     * using the affiliation map
     *
     * @param string $uid
     * @return boolean
     */
    public function isFacultyStaff($uid)
    {
        if ($r = $this->loadLdap($uid)) {
            foreach ($r->eduPersonAffiliation as $affil) {
                if (in_array($affil, $this->_affiliationMap[Unl_Cas_Helper_Data::CUSTOMER_TAG_FACULTY_STAFF])) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Checks for known student affiliations in LDAP
     * using the affiliation map
     *
     * @param string $uid
     * @return boolean
     */
    public function isStudent($uid)
    {
        if ($r = $this->loadLdap($uid)) {
            foreach ($r->eduPersonAffiliation as $affil) {
                if (in_array($affil, $this->_affiliationMap[Unl_Cas_Helper_Data::CUSTOMER_TAG_STUDENT])) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Gets the CAS Authentication model
     *
     * @see Unl_Cas_Helper_Data::getAuth()
     */
    protected function _getCasAuth()
    {
        return Mage::helper('unl_cas')->getAuth();
    }

    /**
     * Returns a unique cache id for all LDAP results
     *
     * @param string $uid
     * @return string
     */
    protected function _getCacheKey($uid)
    {
        return self::CACHE_KEY_PREFIX . $uid;
    }

    /**
     * Returns the number of seconds an LDAP record should be slow
     * cached. If the configured value is less than 60 minutes,
     * false is returned.
     *
     * @return boolean|number
     */
    protected function _getCacheLifetime()
    {
        $lifetime = (int)Mage::getStoreConfig(self::XML_PATH_CUSTOMER_UNL_LDAP_CACHE);
        if ($lifetime < 60) {
            return false;
        }

        return $lifetime * 60;
    }

    /**
     * Loads data from the internal cache, falling back on the app cache
     *
     * @param string $id
     * @return mixed
     */
    protected function _loadCache($id)
    {
        if (isset($this->_fastCache[$id])) {
            return $this->_fastCache[$id];
        }

        $data = parent::_loadCache($id);
        if ($data) {
            $data = unserialize($data);
        }

        return $data;
    }

    /**
     * Saves data to the internal cache and app cache
     *
     * @param   mixed $data
     * @param   string $id
     * @param   array $tags
     * @return  Unl_Cas_Helper_Ldap
     */
    protected function _saveCache($data, $id, $tags = array(), $lifeTime = false)
    {
        $this->_fastCache[$id] = $data;
        if ($lifeTime === false) {
            $lifeTime = $this->_getCacheLifetime();
        }
        $tags = array_merge($tags, array(self::CACHE_TAG));
        parent::_saveCache(serialize($data), $id, $tags, $lifeTime);

        return $this;
    }
}

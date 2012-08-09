<?php
class UNL_Peoplefinder_Driver_LDAP implements UNL_Peoplefinder_DriverInterface
{
    /**
     * Connection credentials
     * 
     * @param string
     */
    static public $ldapServer = 'ldap.unl.edu ldap-backup.unl.edu';
    
    /**
     * LDAP Connection bind distinguised name
     *
     * @var string
     * @ignore
     */
    static public $bindDN = 'uid=insertyouruidhere,ou=service,dc=unl,dc=edu';
    
    /**
     * LDAP connection password.
     *
     * @var string
     * @ignore
     */
    static public $bindPW             = 'putyourpasswordhere';
    static public $baseDN             = 'ou=people,dc=unl,dc=edu';
    static public $ldapTimeout        = 10;
    
    /**
     * Attribute arrays
     * Attributes are the fields retrieved in an LDAP QUERY, limit this to
     * ONLY what is USED/DISPLAYED!
     */
    
    /**
     * List attributes are the attributes displayed in a list of results
     * 
     * @var array
     */
    public $listAttributes = array(
        'cn',
        'eduPersonNickname',
        'eduPersonAffiliation',
        'eduPersonPrimaryAffiliation',
        'givenName',
        'sn',
        'telephoneNumber',
        'title',
        'uid',
        'unlHRPrimaryDepartment',
        'unlHROrgUnitNumber');
    
    /**
     * Details are for UID detail display only.
     * @var array
     */
    public $detailAttributes = array(
        'ou',
        'cn',
        'eduPersonAffiliation',
        'eduPersonNickname',
        'eduPersonPrimaryAffiliation',
        'givenName',
        'displayName',
        'mail',
        'postalAddress',
        'sn',
        'telephoneNumber',
        'title',
        'uid',
        'unlHROrgUnitNumber',
        'unlHRPrimaryDepartment',
        'unlHRAddress',
        'unlSISClassLevel',
        'unlSISCollege',
        'unlSISLocalAddr1',
        'unlSISLocalAddr2',
        'unlSISLocalCity',
        'unlSISLocalState',
        'unlSISLocalZip',
        'unlSISPermAddr1',
        'unlSISPermAddr2',
        'unlSISPermCity',
        'unlSISPermState',
        'unlSISPermZip',
        'unlSISMajor',
        'unlEmailAlias');
    
    /** Connection details */
    public $connected = false;
    public $linkID;

    /** Result Info */
    public $lastQuery;
    public $lastResult;
    public $lastResultCount = 0;
    
    function __construct()
    {
        
    }
    
    /**
     * Binds to the LDAP directory using the bind credentials stored in
     * bindDN and bindPW
     *
     * @return bool
     */
    function bind()
    {
        $this->linkID = ldap_connect(self::$ldapServer);

        if (!ldap_set_option($this->linkID, LDAP_OPT_PROTOCOL_VERSION, 3)) {
            throw new Exception('Could not set LDAP_OPT_PROTOCOL_VERSION to 3', 500);
        }

        if (!$this->linkID) {
            throw new Exception('ldap_connect failed! Cound not connect to the LDAP directory.', 500);
        }

        if (!ldap_start_tls($this->linkID)) {
            throw new Exception('Could not connect using StartTLS!', 500);
        }

        $this->connected = ldap_bind($this->linkID,
                                     self::$bindDN,
                                     self::$bindPW);
        if (!$this->connected) {
            throw new Exception('ldap_bind failed! Could not connect to the LDAP directory.', 500);
        }

        return $this->connected;
    }
    
    /**
     * Disconnect from the ldap directory.
     *
     * @return unknown
     */
    function unbind()
    {
        $this->connected = false;
        return ldap_unbind($this->linkID);
    }
    
    /**
     * Send a query to the ldap directory
     *
     * @param string $filter     LDAP filter (uid=blah)
     * @param array  $attributes attributes to return for the entries
     * @param bool   $setResult  whether or not to set the last result
     * 
     * @return mixed
     */
    function query($filter,$attributes,$setResult=true)
    {
        $this->bind();
        $this->lastQuery = $filter;
        $sr              = @ldap_search($this->linkID, 
                                        self::$baseDN,
                                        $filter,
                                        $attributes,
                                        0,
                                        UNL_Peoplefinder::$resultLimit,
                                        self::$ldapTimeout);
        if ($setResult) {
            $this->lastResultCount = @ldap_count_entries($this->linkID, $sr);
            $this->lastResult      = @ldap_get_entries($this->linkID, $sr);
            $this->unbind();
            $this->lastResult = $this->caseInsensitiveSortLDAPResults($this->lastResult);
            return $this->lastResult;
        } else {
            $result = ldap_get_entries($this->linkID, $sr);
            $this->unbind();
            return $result;
        }
    }

    protected function caseInsensitiveSortLDAPResults($result)
    {
        if (!is_array($result)) {
            return $result;
        }
        // sort the results
        for ($i=0; $i<$result['count']; $i++) {
            $name = '';

            if (isset($result[$i]['sn'])) {
                $name = $result[$i]['sn'][0];
            }

            if (isset($result[$i]['givenname'])) {
                $name .= ', ' . $result[$i]['givenname'][0];
            }

            $result[$i]['insensitiveName'] = strtoupper($name);
        }
        reset($result);
        $result = UNL_Peoplefinder_Driver_LDAP_Util::array_csort(
                                                $result,
                                                'insensitiveName',
                                                SORT_ASC);
        return $result;
    }

    
    /**
     * Get records which match the query exactly.
     *
     * @param string $query       Search string.
     * @param string $affiliation eduPersonAffiliation, eg staff/faculty/student
     * 
     * @return array(UNL_Peoplefinder_Record)
     */
    public function getExactMatches($query, $affiliation = null)
    {
        if ($affiliation) {
            $filter = new UNL_Peoplefinder_Driver_LDAP_AffiliationFilter($query, $affiliation, '&', false);
        } else {
            $filter = new UNL_Peoplefinder_Driver_LDAP_StandardFilter($query, '&', false);
        }
        $this->query($filter->__toString(), $this->detailAttributes);
        return $this->getRecordsFromResults();
    }
    
    /**
     * Returns an array of UNL_Peoplefinder_Record objects from the ldap
     * query result.
     *
     * @return array(UNL_Peoplefinder_Record)
     */
    protected function getRecordsFromResults()
    {
        $r = array();
        if ($this->lastResultCount > 0) {
            for ($i = 0; $i < $this->lastResultCount; $i++) {
                $r[] = self::recordFromLDAPEntry($this->lastResult[$i]);
            }
        }
        return $r;
    }
    
    /**
     * Get results for an advanced/detailed search.
     *
     * @param string $sn   Surname/last name
     * @param string $cn   Common name/first name
     * @param string $eppa Primary Affiliation
     * 
     * @return array(UNL_Peoplefinder_Record)
     */
    public function getAdvancedSearchMatches($query, $affiliation = null)
    {
        $filter = new UNL_Peoplefinder_Driver_LDAP_AdvancedFilter($query['sn'], $query['cn'], $affiliation, '&', true);
        $this->query($filter->__toString(), $this->detailAttributes);
        return $this->getRecordsFromResults();
    }
    
    /**
     * Find matches similar to the query given
     *
     * @param string $query            Search query
     * @param string $affiliation      eduPersonAffiliation, eg staff/faculty/student
     * @param array  $excluded_records Array of records to exclude.
     * 
     * @return array(UNL_Peoplefinder_Record)
     */
    public function getLikeMatches($query, $affiliation = null, $excluded_records = array())
    {
        if ($affiliation) {
            $filter = new UNL_Peoplefinder_Driver_LDAP_AffiliationFilter($query, $affiliation, '&', true);
        } else {
            $filter = new UNL_Peoplefinder_Driver_LDAP_StandardFilter($query, '&', true);
        }
        // Exclude those displayed above
        $filter->excludeRecords($excluded_records);
        $this->query($filter->__toString(), $this->detailAttributes);
        return $this->getRecordsFromResults();
    }
    
    /**
     * Get an array of records which matche by the phone number.
     *
     * @param string $q           EG: 472-1598
     * @param string $affiliation eduPersonAffiliation, eg staff/faculty/student
     * 
     * @return array(UNL_Peoplefinder_Record)
     */
    public function getPhoneMatches($query, $affiliation = null)
    {
        $filter = new UNL_Peoplefinder_Driver_LDAP_TelephoneFilter($query, $affiliation);
        $this->query($filter->__toString(), $this->detailAttributes);
        return $this->getRecordsFromResults();
    }

    /**
     * Get the ldap record for a specific uid eg:bbieber2
     *
     * @param string $uid The unique ID for the user you want to get.
     * 
     * @return UNL_Peoplefinder_Record
     */
    function getUID($uid)
    {
        $filter = new UNL_Peoplefinder_Driver_LDAP_UIDFilter($uid);
        $r = $this->query($filter->__toString(), $this->detailAttributes, false);
        if (!isset($r[0])) {
            throw new Exception('Cannot find that UID.', 404);
        }
        return self::recordFromLDAPEntry($r[0]);
    }

    function getRoles($dn)
    {
        $ldap   = UNL_LDAP::getConnection(
                      array('uri'           => self::$ldapServer,
                            'base'          => self::$baseDN,
                            'suffix'        => 'ou=People,dc=unl,dc=edu',
                            'bind_dn'       => self::$bindDN,
                            'bind_password' => self::$bindPW));
        return new UNL_Peoplefinder_Person_Roles(array('iterator'=>$ldap->search($dn, '(&(objectClass=unlRole)(!(unlListingOrder=NL)))')));
    }
    
    
    public static function recordFromLDAPEntry(array $entry)
    {
        $r = new UNL_Peoplefinder_Record();
        foreach (get_object_vars($r) as $var=>$val) {
            if (isset($entry[strtolower($var)])) {
                switch(gettype($entry[strtolower($var)])) {
                    case 'string':
                        $r->$var = $entry[strtolower($var)];
                        break;
                    default:
                        $r->$var = new UNL_LDAP_Entry_Attribute($entry[strtolower($var)]);
                }
            }
        }
        $r->imageURL = $r->getImageURL();
        return $r;
    }
    
    public static function recordFromUNLLDAPEntry(UNL_LDAP_Entry $entry)
    {
        $r = new UNL_Peoplefinder_Record();
        foreach (get_object_vars($r) as $var=>$val) {
            $r->$var = $entry->$var;
        }
        $r->imageURL = $r->getImageURL();
        return $r;
    }

    public function getHRPrimaryDepartmentMatches($query, $affiliation = null)
    {
        $filter = new UNL_Peoplefinder_Driver_LDAP_HRPrimaryDepartmentFilter($query);
        $this->query($filter->__toString(), $this->detailAttributes);
        return $this->getRecordsFromResults();
    }

    public function getHROrgUnitNumberMatches($query, $affiliation = null)
    {
        $filter = new UNL_Peoplefinder_Driver_LDAP_HROrgUnitNumberFilter($query);

        // @TODO Clean up this mess. Either use UNL_LDAP entirely, or use something internal
        $this->query($filter->__toString(), $this->listAttributes);
        
        return new UNL_Peoplefinder_Department_Personnel(new ArrayIterator($this->getRecordsFromResults()));
    }
}

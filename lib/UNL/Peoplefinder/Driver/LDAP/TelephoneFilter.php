<?php
/**
 * Builds a simple telephone filter for searching for records.
 *
 * PHP version 5
 * 
 * @category  Default
 * @package   UNL_Peoplefinder
 * @author    Brett Bieber <brett.bieber@gmail.com>
 * @copyright 2007 Regents of the University of Nebraska
 * @license   http://www1.unl.edu/wdn/wiki/Software_License BSD License
 * @link      http://peoplefinder.unl.edu/
 */
class UNL_Peoplefinder_Driver_LDAP_TelephoneFilter
{
    private $_filter;
    
    protected $affiliation;
    
    function __construct($q, $affiliation = null)
    {
        if (!empty($q)) {
            $this->_filter = '(telephoneNumber=*'.str_replace('-','*',$q).')';
        }
        
        switch ($affiliation) {
            case 'faculty':
            case 'staff':
            case 'student':
                $this->affiliation = $affiliation;
                break;
        }
    }
    
    function __toString()
    {
        $this->_filter = '(&'.$this->_filter.'(!(|(ou=org)(eduPersonPrimaryAffiliation=guest)))';
        if ($this->affiliation) {
            $this->_filter .= '(eduPersonAffiliation='.$this->affiliation.')';
        }
        $this->_filter .= ')';
        return $this->_filter;
    }
}

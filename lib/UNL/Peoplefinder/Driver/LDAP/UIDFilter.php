<?php
class UNL_Peoplefinder_Driver_LDAP_UIDFilter
{
    protected $_filter;

    function __construct($uid, $affiliation = null)
    {
        $this->_filter = "(&(uid=$uid))";
    }

    function __toString()
    {
        $this->_filter = UNL_Peoplefinder_Driver_LDAP_Util::wrapGlobalExclusions($this->_filter);
        return $this->_filter;
    }
}
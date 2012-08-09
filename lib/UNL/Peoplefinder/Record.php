<?php
/**
 * Peoplefinder class for UNL's online directory.
 *
 * PHP version 5
 *
 * @package   UNL_Peoplefinder
 * @author    Brett Bieber <brett.bieber@gmail.com>
 * @copyright 2007 Regents of the University of Nebraska
 * @license   http://www1.unl.edu/wdn/wiki/Software_License BSD License
 * @link      http://peoplefinder.unl.edu/
 */
class UNL_Peoplefinder_Record
{
    public $dn; // distinguished name
    public $cn;
    public $ou;
    public $eduPersonAffiliation;
    public $eduPersonNickname;
    public $eduPersonPrimaryAffiliation;
    public $givenName;
    public $displayName;
    public $mail;
    public $postalAddress;
    public $sn;
    public $telephoneNumber;
    public $title;
    public $uid;
    public $unlHROrgUnitNumber;
    public $unlHRPrimaryDepartment;
    public $unlHRAddress;
    public $unlSISClassLevel;
    public $unlSISCollege;
//    public $unlSISLocalAddr1;
//    public $unlSISLocalAddr2;
//    public $unlSISLocalCity;
//    public $unlSISLocalPhone;
//    public $unlSISLocalState;
//    public $unlSISLocalZip;
//    public $unlSISPermAddr1;
//    public $unlSISPermAddr2;
//    public $unlSISPermCity;
//    public $unlSISPermState;
//    public $unlSISPermZip;
    public $unlSISMajor;
    public $unlSISMinor;
    public $unlEmailAlias;

    function __construct($options = array())
    {
        if (isset($options['uid'])
            && $options['peoplefinder']) {
            return $options['peoplefinder']->getUID($options['uid']);
        }
    }



    /**
     * Takes in a string from the LDAP directory, usually formatted like:
     *     ### ___ UNL 68588-####
     *    Where ### is the room number, ___ = Building Abbreviation, #### zip extension
     *
     * @param string
     * @return array Associative array.
     */
    function formatPostalAddress()
    {
        $parts = explode(',', $this->postalAddress);

        // Set up defaults:
        $address = array();
        $address['street-address'] = trim($parts[0]);
        $address['locality']       = '';
        $address['region']         = 'NE';
        $address['postal-code']    = '';

        if (count($parts) == 3) {
            // Assume we have a street address, city, zip.
            $address['locality'] = trim($parts[1]);
        }

        // Now lets find some important bits.
        foreach ($parts as $part) {
            if (preg_match('/([\d]{5})(\-[\d]{4})?/', $part)) {
                // Found a zip-code
                $address['postal-code'] = trim($part);
            }
        }

        switch (substr($address['postal-code'], 0, 3)) {
            case '681':
                $address['locality'] = 'Omaha';
                break;
            case '685':
                $address['locality'] = 'Lincoln';
                break;
        }

        return $address;
    }

    /**
     * Formats a major subject code into a text description.
     *
     * @param string $subject Subject code for the major eg: MSYM
     *
     * @return string
     */
    public function formatMajor($subject)
    {

        $c = new UNL_Cache_Lite();
        $majors = $c->get('catalog majors');

        if (!$majors) {
            if ($majors = file_get_contents('http://bulletin.unl.edu/undergraduate/majors/lookup/?format=json')) {
                $c->save($majors);
            } else {
                $c->extendLife();
                $c->get('catalog majors');
            }
        }

        $majors = json_decode($majors, true);

        if (array_key_exists($subject, $majors)) {
            return $majors[$subject];
        }

        return $subject;
    }

    /**
     * Format a three letter college abbreviation into the full college name.
     *
     * @param string $college College abbreviation = FPA
     *
     * @return string College of Fine &amp; Performing Arts
     */
    public function formatCollege($college)
    {
        include_once 'UNL/Common/Colleges.php';
        $colleges = new UNL_Common_Colleges();
        if (isset($colleges->colleges[$college])) {
            return htmlentities($colleges->colleges[$college]);
        }

        return $college;
    }

    function getImageURL($size = 'medium')
    {

        if ($this->ou == 'org') {
            return UNL_Peoplefinder::getURL().'images/organization.png';
        }

        switch ($size) {
            case 'large':
            case 'medium':
            case 'small':
            case 'tiny':
            case 'topbar':
                break;
            default:
                $size = 'medium';
        }

        return 'http://planetred.unl.edu/pg/icon/unl_'.str_replace('-', '_', $this->uid).'/'.$size.'/';
    }

    function __toString()
    {
        return (string)$this->uid;
    }
}


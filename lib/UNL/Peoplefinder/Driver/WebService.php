<?php
class UNL_Peoplefinder_Driver_WebService implements UNL_Peoplefinder_DriverInterface
{
    /**
     * The address to the webservice
     *
     * @var string
     */
    public $service_url = 'http://peoplefinder.unl.edu/service.php';

    function __construct($options = array())
    {
        if (isset($options['service_url'])) {
            $this->service_url = $options['service_url'];
        }
    }

    function getExactMatches($query, $affiliation = null)
    {
        $results = file_get_contents($this->service_url.'?q='.urlencode($query).'&format=php&affiliation='.urlencode($affiliation).'&method=getExactMatches');
        if ($results) {
            $results = unserialize($results);
        }
        return $results;
    }

    function getAdvancedSearchMatches($query, $affliation = null)
    {
        if (empty($affiliation)) {
            $affiliation = '';
        }
        $results = file_get_contents($this->service_url.'?sn='.urlencode($query['sn']).'&cn='.urlencode($query['cn']).'&format=php&affiliation='.urlencode($affiliation).'&method=getAdvancedSearchMatches');
        if ($results) {
            $results = unserialize($results);
        }
        return $results;
    }

    function getLikeMatches($query, $affiliation = null, $excluded_records = array())
    {
        $results = file_get_contents($this->service_url.'?q='.urlencode($query).'&format=php&affiliation='.urlencode($affiliation).'&method=getLikeMatches');

        if ($results) {
            $results = unserialize($results);
        }

        if (count($excluded_records)) {
            foreach ($results as $i=>$record) {
                foreach($excluded_records as $e=>$exclude) {
                    if ((string)$exclude->uid == (string)$record->uid) {
                        unset($results[$i]);
                        break;
                    }
                }
            }
        }

        return $results;
    }

    /**
     * Get matches for a phone search
     *
     * @param string $query       Numerical search query
     * @param string $affiliation eduPersonAffiliation, eg, student, staff, faculty
     *
     * @return UNL_Peoplefinder_SearchResults
     */
    function getPhoneMatches($query, $affiliation = null)
    {
        $results = file_get_contents($this->service_url.'?q='.urlencode($query).'&format=php&affiliation='.urlencode($affiliation).'&method=getPhoneMatches');
        if ($results) {
            $results = unserialize($results);
        }
        return $results;
    }

    /**
     * Get an individual's record within the directory.
     *
     * @param string $uid Unique ID for the user, eg: bbieber2
     *
     * @return UNL_Peoplefinder_Record
     */
    function getUID($uid)
    {
        $record = @file_get_contents($this->service_url.'?uid='.urlencode($uid).'&format=php');

        if (false === $record) {
            throw new Exception('Could not find that user!', 404);
        }

        return unserialize($record);
    }

    function getHRPrimaryDepartmentMatches($query, $affiliation = null)
    {
        $results = @file_get_contents($this->service_url.'?q=d:'.urlencode($query).'&format=php&affiliation='.urlencode($affiliation).'&method=getHRPrimaryDepartmentMatches');
        if ($results) {
            $results = unserialize($results);
        }
        return $results;
    }

    public function getHROrgUnitNumberMatches($query, $affiliation = null)
    {
        throw new Exception('Not implemented yet!');
    }
}

<?php

class Unl_DownloadablePlus_Model_Link_Api_Validator extends Mage_Downloadable_Model_Link_Api_Validator
{
    protected $_uploadTypes = array('file', 'url', 'callback');

    /* Overrides
     * @see Mage_Downloadable_Model_Link_Api_Validator::_dispatch()
     * to prevent PHP warnings on valid input
     */
    protected function _dispatch(&$resource, $fields)
    {
        foreach ($fields as $name => $validator) {
            if (array_key_exists($name, $resource)) {
                if (is_string($validator) && strlen($validator) > 0) {
                    $call = 'validate' . $validator;
                    $this->$call($resource[$name]);
                } elseif (is_array($validator)) {
                    $this->_dispatch($resource[$name], $validator);
                }
            }
        }
    }

    /* Overrides
     * @see Mage_Downloadable_Model_Link_Api_Validator::completeCheck()
     * to prevent PHP warnings on valid input
     */
    public function completeCheck(&$resource, $resourceType)
    {
        if ($resourceType == 'link') {
            if ($resource['type'] == 'file') {
                $this->validateFileDetails($resource['file']);
            }
            if (in_array($resource['type'], array('url', 'callback')) && empty($resource['link_url'])) {
                throw new Exception('empty_url');
            }

            // sample
            if (isset($resource['sample'])) {
                if ($resource['sample']['type'] == 'file') {
                    $this->validateFileDetails($resource['sample']['file']);
                }
                if ($resource['sample']['type'] == 'url' && empty($resource['sample']['url'])) {
                    throw new Exception('empty_url');
                }
            }
        }
        if ($resourceType == 'sample') {
            if ($resource['type'] == 'file') {
                $this->validateFileDetails($resource['file']);
            }
            if ($resource['type'] == 'url' && empty($resource['sample_url'])) {
                throw new Exception('empty_url');
            }
        }
    }
}

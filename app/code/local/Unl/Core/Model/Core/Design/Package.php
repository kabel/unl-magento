<?php

class Unl_Core_Model_Core_Design_Package extends Mage_Core_Model_Design_Package
{
    protected $_varySet = false;

    /* Extends
     * @see Mage_Core_Model_Design_Package::_checkUserAgentAgainstRegexps()
     * by adding an event to prevent default behavior and setting a header
     */
    protected function _checkUserAgentAgainstRegexps($regexpsConfigPath)
    {
        $configValueSerialized = Mage::getStoreConfig($regexpsConfigPath, $this->getStore());

        if (!$configValueSerialized) {
            return false;
        }

        $regexps = @unserialize($configValueSerialized);

        if (empty($regexps)) {
            return false;
        }

        if (!$this->_varySet) {
            $this->_varySet = true;
            Mage::app()->getResponse()->setHeader('Vary', 'User-Agent');
        }

        $result = new Varien_Object(array('prevent_default' => false));
        Mage::dispatchEvent('core_design_check_useragent_exps_before', array(
            'design_package' => $this,
            'result' => $result,
        ));
        if ($result->getPreventDefault()) {
            return false;
        }

        return parent::_checkUserAgentAgainstRegexps($regexpsConfigPath);
    }
}

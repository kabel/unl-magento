<?php

class Unl_Cas_Block_Form_Register extends Mage_Customer_Block_Form_Register
{
    protected function _prepareLayout()
    {
        $this->getLayout()->getBlock('head')->setTitle(Mage::helper('customer')->__('Link New Customer Account'));
        return Mage_Directory_Block_Data::_prepareLayout();
    }

    /**
     * Retrieve form posting url
     *
     * @return string
     */
    public function getPostActionUrl()
    {
        return $this->helper('unl_cas')->getRegisterPostUrl();
    }

    /**
     * Retrieve form data
     *
     * @return Varien_Object
     */
    public function getFormData()
    {
        $data = $this->getData('form_data');
        if (is_null($data)) {
            $data = parent::getFormData();
            Mage::helper('unl_cas/ldap')->populateLdapData($data);
            $this->setData('form_data', $data);
        }
        return $data;
    }

    public function getDisplayName()
    {
        return Mage::helper('unl_cas/ldap')->getDisplayName();
    }
}

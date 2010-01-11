<?php

class Unl_Cas_Block_Form_Register extends Mage_Directory_Block_Data
{
    protected function _prepareLayout()
    {
        $this->getLayout()->getBlock('head')->setTitle(Mage::helper('customer')->__('Link New Customer Account'));
        return parent::_prepareLayout();
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
            $data = new Varien_Object(Mage::getSingleton('customer/session')->getCustomerFormData(true));
            $this->_loadPfData($data);
            $this->setData('form_data', $data);
        }
        return $data;
    }
    
    /**
     * Add peoplefinder data to Varian Object
     *
     * @param $data Varien_Object
     */
    protected function _loadPfData($data)
    {
        $user = Mage::helper('unl_cas')->getAuth()->getUser();
        $pf = new UNL_Peoplefinder();
        if ($r = $pf->getUID($user)) {
            if (empty($data['email']) && !empty($r->mail)) {
                if (isset($r->unlEmailAlias)) {
                    $data['email'] = $r->unlEmailAlias . '@unl.edu';
                } else {
                    $data['email'] = $r->mail;
                }
            }
            
            if (empty($data['firstname'])) {
                $data['firstname'] = $r->givenName;
            }
            
            if (empty($data['lastname'])) {
                $data['lastname'] = $r->sn;
            }
        }
    }

    /**
     * Retrieve customer country identifier
     *
     * @return int
     */
    public function getCountryId()
    {
        if ($countryId = $this->getFormData()->getCountryId()) {
            return $countryId;
        }
        return parent::getCountryId();
    }

    /**
     * Retrieve customer region identifier
     *
     * @return int
     */
    public function getRegion()
    {
        if ($region = $this->getFormData()->getRegion()) {
            return $region;
        }
        elseif ($region = $this->getFormData()->getRegionId()) {
            return $region;
        }
        return null;
    }
    
    public function getDisplayName()
    {
        $user = Mage::helper('unl_cas')->getAuth()->getUser();
        $pf = new UNL_Peoplefinder();
        if ($r = $pf->getUID($user)) {
            if (isset($r->displayName)) {
                return $r->displayName;
            }
        }
        return $user;
    }

    /**
     *  Newsletter module availability
     *
     *  @return	  boolean
     */
    public function isNewsletterEnabled()
    {
        return !Mage::getStoreConfigFlag('advanced/modules_disable_output/Mage_Newsletter');
    }
}
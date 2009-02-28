<?php

class Unl_Core_Model_Admin_User extends Mage_Admin_Model_User
{
    /**
     * Save user
     *
     * @return Mage_Admin_Model_User
     */
    public function save()
    {
        $data = array(
            'firstname' => $this->getFirstname(),
            'lastname'  => $this->getLastname(),
            'email'     => $this->getEmail(),
            'modified'  => now(),
            'extra'     => serialize($this->getExtra()),
            'store'     => ($this->getStore() == '') ? null : $this->getStore()
        );

        if($this->getId() > 0) {
            $data['user_id'] = $this->getId();
        }

        if( $this->getUsername() ) {
            $data['username'] = $this->getUsername();
        }

        if ($this->getPassword()) {
            $data['password'] = $this->_getEncodedPassword($this->getPassword());
        }

        if ($this->getNewPassword()) {
            $data['password'] = $this->_getEncodedPassword($this->getNewPassword());
        }

        if ( !is_null($this->getIsActive()) ) {
            $data['is_active'] = intval($this->getIsActive());
        }

        $this->setData($data);
        $this->_getResource()->save($this);
        return $this;
    }
}
<?php

class Unl_Email_Model_Core_Resource_Email_Template extends Mage_Core_Model_Resource_Email_Template
{
    /* Extends
     * @see Mage_Core_Model_Resource_Email_Template::_beforeSave()
     * by moving created_at to proper added_at storage
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        parent::_beforeSave($object);

        if ($object->getCreatedAt()) {
            $object->setAddedAt($object->getCreatedAt());
        }

        return $this;
    }
}

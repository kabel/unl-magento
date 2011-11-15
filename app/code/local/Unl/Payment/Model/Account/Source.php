<?php

class Unl_Payment_Model_Account_Source extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    public function getAllOptions($withEmpty = true)
    {
        if (is_null($this->_options)) {
            $options = array();

            foreach ($this->getAccountCollection() as $account) {
                $options[] = array('label' => $account->getName(), 'value' => $account->getId());
            }

            $this->_options = $options;
        }

        $options = $this->_options;
        if ($withEmpty) {
            array_unshift($options, array('value'=>'', 'label' => ''));
        }

        return $options;
    }

    public function getAccountCollection()
    {
        $collection = Mage::getModel('unl_payment/account')->getResourceCollection();
        $collection->addScopeFilter(Mage::helper('unl_core')->getAdminUserScope(true));

        return $collection;
    }

    public function getOptionArray()
    {
        return $this->getAllOptions(false);
    }

    public function toOptionArray()
    {
        return $this->getAllOptions(false);
    }

    public function toOptionHash()
    {
        $options = $this->toOptionArray();
        $hash = array();
        foreach ($options as $option) {
            $hash[$option['value']] = $option['label'];
        }

        return $hash;
    }
}

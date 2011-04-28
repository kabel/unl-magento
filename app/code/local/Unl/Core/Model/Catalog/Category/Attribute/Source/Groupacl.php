<?php

class Unl_Core_Model_Catalog_Category_Attribute_Source_Groupacl
    extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    /**
     * Retrieve All options
     *
     * @return array
     */
    public function getAllOptions()
    {
        if (is_null($this->_options)) {
            /* @var $groups Mage_Customer_Model_Entity_Group_Collection */
            $groups = Mage::getModel('customer/group')->getCollection();
            $groups->setRealGroupsFilter();
            $this->_options = $groups->toOptionArray();
        }
        return $this->_options;
    }
}

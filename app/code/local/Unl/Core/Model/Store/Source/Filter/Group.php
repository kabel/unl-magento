<?php

class Unl_Core_Model_Store_Source_Filter_Group extends Unl_Core_Model_Store_Source_Filter
{
    public function getAllOptions($withEmpty = true)
    {
        if (is_null($this->_options)) {
            $options = array();
            $scope = Mage::helper('unl_core')->getAdminUserScope(true);

            foreach ($this->getWebsiteCollection() as $_website) {
                foreach ($this->getGroupCollection($_website) as $_group) {
                    if (empty($scope) || in_array($_group->getId(), $scope)) {
                        $options[$_group->getId()] = $_group->getName();
                    }
                }
            }

            $this->_options = $options;
        }

        $options = array();
        if ($withEmpty) {
            $options[''] = '';
        }

        return $options + $this->_options;
    }
}

<?php

class Unl_Core_Block_Adminhtml_Cms_Page_Grid extends Mage_Adminhtml_Block_Cms_Page_Grid
{
    /**
     * Extends parent by adding scope filters
     *
     * @param $collection Mage_Cms_Model_Resource_Page_Collection
     */
    public function setCollection($collection)
    {
        if ($scope = Mage::helper('unl_core')->getAdminUserScope()) {
            $connection = $collection->getConnection();
            $where = array(array('null' => true));
            foreach ($scope as $store_id) {
                $where[] = array('finset' => $store_id);
            }

            $collection->addFieldToFilter('permissions', $where);
        }

        return parent::setCollection($collection);
    }
}

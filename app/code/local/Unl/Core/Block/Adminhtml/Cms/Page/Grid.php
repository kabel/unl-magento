<?php

class Unl_Core_Block_Adminhtml_Cms_Page_Grid extends Mage_Adminhtml_Block_Cms_Page_Grid
{
    /* Overrides
     * @see Mage_Adminhtml_Block_Cms_Page_Grid::_prepareCollection()
     * by adding scope filter
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('cms/page')->getCollection();
        /* @var $collection Mage_Cms_Model_Mysql4_Page_Collection */
        $collection->setFirstStoreFlag(true);

        if ($scope = Mage::helper('unl_core')->getAdminUserScope()) {
            $connection = $collection->getConnection();
            $where = array(array('null' => true));
            foreach ($scope as $store_id) {
                $where[] = array('finset' => $store_id);
            }

            $collection->addFieldToFilter('permissions', $where);
        }

        $this->setCollection($collection);

        return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
    }
}

<?php

class Unl_Core_Block_Adminhtml_Report_Review_Product_Grid extends Mage_Adminhtml_Block_Report_Review_Product_Grid
{
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('reports/review_product_collection')
            ->joinReview();

        $user  = Mage::getSingleton('admin/session')->getUser();
        if (!is_null($user->getScope())) {
            $collection->addAttributeToFilter('source_store_view', array('in' => explode(',', $user->getScope())));
        }
            
        $this->setCollection($collection);

        return Mage_Adminhtml_Block_Widget_Grid::_prepareCollection();
    }
}

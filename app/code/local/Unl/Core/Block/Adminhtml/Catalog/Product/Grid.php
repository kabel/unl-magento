<?php

class Unl_Core_Block_Adminhtml_Catalog_Product_Grid extends Mage_Adminhtml_Block_Catalog_Product_Grid
{

    /* Extends
     * @see Mage_Adminhtml_Block_Widget_Grid::setCollection()
     * by joining source_store_view attribute
     */
    public function setCollection($collection)
    {
        $collection->addAttributeToSelect('source_store_view');
        parent::setCollection($collection);
    }

    /* Extends
     * @see Mage_Adminhtml_Block_Catalog_Product_Grid::_prepareColumns()
     * by changing columns
     */
    protected function _prepareColumns()
    {
        $this->addColumnAfter('source_store',
            array(
                'header'=> Mage::helper('catalog')->__('Source Store'),
                'width' => '100px',
                'sortable'  => false,
                'index'     => 'source_store_view',
                'type'      => 'options',
                'options'   => Mage::getModel('unl_core/store_source_filter')->toOptionArray(),
        ), 'status');

        parent::_prepareColumns();

        $this->removeColumn('entity_id')
            ->removeColumn('websites');

        return $this;
    }
}

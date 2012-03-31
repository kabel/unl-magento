<?php

abstract class Unl_Core_Block_Adminhtml_Report_Sales_Bursar_Abstract
    extends Unl_Core_Block_Adminhtml_Widget_Grid_Multicontainer
{
    protected $_blockGroup = 'unl_core';
    protected $_controllerGroup = 'bursar';
    protected $_controllerTitle = 'Bursar Report';
    protected $_blockTitle;

    public function __construct()
    {
        parent::__construct();

        $this->_headerText = Mage::helper('reports')->__($this->_controllerTitle . ': ' . $this->_blockTitle);

        $this->addButton('filter_form_submit', array(
            'label'     => Mage::helper('reports')->__('Show Report'),
            'onclick'   => 'filterFormSubmit()'
        ));

        $blockTypePrefix = "{$this->_blockGroup}/adminhtml_report_sales_{$this->_controllerGroup}_{$this->_controller}_";

        $grids = array(
            'paid' => 'Paid',
            'refunded' => 'Refunded'
        );

        $gridIdPrefix = 'products_';
        $headingPrefix = 'Products ';
        foreach ($grids as $gridIdSuffix => $headingSuffix) {
            $gridId = $gridIdPrefix . $gridIdSuffix;
            $this->addGrid($gridId, $blockTypePrefix . $gridId, array(
                'grid_header' => Mage::helper('reports')->__($headingPrefix . $headingSuffix)
            ));
        }

        if ($this->_isAllowedShipping()) {
            $gridIdPrefix = 'shipping_';
            $headingPrefix = 'Shipping ';
            foreach ($grids as $gridIdSuffix => $headingSuffix) {
                $gridId = $gridIdPrefix . $gridIdSuffix;
                $this->addGrid($gridId, $blockTypePrefix . $gridId, array(
                    'grid_header' => Mage::helper('reports')->__($headingPrefix . $headingSuffix)
                ));
            }
        }
    }

    public function getFilterUrl()
    {
        $this->getRequest()->setParam('filter', null);
        return $this->getUrl('*/*/' . $this->_controller, array('_current' => true));
    }

    protected function _isAllowedShipping()
    {
        return true;
    }
}

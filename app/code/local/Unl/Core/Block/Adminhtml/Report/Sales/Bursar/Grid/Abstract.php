<?php

abstract class Unl_Core_Block_Adminhtml_Report_Sales_Bursar_Grid_Abstract
    extends Mage_Adminhtml_Block_Report_Grid_Abstract
{
    protected $_columnGroupBy = 'period';

    protected $_exportExcelUrl = '';
    protected $_exportCsvUrl   = '';

    public function __construct()
    {
        parent::__construct();
        $this->setCountTotals(true);
        $this->setCountSubTotals(true);
    }

    protected function _prepareColumns()
    {
        $this->_prepareExportTypes();
        return parent::_prepareColumns();
    }

    protected function _prepareExportTypes()
    {
        $this->addExportType($this->_getExportCsvUrl(), Mage::helper('reports')->__('CSV'));
        $this->addExportType($this->_getExportExcelUrl(), Mage::helper('reports')->__('Excel'));

        return $this;
    }

    protected function _getExportExcelUrl()
    {
        return $this->_exportExcelUrl;
    }

    protected function _getExportCsvUrl()
    {
        return $this->_exportCsvUrl;
    }
}

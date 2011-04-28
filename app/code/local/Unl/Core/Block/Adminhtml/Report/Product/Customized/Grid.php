<?php

class Unl_Core_Block_Adminhtml_Report_Product_Customized_Grid extends Mage_Adminhtml_Block_Report_Grid
{
    protected $_defaultFilters = array(
            'report_from' => '',
            'report_to' => '',
            'sku' => ''
        );

    /**
     * Initialize Grid settings
     *
     */
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('unl/report/customizedgrid.phtml');
        $this->setId('gridProductsCustomized');
    }

    /**
     * Prepare collection object for grid
     *
     * @return Unl_Core_Block_Adminhtml_Report_Product_Customized_Grid
     */
    protected function _prepareCollection()
    {
        $filter = $this->getParam($this->getVarNameFilter(), null);

        if (is_null($filter)) {
            $filter = $this->_defaultFilter;
        }

        if (is_string($filter)) {
            $data = array();
            $filter = base64_decode($filter);
            parse_str(urldecode($filter), $data);

            if (!isset($data['report_from'])) {
                // getting all reports from 2001 year
                $date = new Zend_Date(mktime(0,0,0,1,1,2001));
                $data['report_from'] = $date->toString($this->getLocale()->getDateFormat('short'));
            }

            if (!isset($data['report_to'])) {
                // getting all reports from 2001 year
                $date = new Zend_Date();
                $data['report_to'] = $date->toString($this->getLocale()->getDateFormat('short'));
            }

            $this->_setFilterValues($data);
        } else if ($filter && is_array($filter)) {
            $this->_setFilterValues($filter);
        } else if(0 !== sizeof($this->_defaultFilter)) {
            $this->_setFilterValues($this->_defaultFilter);
        }

        $collection = Mage::getResourceModel('unl_core/report_product_customized_collection');
        /* @var $collection Unl_Core_Model_Mysql4_Report_Product_Customized_Collection */

        if ($this->getFilter('sku')) {
            $collection->addFieldToFilter('sku', array('like' => $this->getFilter('sku') . '%'));
        }


        if ($this->getFilter('report_from') && $this->getFilter('report_to')) {
            /**
             * Validate from and to date
             */
            try {
                $from = $this->getLocale()->date($this->getFilter('report_from'), Zend_Date::DATE_SHORT, null, false);
                $to   = $this->getLocale()->date($this->getFilter('report_to'), Zend_Date::DATE_SHORT, null, false);

                $collection->setInterval($from, $to);
            }
            catch (Exception $e) {
                $this->_errors[] = Mage::helper('reports')->__('Invalid date specified');
            }
        }

        /**
         * Getting and saving store ids for website & group
         */
        if ($this->getRequest()->getParam('store')) {
            $storeIds = array($this->getParam('store'));
        } else if ($this->getRequest()->getParam('website')){
            $storeIds = Mage::app()->getWebsite($this->getRequest()->getParam('website'))->getStoreIds();
        } else if ($this->getRequest()->getParam('group')){
            $storeIds = Mage::app()->getGroup($this->getRequest()->getParam('group'))->getStoreIds();
        } else {
            $storeIds = array('');
        }

        $storeId = array_pop($storeIds);
        if ($storeId) {
            $collection->addFieldToFilter('source_store_view', array('eq' => $storeId));
        }

        $this->setCollection($collection);

        Mage::dispatchEvent('adminhtml_widget_grid_filter_collection',
                array('collection' => $this->getCollection(), 'filter_values' => $this->_filterValues)
        );

        return $this;
    }

    /**
     * Prepare Grid columns
     *
     * @return Unl_Core_Block_Adminhtml_Report_Product_Customized_Grid
     */
    protected function _prepareColumns()
    {
        $this->addExportType('*/*/exportCustomizedCsv', Mage::helper('reports')->__('CSV'));
        $this->addExportType('*/*/exportCustomizedExcel', Mage::helper('reports')->__('Excel'));

        return parent::_prepareColumns();
    }

    public function getEmptyText()
    {
        return $this->__('No records found.');
    }

    public function getCsv()
    {
        $csv = '';
        $this->_prepareGrid();

        $data = array(
            '"' . $this->__('Period') . '"',
            '"' . $this->__('SKU') . '"',
            '"' . $this->__('Product Name') . '"',
            '"' . $this->__('Qty Ordered') . '"',
            '"' . $this->__('Order #') . '"',
            '"' . $this->__('Customer First Name') . '"',
            '"' . $this->__('Customer Last Name') . '"',
            '"' . $this->__('Options') . '"'
        );
        $csv .= implode(',', $data) . "\n";

        foreach ($this->getCollection() as $_item) {
            $_order = $_item->getOrder();
            if ($_order->getCustomerIsGuest()) {
                $customerFirstname = $_order->getBillingAddress()->getFirstname();
                $customerLastname  = $_order->getBillingAddress()->getLastname();
            } else {
                $customerFirstname = $_order->getCustomerFirstname();
                $customerLastname  = $_order->getCustomerLastname();
            }

            $data = array(
                $this->_cleanCsvValue($this->formatDate($_order->getCreatedAtDate())),
                $this->_cleanCsvValue($_item->getSku()),
                $this->_cleanCsvValue($_item->getName()),
                $this->_cleanCsvValue($_item->getQtyOrdered()),
                $this->_cleanCsvValue($_order->getIncrementId()),
                $this->_cleanCsvValue($customerFirstname),
                $this->_cleanCsvValue($customerLastname),
            );

            foreach ($_item->getProductOptionByCode('options') as $option) {
                $data[] = $this->_cleanCsvValue($option['label']);
                $data[] = $this->_cleanCsvValue($option['print_value']);
            }

            $csv .= implode(',', $data) . "\n";
        }

        return $csv;
    }

    protected function _cleanCsvValue($value)
    {
        return '"' . str_replace(array('"', '\\'), array('""', '\\\\'), $value) . '"';
    }

    public function getExcel($filename = '')
    {
        $this->_prepareGrid();

        $data = array();
        $row = array(
            $this->__('Period'),
            $this->__('SKU'),
            $this->__('Product Name'),
            $this->__('Qty Ordered'),
            $this->__('Order #'),
            $this->__('Customer First Name'),
            $this->__('Customer Last Name'),
            $this->__('Options')
        );
        $data[] = $row;

        foreach ($this->getCollection() as $_item) {
            $_order = $_item->getOrder();
            if ($_order->getCustomerIsGuest()) {
                $customerFirstname = $_order->getBillingAddress()->getFirstname();
                $customerLastname  = $_order->getBillingAddress()->getLastname();
            } else {
                $customerFirstname = $_order->getCustomerFirstname();
                $customerLastname  = $_order->getCustomerLastname();
            }


            $row = array(
                $this->formatDate($_order->getCreatedAtDate()),
                $_item->getSku(),
                $_item->getName(),
                $_item->getQtyOrdered(),
                $_order->getIncrementId(),
                $customerFirstname,
                $customerLastname
            );

            foreach ($_item->getProductOptionByCode('options') as $option) {
                $row[] = $option['label'];
                $row[] = $option['print_value'];
            }

            $data[] = $row;
        }

        $xmlObj = new Varien_Convert_Parser_Xml_Excel();
        $xmlObj->setVar('single_sheet', $filename);
        $xmlObj->setData($data);
        $xmlObj->unparse();

        return $xmlObj->getData();
    }
}

<?php

class Unl_Core_Block_Adminhtml_Report_Product_Options_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    /**
     * @var Mage_Catalog_Model_Resource_Product_Option_Collection
     */
    protected $_productOptions;

    protected $_isAllowedOrderView;

    public function __construct()
    {
        parent::__construct();
        $this->setId('productOptionsReportGrid');
        $this->setDefaultSort('order_date');
        $this->setUseAjax(true);
        $this->setPagerVisibility(false);

        $this->_defaultFilter = array(
            'order_date' => array('from' => strtotime('last year')),
        );

        $this->_isAllowedOrderView = Mage::getSingleton('admin/session')->isAllowed('sales/order');
    }

    /**
     * @return Mage_Catalog_Model_Product
     */
    protected function _getProduct()
    {
        return Mage::registry('current_product');
    }

    protected function _getCollectionParams()
    {
        $params = $this->getRequest()->getParam('params');
        if (is_string($params)) {
            $params = $this->helper('adminhtml')->prepareFilterString($params);
        }

        return $params;
    }

    /**
     * @return Mage_Catalog_Model_Resource_Product_Option_Collection
     */
    protected function _getProductOptions()
    {
        if (!$this->_productOptions) {
            $this->_productOptions = $this->_getProduct()->getProductOptionsCollection();
        }

        return $this->_productOptions;
    }

    protected function _getProductBundleOptions()
    {
        $product = $this->_getProduct();

        if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
            return $product->getTypeInstance(true)->getOptionsCollection($product);
        }

        return new Varien_Data_Collection();
    }

    protected function _prepareCollection()
    {
        /* @var $collection Unl_Core_Model_Resource_Report_Product_Options_Collection */
        $collection = Mage::getResourceModel('unl_core/report_product_options_collection');
        $this->setCollection($collection);

        parent::_prepareCollection();

        $params = $this->_getCollectionParams();

        if (isset($params['show_removed']) && $params['show_removed']) {
            $deletedOptions = $collection->getExcludedLoadedOptions(array_keys($this->_getProductOptions()->getItems()));

            foreach ($deletedOptions as $option) {
                $optionModel = $optionModel = Mage::getModel('catalog/product_option');

                $columnOptions = array(
                    'header' => $option['label'],
                    'type' => 'text',
                    'index' => 'option_' . $option['option_id'],
                    'renderer' => 'unl_core/adminhtml_report_product_options_grid_column_renderer_productoption',
                );


                if (isset($option['option_type'])) {
                    $optionModel->setType($option['option_type']);
                    $optionGroupType = $optionModel->getGroupByType();

                    switch ($optionGroupType) {
                        case Mage_Catalog_Model_Product_Option::OPTION_GROUP_DATE:
                            if ($option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_DATE_TIME) {
                                $columnOptions['type'] = 'datetime';
                            } else if ($option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_DATE) {
                                $columnOptions['type'] = 'date';
                            }
                            break;
                        case Mage_Catalog_Model_Product_Option::OPTION_GROUP_FILE:
                            //$columnOptions['filter'] = false;
                            break;
                    }
                }

                $this->addColumn('option_' . $option['option_id'], $columnOptions);
            }

            if ($deletedOptions) {
                $collection->clearIncludingFilters();
                parent::_prepareCollection();
            }

            // removed bundle options
            $deletedOptions = $collection->getExcludedLoadedBundleOptions(array_keys($this->_getProductBundleOptions()->getItems()));

            foreach ($deletedOptions as $option) {
                $optionModel = $optionModel = Mage::getModel('catalog/product_option');

                $columnOptions = array(
                    'header' => $option['label'],
                    'type' => 'text',
                    'index' => 'bundle_option_' . $option['option_id'],
                    'renderer' => 'unl_core/adminhtml_report_product_options_grid_column_renderer_productoption',
                    'filter' => false,
                    'sortable' => false,
                );

                $this->addColumn('bundle_option_' . $option['option_id'], $columnOptions);
            }
        }

        return $this;
    }

    protected function _preparePage()
    {
        // This grid should not be paged because the collection must be fully loaded

        return $this;
    }

    protected function _prepareColumns()
    {
        $this->addColumn('real_order_id', array(
            'header' => Mage::helper('sales')->__('Order #'),
            'type' => 'text',
            'index' => 'order_number',
        ));

        $this->addColumn('order_date', array(
            'header' => Mage::helper('sales')->__('Purchased On'),
            'index' => 'order_date',
            'type' => 'datetime',
        ));

        $this->addColumn('status', array(
            'header' => Mage::helper('sales')->__('Item Status'),
            'index' => 'status',
            'type' => 'options',
            'options' => Mage_Sales_Model_Order_Item::getStatuses(),
        ));

        $this->addColumn('qty', array(
            'header' => Mage::helper('reports')->__('Qty Ordered'),
            'index' => 'qty_adjusted',
            'type' => 'number',
        ));

        $currencyCode = Mage::app()->getStore()->getBaseCurrencyCode();
        $this->addColumn('base_total', array(
            'header' => Mage::helper('reports')->__('Item Total'),
            'type' => 'currency',
            'currency_code' => $currencyCode,
            'index' => 'base_total',
        ));

        $productOptions = $this->_getProductOptions();

        /* @var $option Mage_Catalog_Model_Product_Option */
        foreach ($productOptions as $option) {
            $columnOptions = array(
                'header' => $option->getTitle(),
                'type' => 'text',
                'index' => 'option_' . $option->getId(),
                'renderer' => 'unl_core/adminhtml_report_product_options_grid_column_renderer_productoption',
            );

            $optionGroupType = $option->getGroupByType();
            switch ($optionGroupType) {
                case Mage_Catalog_Model_Product_Option::OPTION_GROUP_SELECT:
                    $columnOptions['type'] = 'options';
                    $columnOptions['options'] = array();
                    foreach ($option->getValues() as $valueId => $optionValue) {
                        $columnOptions['options'][$valueId] = $optionValue->getTitle();
                    }
                    break;
                case Mage_Catalog_Model_Product_Option::OPTION_GROUP_DATE:
                    if ($option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_DATE_TIME) {
                        $columnOptions['type'] = 'datetime';
                    } else if ($option->getType() == Mage_Catalog_Model_Product_Option::OPTION_TYPE_DATE) {
                        $columnOptions['type'] = 'date';
                    }
                    break;
                case Mage_Catalog_Model_Product_Option::OPTION_GROUP_FILE:
                    //$columnOptions['filter'] = false;
                    break;
            }

            $this->addColumn('option_' . $option->getId(), $columnOptions);
        }

        $bundleOptions = $this->_getProductBundleOptions();
        /* @var $option Mage_Bundle_Model_Option */
        foreach ($bundleOptions as $option) {
            $columnOptions = array(
                'header' => $option->getDefaultTitle(),
                'type' => 'text',
                'index' => 'bundle_option_' . $option->getId(),
                'renderer' => 'unl_core/adminhtml_report_product_options_grid_column_renderer_productoption',
                'filter' => false,
                'sortable' => false,
            );

            $this->addColumn('bundle_option_' . $option->getId(), $columnOptions);
        }

        $this->addExportType('*/*/exportOptionsCsv', Mage::helper('reports')->__('CSV'));
        $this->addExportType('*/*/exportOptionsExcel', Mage::helper('reports')->__('Excel XML'));

        return parent::_prepareColumns();
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/optionsGrid', array('_current' => true));
    }

    public function getRowUrl($row)
    {
        return $this->_isAllowedOrderView
            ? $this->getUrl('*/sales_order/view', array('order_id' => $row->getOrderId()))
            : false;
    }
}

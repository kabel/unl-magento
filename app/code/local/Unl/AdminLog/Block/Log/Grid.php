<?php

class Unl_AdminLog_Block_Log_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $_archiveFilter = false;

    public function __construct()
    {
        parent::__construct();
        $this->setId('adminlogGrid');
        $this->setDefaultSort('time');
        $this->setDefaultDir('desc');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('unl_adminlog/log')->getCollection();
        $collection->addArchivedFilter($this->_archiveFilter);

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('time', array(
            'type' => 'datetime',
            'filter_time' => true,
            'header' => Mage::helper('unl_adminlog')->__('Time'),
            'index' => 'created_at',
        ));

        $this->addColumn('ip', array(
            'filter_index' => 'INET_NTOA(remote_addr)',
            'filter' => 'unl_adminlog/log_filter_ip',
            'renderer' => 'adminhtml/widget_grid_column_renderer_ip',
            'header' => Mage::helper('unl_adminlog')->__('IP Address'),
            'index' => 'remote_addr',
        ));

        $users = array("0" => Mage::helper('unl_adminlog')->__('[anonymous]'));
        foreach (Mage::getModel('admin/user')->getCollection() as $user) {
            $users[$user->getId()] = $user->getUsername();
        }
        asort($users);
        $this->addColumn('user', array(
            'type' => 'options',
            'options' => $users,
            'header' => Mage::helper('unl_adminlog')->__('User'),
            'index' => 'user_id',
        ));

        $this->addColumn('event', array(
            'type' => 'options',
            'options' => Mage::getSingleton('unl_adminlog/source_event')->toOptionHash(),
            'header' => Mage::helper('unl_adminlog')->__('Event'),
            'index' => 'event_module',
        ));


        $this->addColumn('action', array(
            'type' => 'options',
            'options' => Mage::getSingleton('unl_adminlog/source_action')->toOptionHash(),
            'header' => Mage::helper('unl_adminlog')->__('Action'),
            'index' => 'action',
        ));

        $this->addColumn('result', array(
            'type' => 'options',
            'options' => Mage::getSingleton('unl_adminlog/source_result')->toOptionHash(),
            'header' => Mage::helper('unl_adminlog')->__('Result'),
            'index' => 'result',
        ));

        $this->addColumn('action_path', array(
            'header' => Mage::helper('unl_adminlog')->__('Action Path'),
            'index' => 'action_path',
        ));

        $this->addColumn('action_info', array(
            'renderer' => 'unl_adminlog/log_renderer_actioninfo',
            'align' => 'center',
            'filter' => false,
            'header' => Mage::helper('unl_adminlog')->__('Info'),
            'index' => 'action_info',
        ));

        $this->addExportType($this->_getCsvUrl(), Mage::helper('sales')->__('CSV'));
        $this->addExportType($this->_getExcelUrl(), Mage::helper('sales')->__('Excel XML'));

        return parent::_prepareColumns();
    }

    protected function _getCsvUrl()
    {
        return '*/*/exportCsv';
    }

    protected function _getExcelUrl()
    {
        return '*/*/exportExcel';
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=>true));
    }
}

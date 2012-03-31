<?php

class Unl_AdminLog_LogController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        if (!Mage::getSingleton('unl_adminlog/config')->isLogEnabled()) {
            $this->_getSession()->addNotice($this->__('Admin logging is currently disabled. <a href="%s">Update Settings</a>', $this->getUrl('adminhtml/system_config/edit', array('section' => 'system'))));
        }

        $this->_title($this->__('System'))->_title($this->__('Admin Action Log'));

        $this->loadLayout()
            ->_setActiveMenu('system/adminlog/')
            ->_addBreadcrumb(Mage::helper('unl_adminlog')->__('System'), Mage::helper('unl_adminlog')->__('System'));
        return $this;
    }

    public function indexAction()
    {
        if ($this->getRequest()->getQuery('ajax')) {
            $this->_forward('grid');
            return;
        }

        $this->_initAction()
            ->_addBreadcrumb(Mage::helper('unl_adminlog')->__('Admin Action Log'), Mage::helper('unl_adminlog')->__('Admin Action Log'))
            ->renderLayout();
    }

    public function gridAction()
    {
        $this->loadLayout();
        $grid = $this->getLayout()->createBlock('unl_adminlog/log_grid')->toHtml();
        $this->getResponse()->setBody($grid);
    }

    public function exportCsvAction()
    {
        $fileName   = 'adminlog.csv';
        $grid       = $this->getLayout()->createBlock('unl_adminlog/log_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    public function exportExcelAction()
    {
        $fileName   = 'adminlog.xml';
        $grid       = $this->getLayout()->createBlock('unl_adminlog/log_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile());
    }

    public function archiveAction()
    {
        $this->_initAction()
            ->_title($this->__('Archive'))
            ->_addBreadcrumb(Mage::helper('unl_adminlog')->__('Admin Action Log'), Mage::helper('unl_adminlog')->__('Admin Action Log'))
            ->_addBreadCrumb(Mage::helper('unl_adminlog')->__('Archive'), Mage::helper('unl_adminlog')->__('Archive'))
            ->renderLayout();
    }

    public function archiveGridAction()
    {
        $this->loadLayout();
        $grid = $this->getLayout()->createBlock('unl_adminlog/archive_grid')
            ->toHtml();
        $this->getResponse()->setBody($grid);
    }

    public function exportArchiveCsvAction()
    {
        $fileName   = 'adminlog_archive.csv';
        $grid       = $this->getLayout()->createBlock('unl_adminlog/archive_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getCsvFile());
    }

    public function exportArchiveExcelAction()
    {
        $fileName   = 'adminlog_archive.xml';
        $grid       = $this->getLayout()->createBlock('unl_adminlog/archive_grid');
        $this->_prepareDownloadResponse($fileName, $grid->getExcelFile());
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/adminlog');
    }
}

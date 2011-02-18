<?php

class Unl_Core_WarehouseController extends Mage_Adminhtml_Controller_Action
{
    protected function _initAction()
    {
        $this->loadLayout()
            ->_setActiveMenu('system/shippingroot/warehouse')
            ->_addBreadcrumb(Mage::helper('shipping')->__('System'), Mage::helper('shipping')->__('System'));
        return $this;
    }

    public function indexAction()
    {
        $this->_title($this->__('System'))->_title($this->__('Manage Warehouses'));

        if ($this->getRequest()->getQuery('ajax')) {
            $this->_forward('grid');
            return;
        }

        $this->_initAction()
            ->_addBreadcrumb(Mage::helper('shipping')->__('Manage Warehouses'), Mage::helper('shipping')->__('Manage Warehouses'))
            ->renderLayout();
    }

    public function gridAction()
    {
        $this->loadLayout();
        $grid = $this->getLayout()->createBlock('unl_core/warehouse_grid')
            ->toHtml();
        $this->getResponse()->setBody($grid);
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $this->_title($this->__('System'))->_title($this->__('Manage Warehouses'));

        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('unl_core/warehouse');

        if ($id) {
            $model->load($id);
            if (! $model->getId()) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('shipping')->__('This warehouse no longer exists.'));
                $this->_redirect('*/*');
                return;
            }
        }

        $name = $model->getName();
        if (empty($name)) {
            $name = $this->__('[Untitled]');
        }
        $this->_title($model->getId() ? $name : $this->__('New Warehouse'));

        $data = $this->_getSession()->getPageData(true);
        if (!empty($data)) {
            $model->addData($data);
        }

        Mage::register('current_warehouse', $model);

        $this->_initAction()->getLayout()->getBlock('warehouse_edit')
             ->setData('form_action_url', $this->getUrl('*/*/save'));

        $this
            ->_addBreadcrumb($id ? Mage::helper('shipping')->__('Edit Warehouse') : Mage::helper('shipping')->__('New Warehouse'), $id ? Mage::helper('shipping')->__('Edit Warehouse') : Mage::helper('catalogrule')->__('New Warehouse'))
            ->renderLayout();

    }

    public function saveAction()
    {
        if ($this->getRequest()->getPost()) {
            try {
                $model = Mage::getModel('unl_core/warehouse');
                $data = $this->getRequest()->getPost();
                if ($id = $this->getRequest()->getParam('id')) {
                    $model->load($id);
                    if ($id != $model->getId()) {
                        Mage::throwException(Mage::helper('shipping')->__('Wrong warehouse specified.'));
                    }
                }

                $model->addData($data);

                $this->_getSession()->setPageData($model->getData());

                $model->save();

                $this->_getSession()->addSuccess(Mage::helper('shipping')->__('The warehouse has been saved.'));
                $this->_getSession()->unsPageData();
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', array('id' => $model->getId()));
                    return;
                }
                $this->_redirect('*/*/');
                return;
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError(Mage::helper('shipping')->__('An error occurred while saving the warehouse data. Please review the log and try again.'));
                Mage::logException($e);
                $this->_getSession()->setPageData($data);
                $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                return;
            }
        }
        $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        $model = Mage::getModel('unl_core/warehouse')
            ->load($this->getRequest()->getParam('id'));
        if ($model->getId()) {
            try {
                $model->delete();
            }
            catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
            catch (Exception $e) {
                $this->_getSession()->addException($e, Mage::helper('adminhtml')->__('An error occurred while deleting this warehouse.'));
            }
        }
        $this->_redirect('*/*');
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sytem/shippingroot/warehouse');
    }
}
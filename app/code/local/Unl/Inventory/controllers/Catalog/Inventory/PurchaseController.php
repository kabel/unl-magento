<?php

class Unl_Inventory_Catalog_Inventory_PurchaseController extends Mage_Adminhtml_Controller_Action
{
    protected function _initPurchase()
    {
        $this->_title($this->__('Catalog'))
             ->_title($this->__('Manage Inventory'));

        $purchaseId = (int) $this->getRequest()->getParam('id');
        if (!$purchaseId) {
            return null;
        }

        $purchase = Mage::getModel('unl_inventory/purchase')->load($purchaseId);
        if (!$purchase->getId()) {
            return null;
        }

        $product = $purchase->getProduct();

        $flags = new Varien_Object(array('denied' => false));
        Mage::dispatchEvent('unl_inventory_controller_product_init', array('product' => $product, 'flags' => $flags));
        if ($flags->getDenied()) {
            $this->_forward('denied');
            return -1;
        }

        Mage::register('product', $product);
        Mage::register('current_product', $product);
        Mage::register('current_purchase', $purchase);
        return $purchase;
    }

    public function editAction()
    {
        if (!$purchase = $this->_initPurchase()) {
            return $this->_redirect('*/catalog_inventory/');
        }
        if (is_int($purchase)) {
            return;
        }

        $this->loadLayout();
        $this->_title($purchase->getProduct()->getName())
            ->_title($this->__('Purchase Details'));
        $this->_setActiveMenu('catalog/inventory');
        $this->renderLayout();
    }

    public function saveAction()
    {
        if (!$purchase = $this->_initPurchase()) {
            return $this->_redirect('*/catalog_inventory/');
        }
        if (is_int($purchase)) {
            return;
        }

        $data = $this->getRequest()->getPost();
        if (empty($data) || $data['id'] != $purchase->getId()) {
            return $this->_redirect('*/catalog_inventory/');
        }

        try {
            //TODO: Implement purchase amount validation and change
            Mage::throwException('Not yet implemented');

            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('unl_inventory')->__('The inventory update has been logged.'));

            if ($redirectBack) {
                return $this->_redirect('*/*/edit', array(
                    'id'    => $product->getId(),
                    '_current'=>true
                ));
            }
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            return $this->_redirect('*/*/edit', array('id'=>$purchase->getId(), '_current'=>true));
        }

        $this->_redirect('*/*/');
    }

    public function auditGridAction()
    {
        $this->_initPurchase();
        $this->loadLayout()
            ->renderLayout();
    }

    protected function _isAllowed()
    {
        switch ($this->getRequest()->getActionName()) {
            case 'save':
                return Mage::getSingleton('admin/session')->isAllowed('catalog/inventory/edit');
                break;
            default:
                return Mage::getSingleton('admin/session')->isAllowed('catalog/inventory');
                break;
        }
    }
}

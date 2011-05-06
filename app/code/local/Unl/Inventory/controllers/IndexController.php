<?php

class Unl_Inventory_IndexController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_title($this->__('Catalog'))
             ->_title($this->__('Manage Inventory'));

        $this->loadLayout();
        $this->_setActiveMenu('catalog/inventory');
        $this->renderLayout();
    }

    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('unl_inventory/products_grid')->toHtml()
        );
    }

    protected function _initProduct()
    {
        $this->_title($this->__('Catalog'))
             ->_title($this->__('Manage Inventory'));

        $productId  = (int) $this->getRequest()->getParam('id');
        if (!$productId) {
            return null;
        }
        $product = Mage::getModel('catalog/product')->load($productId);

        $flags = new Varien_Object(array('denied' => false));
        Mage::dispatchEvent('unl_inventory_controller_product_init', array('product' => $product, 'flags' => $flags));
        if ($flags->getDenied()) {
            $this->_forward('denied');
            return -1;
        }

        Mage::register('product', $product);
        Mage::register('current_product', $product);
        return $product;
    }

    public function editAction()
    {
        if (!$product = $this->_initProduct()) {
            return $this->_redirect('*/*/');
        }
        if (is_int($product)) {
            return;
        }

        // set entered data if was error when we do save
        $data = Mage::getSingleton('adminhtml/session')->getInventoryAdjustmentData(true);
        $product->setAdjustmentFormData($data);

        $this->loadLayout();
        $this->_title($product->getName());
        $this->_setActiveMenu('catalog/inventory');
        $this->renderLayout();
    }

    public function saveAction()
    {
        if (!$product = $this->_initProduct()) {
            return $this->_redirect('*/*/');
        }
        if (is_int($product)) {
            return;
        }

        $data = $this->getRequest()->getPost();
        if (empty($data) || $data['id'] != $product->getId()) {
            return $this->_redirect('*/*/');
        }

        $redirectBack   = $this->getRequest()->getParam('back', false);

        try {
            if (!Mage::helper('unl_inventory')->getIsAuditInventory($product)) {
                Mage::throwException($this->__("This product's inventory is currently not being audited. Save failed."));
            }

            $auditLog = Mage::getModel('unl_inventory/audit');
            $auditLog->setData($data['adjust']);
            $auditLog->setProduct($product);
            $auditLog->setProductId($product->getId());

            $result = $auditLog->validate();
            if (is_string($result)) {
                Mage::throwException($result);
            }

            $auditLog->setRegisterFlag(true)
                ->setCreatedAt(now())
                ->save();

            Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('unl_inventory')->__('The inventory update has been logged.'));

            if ($redirectBack) {
                return $this->_redirect('*/*/edit', array(
                    'id'    => $product->getId(),
                    '_current'=>true
                ));
            }
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            Mage::getSingleton('adminhtml/session')->setInventoryAdjustmentData(isset($data['adjust']) ? $data['adjust'] : array());
            return $this->_redirect('*/*/edit', array('id'=>$product->getId(), '_current'=>true));
        }

        $this->_redirect('*/*/');
    }

    public function auditGridAction()
    {
        $this->_initProduct();
        $this->getResponse()->setBody($this->getLayout()->createBlock('unl_inventory/inventory_edit_tab_audit')->toHtml());
    }

    public function exportAuditCsvAction()
    {
        if (!$product = $this->_initProduct()) {
            $this->_forward('noroute');
            return;
        }
        if (is_int($product)) {
            return;
        }

        $fileName   = 'inventory_audit.csv';
        $content    = $this->getLayout()->createBlock('unl_inventory/inventory_edit_tab_audit')
            ->getCsvFile();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function exportAuditExcelAction()
    {
        if (!$product = $this->_initProduct()) {
            $this->_forward('noroute');
            return;
        }
        if (is_int($product)) {
            return;
        }

        $fileName   = 'valuation.xml';
        $content    = $this->getLayout()->createBlock('unl_inventory/inventory_edit_tab_audit')
            ->getExcelFile($fileName);

        $this->_prepareDownloadResponse($fileName, $content);
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('calalog/inventory');
    }
}

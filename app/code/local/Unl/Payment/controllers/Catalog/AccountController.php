<?php

class Unl_Payment_Catalog_AccountController extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {
        $this->_title($this->__('Catalog'))->_title($this->__('Payment Accounts'));

        $this->loadLayout();
        $this->_setActiveMenu('catalog/accounts');
        $this->_addBreadcrumb(Mage::helper('unl_payment')->__('Catalog'), Mage::helper('unl_payment')->__('Catalog'));
        $this->_addBreadcrumb(Mage::helper('unl_payment')->__('Payment Accounts'), Mage::helper('unl_payment')->__('Payment Accounts'));
        $this->renderLayout();
    }

    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('unl_payment/account_grid')->toHtml()
        );
    }

    protected function _initAccount()
    {
        $this->_title($this->__('Catalog'))->_title($this->__('Payment Accounts'));

        $model = Mage::getModel('unl_payment/account');
        Mage::register('current_account', $model);
        $id = $this->getRequest()->getParam('id');
        if (!is_null($id)) {
            $model->load($id);

            if (!$model->getId()) {
                return false;
            }

            $scope = Mage::helper('unl_core')->getAdminUserScope(true);
            if (!empty($scope) && !in_array($model->getGroupId(), $scope)) {
                return -1;
            }
        }

        return $model;
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $currentAccount = $this->_initAccount();

        if (!$currentAccount) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('unl_payment')->__('Wrong account was specified.'));
            return $this->_redirect('*/*/');
        } else if ($currentAccount === -1) {
            return $this->_forward('denied');
        }

        $this->loadLayout();
        $this->_setActiveMenu('catalog/accont');
        $this->_addBreadcrumb(Mage::helper('unl_payment')->__('Catalog'), Mage::helper('unl_payment')->__('Catalog'));
        $this->_addBreadcrumb(Mage::helper('unl_payment')->__('Payment Accounts'), Mage::helper('unl_payment')->__('Payment Accounts'));

        // set entered data if was error when we do save
        $data = Mage::getSingleton('adminhtml/session')->getPaymentAccountData(true);
        if (!empty($data)) {
            $currentAccount->addData($data);
        }

        if (!is_null($currentAccount->getId())) {
            $this->_addBreadcrumb(Mage::helper('unl_payment')->__('Edit Account'), Mage::helper('unl_payment')->__('Edit Payment Account'));
        } else {
            $this->_addBreadcrumb(Mage::helper('unl_payment')->__('New Account'), Mage::helper('unl_payment')->__('New Payment Account'));
        }

        $this->_title($currentAccount->getId() ? $currentAccount->getName() : $this->__('New Account'));

        $this->renderLayout();
    }

    public function assignedAction()
    {
        $this->_initAccount();
        $this->loadLayout();
        $this->renderLayout();
    }

    public function assignedGridOnlyAction()
    {
        $this->_initAccount();
        $this->loadLayout();
        $this->renderLayout();
    }

    public function saveAction()
    {
        if ($postData = $this->getRequest()->getPost()) {
            $model = $this->_initAccount();
            if ($model === -1) {
                return $this->_forward('denied');
            }

            if (!$model) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Wrong account was specified.'));
                return $this->_redirect('*/*/');
            }

            $data = array();
            $scope = Mage::helper('unl_core')->getAdminUserScope(true);

            if ($scope && isset($postData['group_id']) && !in_array($postData['group_id'], $scope)) {
                $this->_getSession()->addError(Mage::helper('adminhtml')->__('You do not have permission to add an account for the selected merchant.'));
                $this->_getSession()->setPaymentAccountData($data);
                return $this->_redirect('*/*/edit', array('id' => $model->getId()));
            } else if (empty($postData['group_id'])) {
                $this->_getSession()->addError(Mage::helper('adminhtml')->__('Missing merchant data'));
                $this->_getSession()->setPaymentAccountData($data);
				return $this->_redirect('*/*/edit', array('id' => $model->getId()));
			}

			$data['group_id'] = $postData['group_id'];

            $data['name'] = trim($postData['name']);

            $model->addData($data);

            try {
                $model->save();

                if (isset($postData['account_assigned_products'])) {
                    $productIds = Mage::helper('adminhtml/js')->decodeGridSerializedInput(
                        $postData['account_assigned_products']
                    );
                    $model->addRelations($productIds);
                }

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('The account has been saved.'));
                Mage::getSingleton('adminhtml/session')->setPaymentAccountData(false);
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setPaymentAccountData($data);

                return $this->_redirect('*/*/edit', array('id' => $model->getId()));
            }
        }

        return $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        $model   = $this->_initAccount();
        $session = Mage::getSingleton('adminhtml/session');

        if ($model === -1) {
            return $this->_forward('denied');
        }

        if ($model && $model->getId()) {
            try {
                $model->delete();
                $session->addSuccess(Mage::helper('unl_payment')->__('The account has been deleted.'));
            } catch (Exception $e) {
                $session->addError($e->getMessage());
            }
        } else {
            $session->addError(Mage::helper('unl_payment')->__('Unable to find an account to delete.'));
        }

        $this->_redirect('*/*/');
    }

    public function unassignedAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function unassignedGridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/accounts');
    }
}

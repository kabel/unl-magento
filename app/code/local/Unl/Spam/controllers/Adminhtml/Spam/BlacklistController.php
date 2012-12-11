<?php

class Unl_Spam_Adminhtml_Spam_BlacklistController extends Mage_Adminhtml_Controller_Action
{
    /**
     * @return boolean|Unl_Spam_Model_Blacklist
     */
    protected function _initBlacklist()
    {
        $this->_title($this->__('System'))->_title($this->__('Tools'))->_title($this->__('SPAM Blacklist'));

        $model = Mage::getModel('unl_spam/blacklist');
        Mage::register('current_blacklist', $model);
        $id = $this->getRequest()->getParam('id');
        if (!is_null($id)) {
            $model->load($id);

            if (!$model->getId()) {
                return false;
            }
        }

        return $model;
    }

    public function indexAction()
    {
        $this->_title($this->__('System'))->_title($this->__('Tools'))->_title($this->__('SPAM Blacklist'));

        $this->loadLayout();

        $this->_setActiveMenu('system/tools/spam_blacklist');

        $this->renderLayout();
    }

    public function gridAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }

    public function massDeleteAction()
    {
        $ids = $this->getRequest()->getParam('blacklist');
        if (!is_array($ids)) {
            $this->_getSession()->addError($this->__('Please select blacklisting(s).'));
        } else {
            if (!empty($ids)) {
                try {
                    foreach ($ids as $id) {
                        $model = Mage::getSingleton('unl_spam/blacklist')->load($id);
                        $model->delete();
                    }
                    $this->_getSession()->addSuccess(
                        $this->__('Total of %d record(s) have been deleted.', count($ids))
                    );
                } catch (Exception $e) {
                    $this->_getSession()->addError($e->getMessage());
                }
            }
        }
        $this->_redirect('*/*/');
    }

    public function massRespUpdateAction()
    {
        $ids = $this->getRequest()->getParam('blacklist');
        $response = $this->getRequest()->getParam('response');

        $responseOptions = Mage::getSingleton('unl_spam/source_responsetype')->toOptionHash();

        if (!isset($responseOptions[$response])) {
            $this->_getSession()->addError($this->__('Invalid reponse selected'));
            $this->_redirect('*/*/');
            return;
        }

        if (!is_array($ids)) {
            $this->_getSession()->addError($this->__('Please select blacklisting(s).'));
        } else {
            if (!empty($ids)) {
                try {
                    foreach ($ids as $id) {
                        $model = Mage::getModel('unl_spam/blacklist')->load($id);
                        $model->setResponseType($response)
                            ->save();
                    }
                    $this->_getSession()->addSuccess(
                        $this->__('Total of %d record(s) have been updated.', count($ids))
                    );
                } catch (Exception $e) {
                    $this->_getSession()->addError($e->getMessage());
                }
            }
        }
        $this->_redirect('*/*/');
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $model = $this->_initBlacklist();

        if (!$model) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('unl_spam')->__('Cannot find that blacklisting.'));
            return $this->_redirect('*/*/');
        }

        $model->getCidrBitCount();

        $this->loadLayout();
        $this->_setActiveMenu('system/tools/spam_blacklist');

        // set entered data if was error when we do save
        $data = Mage::getSingleton('adminhtml/session')->getBlacklistData(true);
        if (!empty($data)) {
            $model->addData($data);
        }

        $this->_title($model->getId() ? $model->getRemoteAddr() : $this->__('New Blacklisting'));

        $this->renderLayout();
    }

    public function saveAction()
    {
        if ($postData = $this->getRequest()->getPost()) {
            if (!$model = $this->_initBlacklist()) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Cannot find that blacklisting.'));
                return $this->_redirect('*/*/');
            }

            $accept = array(
                'remote_addr'   => false,
                'response_type' => Unl_Spam_Model_Blacklist::RESPONSE_TYPE_403,
                'comment'       => '',
                'cidr_bits'     => '',
            );

            $data = array_merge($accept, array_intersect_key($postData, $accept));

            try {
                $binAddr = @inet_pton($data['remote_addr']);
                if ($binAddr === false) {
                    throw new Exception(Mage::helper('adminhtml')->__('Invalid IP Address'));
                }

                $collection = Mage::getResourceModel('unl_spam/blacklist_collection')
                    ->addFieldToFilter('remote_addr', $binAddr);
                if ($model->getId()) {
                    $collection->addFieldToFilter($model->getIdFieldName(), array('neq' => $model->getId()));
                }
                if ($collection->getSize()) {
                    throw new Exception(Mage::helper('adminhtml')->__('That IP Address is already blacklisted'));
                }

                if (!empty($data['cidr_bits'])) {
                    if (!intval($data['cidr_bits'])) {
                        throw new Exception(Mage::helper('adminhtml')->__('Invalid CIDR mask value. Must be an integer.'));
                    }

                    $maxCidr = strlen($binAddr) * 8;
                    $cidr = min($maxCidr - 1, intval($data['cidr_bits']));
                    $data['cidr_mask'] = Mage::helper('unl_spam')->getCidrMask($cidr, $maxCidr);
                    $maskedAddr = $binAddr & $data['cidr_mask'];

                    if ($maskedAddr != $binAddr) {
                        $data['remote_addr'] = inet_ntop($maskedAddr);
                    }
                } else {
                    $data['cidr_bits'] = null;
                }

                $model->addData($data);
                $model->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('The blacklisting has been saved.'));
                Mage::getSingleton('adminhtml/session')->setBlacklistData(false);
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setBlacklistData($data);

                return $this->_redirect('*/*/edit', array('id' => $model->getId()));
            }
        }

        return $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        $model   = $this->_initBlacklist();
        $session = Mage::getSingleton('adminhtml/session');

        if ($model && $model->getId()) {
            try {
                $model->delete();
                $session->addSuccess(Mage::helper('adminhtml')->__('The blacklisting has been deleted.'));
            } catch (Exception $e) {
                $session->addError($e->getMessage());
            }
        } else {
            $session->addError(Mage::helper('adminhtml')->__('Unable to find a blacklisting to delete.'));
        }

        $this->_redirect('*/*/');
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('system/tools/spam_blacklist');
    }
}

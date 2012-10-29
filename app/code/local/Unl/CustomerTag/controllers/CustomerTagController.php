<?php

class Unl_CustomerTag_CustomerTagController extends Mage_Adminhtml_Controller_Action
{
    protected function _initTag()
    {
        $this->_title($this->__('Customers'))->_title($this->__('Customer Tags'));

        $tag = Mage::getModel('unl_customertag/tag');
        Mage::register('current_tag', $tag);
        $tagId = $this->getRequest()->getParam('id');
        if (!is_null($tagId)) {
            $tag->load($tagId);

            if (!$tag->getId()) {
                return false;
            }
        }

        return $tag;
    }

    public function indexAction()
    {
        $this->_title($this->__('Customers'))->_title($this->__('Customer Tags'));

        $this->loadLayout();
        $this->_setActiveMenu('customer/tag');
        $this->_addBreadcrumb(Mage::helper('unl_customertag')->__('Customers'), Mage::helper('unl_customertag')->__('Customers'));
        $this->_addBreadcrumb(Mage::helper('unl_customertag')->__('Customer Tags'), Mage::helper('unl_customertag')->__('Customer Tags'));
        $this->renderLayout();
    }

    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('unl_customertag/tag_grid')->toHtml()
        );
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    public function editAction()
    {
        $currentTag = $this->_initTag();

        if (!$currentTag) {
            Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Wrong tag was specified.'));
            return $this->_redirect('*/*/');
        }

        $this->loadLayout();
        $this->_setActiveMenu('customer/tag');
        $this->_addBreadcrumb(Mage::helper('unl_customertag')->__('Customers'), Mage::helper('unl_customertag')->__('Customers'));
        $this->_addBreadcrumb(Mage::helper('unl_customertag')->__('Customer Tags'), Mage::helper('unl_customertag')->__('Customer Tags'));

        // set entered data if was error when we do save
        $data = Mage::getSingleton('adminhtml/session')->getCustomerTagData(true);
        if (! empty($data)) {
            $currentTag->addData($data);
        }

        if (!is_null($currentTag->getId())) {
            $this->_addBreadcrumb(Mage::helper('unl_customertag')->__('Edit Tag'), Mage::helper('unl_customertag')->__('Edit Customer Tag'));
        } else {
            $this->_addBreadcrumb(Mage::helper('unl_customertag')->__('New Tag'), Mage::helper('unl_customertag')->__('New Customer Tag'));
        }

        $this->_title($currentTag->getId() ? $currentTag->getName() : $this->__('New Tag'));

        $this->renderLayout();
    }

    public function assignedAction()
    {
        $this->_initTag();
        $this->loadLayout();
        $this->renderLayout();
    }

    public function saveAction()
    {
        if ($postData = $this->getRequest()->getPost()) {
            if (!$model = $this->_initTag()) {
                Mage::getSingleton('adminhtml/session')->addError(Mage::helper('adminhtml')->__('Wrong tag was specified.'));
                return $this->_redirect('*/*/');
            }

            if (!$model->getIsSystem()) {
                if (isset($postData['tag_id'])) {
                    $data['tag_id'] = $postData['tag_id'];
                }

                $data['name'] = trim($postData['tag_name']);
                $collection = Mage::getResourceModel('unl_customertag/tag_collection')
                    ->addFieldToFilter('name', $data['name']);
                if ($model->getId()) {
                    $collection->addFieldToFilter('tag_id', array('neq' => $model->getId()));
                }
                if ($collection->getSize()) {
                    $this->_getSession()->addError(Mage::helper('adminhtml')->__('The tag name must be unique.'));
                    $this->_getSession()->setCustomerTagData($data);
                    return $this->_redirect('*/*/edit', array('id' => $model->getId()));
                }


                $model->addData($data);
            }

            try {
                $model->save();

                Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('adminhtml')->__('The tag has been saved.'));
                Mage::getSingleton('adminhtml/session')->setCustomerTagData(false);

                if (($this->getRequest()->getParam('continue'))) {
                    return $this->_redirect('*/*/edit', array('id' => $model->getId()));
                } else {
                    return $this->_redirect('*/*/');
                }
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                Mage::getSingleton('adminhtml/session')->setCustomerTagData($data);

                return $this->_redirect('*/*/edit', array('id' => $model->getId()));
            }
        }

        return $this->_redirect('*/*/');
    }

    public function deleteAction()
    {
        $model   = $this->_initTag();
        $session = Mage::getSingleton('adminhtml/session');

        if ($model && $model->getId()) {
            try {
                if ($model->getIsSystem()) {
                    Mage::throwException(Mage::helper('unl_customertag')->__('The tag cannot be deleted'));
                }
                $model->delete();
                $session->addSuccess(Mage::helper('adminhtml')->__('The tag has been deleted.'));
            } catch (Exception $e) {
                $session->addError($e->getMessage());
            }
        } else {
            $session->addError(Mage::helper('adminhtml')->__('Unable to find a tag to delete.'));
        }

        $this->_redirect('*/*/');
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('customer/tag');
    }
}

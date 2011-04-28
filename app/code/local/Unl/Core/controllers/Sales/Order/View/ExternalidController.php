<?php

class Unl_Core_Sales_Order_View_ExternalidController extends Mage_Adminhtml_Controller_Action
{
    protected function _construct()
    {
        $this->setUsedModuleName('Mage_Sales');
    }

    public function saveAction()
    {
        try {
            $this->_getSaveModel()
                ->setOrderId($this->getRequest()->getParam('order_id'))
                ->setExternalId($this->getRequest()->getParam('external_id'))
                ->save();
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__('Error while saving external ID'));
        }

        $this->getResponse()->setBody(
            $this->_getSaveModel()->getSaved() ? 'YES' : 'NO'
        );
    }

    protected function _getSaveModel()
    {
        return Mage::getSingleton('unl_core/adminhtml_externalid_save');
    }
}

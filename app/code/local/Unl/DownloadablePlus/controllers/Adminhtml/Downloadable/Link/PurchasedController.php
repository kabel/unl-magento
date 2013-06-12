<?php

class Unl_DownloadablePlus_Adminhtml_Downloadable_Link_PurchasedController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Unitialize the order model instance
     *
     * @return false|Mage_Sales_Model_Order
     */
    protected function _initOrder()
    {
        $id = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($id);

        if (!$order->getId()) {
            $this->_getSession()->addError($this->__('This order no longer exists.'));
            $this->_redirect('*/sales_order/');
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return false;
        }
        Mage::register('sales_order', $order);
        Mage::register('current_order', $order);
        return $order;
    }

    /**
     * Initialize the purchased link item model instance
     *
     * @param Mage_Sales_Model_Order $order
     * @return false|Mage_Downloadable_Model_Link_Purchased_Item
     */
    protected function _initLink($order)
    {
        $id = $this->getRequest()->getParam('item_id');
        $link = Mage::getModel('downloadable/link_purchased_item')->load($id);


        if (!$link->getId()) {
            $this->_getSession()->addError($this->__('This downloadable link no longer exists.'));
            $this->_redirect('*/sales_order/view', array('order_id' => $this->getRequest()->getParam('order_id')));
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return false;
        }

        $orderItem = $order->getItemById($link->getOrderItemId());
        $scope = Mage::helper('unl_core')->getAdminUserScope();
        if (!$orderItem || ($scope && !in_array($orderItem->getSourceStoreView(), $scope))) {
            $this->_getSession()->addError($this->__('You are not allowed to access this download link.'));
            $this->_redirect('*/sales_order/view', array('order_id' => $this->getRequest()->getParam('order_id')));
            $this->setFlag('', self::FLAG_NO_DISPATCH, true);
            return false;
        }

        return $link;
    }

    public function resetUseAction()
    {
        if (($order = $this->_initOrder()) && ($link = $this->_initLink($order))) {
            try {
                $link->setNumberOfDownloadsUsed(0);

                if ($link->getStatus() == Mage_Downloadable_Model_Link_Purchased_Item::LINK_STATUS_EXPIRED) {
                    $link->setStatus(Mage_Downloadable_Model_Link_Purchased_Item::LINK_STATUS_AVAILABLE)
                        ->save();
                }

                $order->setDataChanges(true)
                    ->save();
                $this->_getSession()->addSuccess(
                    $this->__('The download link use has been reset.')
                );
            }
            catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
            catch (Exception $e) {
                $this->_getSession()->addError($this->__('Could not reset download link use.'));
            }
            $this->_redirect('*/sales_order/view', array('order_id' => $order->getId()));
        }
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/view');
    }
}

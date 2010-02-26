<?php

class Unl_Core_Model_Mysql4_Bursar_Co_Collection extends Unl_Core_Model_Mysql4_Bursar_Abstract
{
    protected $_paymentMethodCodes = array('purchaseorder');
    
    protected function joinOtherTables()
    {
        parent::joinOtherTables();
        
        /* @var $paymentModel Mage_Sales_Model_Mysql4_Order_Payment */
        $paymentModel = Mage::getResourceSingleton('sales/order_payment');
        $poNumberAttr = $paymentModel->getAttribute('po_number');
        
        $this->getSelect()
            ->joinInner(
                array('payment_po_number' => $poNumberAttr->getBackendTable()),
                'payment.entity_id = payment_po_number.entity_id AND payment_po_number.attribute_id = ' . $poNumberAttr->getId(),
                array('po_number' => 'payment_po_number.value'))
            ->group('payment_po_number.value');
    }
}
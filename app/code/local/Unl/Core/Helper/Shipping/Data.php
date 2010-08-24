<?php

class Unl_Core_Helper_Shipping_Data extends Mage_Shipping_Helper_Data
{
    /**
     * Retrieve tracking url with params
     *
     * @param  string $key
     * @param  integer|Mage_Sales_Model_Order|Mage_Sales_Model_Order_Shipment|Mage_Sales_Model_Order_Shipment_Track $model
     * @param  string $method - option
     * @return string
     */
    protected function _getTrackingUrl($key, $model, $method = 'getId')
    {
         if (empty($model)) {
             $param = array($key => ''); // @deprecated after 1.4.0.0-alpha3
         } else if (!is_object($model)) {
             $param = array($key => $model); // @deprecated after 1.4.0.0-alpha3
         } else {
             $param = array(
                 'hash' => Mage::helper('core')->urlEncode("{$key}:{$model->$method()}:{$model->getProtectCode()}")
             );
         }
         
         // Track model doesn't have store_id
         if ($model instanceof Mage_Sales_Model_Order_Shipment_Track) {
             $storeModel = Mage::app()->getStore($model->getShipment()->getStoreId());
         } else {
             $storeModel = Mage::app()->getStore($model->getStoreId());
         }
         
         return $storeModel->getUrl('shipping/tracking/popup', $param);
    }
}
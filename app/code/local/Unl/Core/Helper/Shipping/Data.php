<?php

class Unl_Core_Helper_Shipping_Data extends Mage_Shipping_Helper_Data
{
    /* Overrides
     * @see Mage_Shipping_Helper_Data::_getTrackingUrl()
     * by using the right story_id/model
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

         $storeId = null;
         // Track model doesn't have store_id
         if ($model instanceof Mage_Sales_Model_Order_Shipment_Track) {
             $storeId = $model->getShipment()->getStoreId();
         } elseif (is_object($model)) {
             $storeId = $model->getStoreId();
         }
         $storeModel = Mage::app()->getStore($storeId);
         return $storeModel->getUrl('shipping/tracking/popup', $param);
    }
}

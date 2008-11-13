<?php

class Unl_Reports_Model_Mysql4_Order_Collection extends Mage_Reports_Model_Mysql4_Order_Collection
{
    public function setStoreIds($storeIds)
    {
        $vals = array_values($storeIds);
        if (count($storeIds) >= 1 && $vals[0] != '') {
            if (Mage::app()->getRequest()->getParam('website')) {
                $this->addAttributeToFilter('store_id', array('in' => (array)$storeIds))
                    ->addExpressionAttributeToSelect(
                        'subtotal',
                        'SUM({{base_subtotal}})',
                        array('base_subtotal'))
                    ->addExpressionAttributeToSelect(
                        'tax',
                        'SUM({{base_tax_amount}})',
                        array('base_tax_amount'))
                    ->addExpressionAttributeToSelect(
                        'shipping',
                        'SUM({{base_shipping_amount}})',
                        array('base_shipping_amount'))
                    ->addExpressionAttributeToSelect(
                        'discount',
                        'SUM({{base_discount_amount}})',
                        array('base_discount_amount'))
                    ->addExpressionAttributeToSelect(
                        'total',
                        'SUM({{base_grand_total}})',
                        array('base_grand_total'))
                    ->addExpressionAttributeToSelect(
                        'invoiced',
                        'SUM({{base_total_paid}})',
                        array('base_total_paid'))
                    ->addExpressionAttributeToSelect(
                        'refunded',
                        'SUM({{base_total_refunded}})',
                        array('base_total_refunded'));
            } else {
                //TODO: Do custom processing for group/store
                $productOrderCollection = Mage::getResourceModel('reports/product_ordered_collection');
                $filter = $this->getSelect()->getPart(Zend_Db_Select::WHERE);
                $filter = substr($filter[1], strpos($filter[1], '(')+1, -1);
                $filter = str_replace('e.', 'order.', $filter);
                
                $productTypes = " AND (e.type_id = '" .
                    Mage_Catalog_Model_Product_Type::TYPE_SIMPLE .
                    "' OR e.type_id = '" . Mage_Catalog_Model_Product_Type::TYPE_VIRTUAL  . "')";
                
                $productOrderCollection->getSelect()->reset()
                    ->from(
                        array('order_items' => $productOrderCollection->getTable('sales/order_item')),
                        array('*'))
                    ->joinInner(
                        array('order' => $productOrderCollection->getTable('sales/order')),
                        'order.entity_id = order_items.order_id',
                        array())
                    ->joinInner(array('e' => $productOrderCollection->getProductEntityTableName()),
                        "e.entity_id = order_items.product_id AND e.entity_type_id = {$productOrderCollection->getProductEntityTypeId()}{$productTypes}")
                    ->where($filter);
                
                $productOrderCollection->load();
                $products = $productOrderCollection->toArray();
                
                $orders    = array();
                $items     = array();
                $subtotal  = array();
                $tax       = array();
                $discounts = array();
                $grandTot  = array();
                $invoiced  = array();
                $refunded  = array();
                
                foreach ($products as $p) {
                    $categories = explode(',', $p['category_ids']);
                    $homeCat = Mage::getModel('catalog/category')->setId($categories[0]);
                    $ownerStores = $homeCat->getStoreIds();
                    foreach ($ownerStores as $s) {
                        if (in_array($s, $storeIds)) {
                            $orders[] = $p['order_id'];
                            $items[] = $p['qty_ordered'];
                            $subtotal[] = $p['base_row_total'];
                            $tax[] = $p['base_tax_amount'];
                            $discounts[] = $p['base_discount_amount'];
                            $grandTot[] = $p['base_row_total'] - $p['base_discount_amount'] + $p['base_tax_amount'];
                            $invoiced[] = $p['base_row_invoiced'];
                            $refunded[] = $p['base_amount_refunded'];
                            break;
                        }
                    }
                }
                
                $this->getSelect()
                    ->from('', array('orders' => new Zend_Db_Expr(count(array_unique($orders)))))
                    ->from('', array('items' => new Zend_Db_Expr(array_sum($items))))
                    ->from('', array('subtotal' => new Zend_Db_Expr(array_sum($subtotal))))
                    ->from('', array('tax' => new Zend_Db_Expr(array_sum($tax))))
                    ->from('', array('discount' => new Zend_Db_Expr(array_sum($discounts))))
                    ->from('', array('total' => new Zend_Db_Expr(array_sum($grandTot))))
                    ->from('', array('invoiced' => new Zend_Db_Expr(array_sum($invoiced))))
                    ->from('', array('refunded' => new Zend_Db_Expr(array_sum($refunded))));
                
            }
        } else {
            $this->addExpressionAttributeToSelect(
                    'subtotal',
                    'SUM({{base_subtotal}}/{{store_to_base_rate}})',
                    array('base_subtotal', 'store_to_base_rate'))
                ->addExpressionAttributeToSelect(
                    'tax',
                    'SUM({{base_tax_amount}}/{{store_to_base_rate}})',
                    array('base_tax_amount', 'store_to_base_rate'))
                ->addExpressionAttributeToSelect(
                    'shipping',
                    'SUM({{base_shipping_amount}}/{{store_to_base_rate}})',
                    array('base_shipping_amount', 'store_to_base_rate'))
                ->addExpressionAttributeToSelect(
                    'discount',
                    'SUM({{base_discount_amount}}/{{store_to_base_rate}})',
                    array('base_discount_amount', 'store_to_base_rate'))
                ->addExpressionAttributeToSelect(
                    'total',
                    'SUM({{base_grand_total}}/{{store_to_base_rate}})',
                    array('base_grand_total', 'store_to_base_rate'))
                ->addExpressionAttributeToSelect(
                    'invoiced',
                    'SUM({{base_total_paid}}/{{store_to_base_rate}})',
                    array('base_total_paid', 'store_to_base_rate'))
                ->addExpressionAttributeToSelect(
                    'refunded',
                    'SUM({{base_total_refunded}}/{{store_to_base_rate}})',
                    array('base_total_refunded', 'store_to_base_rate'));
        }

        return $this;
    }
}
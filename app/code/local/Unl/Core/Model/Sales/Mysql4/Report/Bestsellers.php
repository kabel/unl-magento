<?php

class Unl_Core_Model_Sales_Mysql4_Report_Bestsellers extends Mage_Sales_Model_Mysql4_Report_Bestsellers
{
    /**
     * Aggregate Orders data by order created at
     *
     * @param mixed $from
     * @param mixed $to
     * @return Unl_Core_Model_Sales_Mysql4_Report_Bestsellers
     */
    public function aggregate($from = null, $to = null)
    {
        // convert input dates to UTC to be comparable with DATETIME fields in DB
        $from = $this->_dateToUtc($from);
        $to = $this->_dateToUtc($to);

        $this->_checkDates($from, $to);
        //$this->_getWriteAdapter()->beginTransaction();

        try {
            if ($from !== null || $to !== null) {
                $subSelect = $this->_getTableDateRangeSelect(
                    $this->getTable('sales/order'),
                    'created_at', 'updated_at', $from, $to
                );
            } else {
                $subSelect = null;
            }

            $this->_clearTableByDateRange($this->getMainTable(), $from, $to, $subSelect);

            $columns = array(
                // convert dates from UTC to current admin timezone
                'period'                         => "DATE(CONVERT_TZ(source_table.created_at, '+00:00', '" . $this->_getStoreTimezoneUtcOffset() . "'))",
                'store_id'                       => 'order_item.source_store_view',
                'product_id'                     => 'order_item.product_id',
                'product_name'                   => 'IFNULL(product_name.value, product_default_name.value)',
                'product_price'                  => 'IFNULL(product_price.value, product_default_price.value) * IFNULL(source_table.base_to_global_rate, 0)',
                'qty_ordered'                    => 'SUM(order_item.qty_ordered)',
            );

            $select = $this->_getWriteAdapter()->select();

            $select->from(array('source_table' => $this->getTable('sales/order')), $columns)
                ->joinInner(
                    array('order_item' => $this->getTable('sales/order_item')),
                    'order_item.order_id = source_table.entity_id',
                    array()
                )
                ->where('source_table.state <> ?', Mage_Sales_Model_Order::STATE_CANCELED);


            /** @var Mage_Catalog_Model_Resource_Eav_Mysql4_Product $product */
            $product = Mage::getResourceSingleton('catalog/product');

            $productTypes = array(
                Mage_Catalog_Model_Product_Type::TYPE_GROUPED,
                Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE,
                Mage_Catalog_Model_Product_Type::TYPE_BUNDLE,
            );
            $select->joinInner(
                array('product' => $this->getTable('catalog/product')),
                'product.entity_id = order_item.product_id'
                . ' AND product.entity_type_id = ' . $product->getTypeId()
                . " AND product.type_id NOT IN('" . implode("', '", $productTypes) . "')",
                array()
            );

            // join product attributes Name & Price
            $attr = $product->getAttribute('name');
            $select->joinLeft(array('product_name' => $attr->getBackend()->getTable()),
                'product_name.entity_id = product.entity_id'
                . ' AND product_name.store_id = source_table.store_id'
                . ' AND product_name.entity_type_id = ' . $product->getTypeId()
                . ' AND product_name.attribute_id = ' . $attr->getAttributeId(),
                array())
                ->joinLeft(array('product_default_name' => $attr->getBackend()->getTable()),
                'product_default_name.entity_id = product.entity_id'
                . ' AND product_default_name.store_id = 0'
                . ' AND product_default_name.entity_type_id = ' . $product->getTypeId()
                . ' AND product_default_name.attribute_id = ' . $attr->getAttributeId(),
                array());

            $attr = $product->getAttribute('price');
            $select->joinLeft(array('product_price' => $attr->getBackend()->getTable()),
                'product_price.entity_id = product.entity_id'
                . ' AND product_price.store_id = source_table.store_id'
                . ' AND product_price.entity_type_id = ' . $product->getTypeId()
                . ' AND product_price.attribute_id = ' . $attr->getAttributeId(),
                array())
                ->joinLeft(array('product_default_price' => $attr->getBackend()->getTable()),
                'product_default_price.entity_id = product.entity_id'
                . ' AND product_default_price.store_id = 0'
                . ' AND product_default_price.entity_type_id = ' . $product->getTypeId()
                . ' AND product_default_price.attribute_id = ' . $attr->getAttributeId(),
                array());


            if ($subSelect !== null) {
                $select->where($this->_makeConditionFromDateRangeSelect($subSelect, 'source_table.created_at'));
            }

            $select->group(new Zend_Db_Expr('1,2,3'));

            $select->useStraightJoin();  // important!
            $sql = $select->insertFromSelect($this->getMainTable(), array_keys($columns));
            $this->_getWriteAdapter()->query($sql);

            $columns = array(
                'period'                         => 'period',
                'store_id'                       => new Zend_Db_Expr('0'),
                'product_id'                     => 'product_id',
                'product_name'                   => 'product_name',
                'product_price'                  => 'product_price',
                'qty_ordered'                    => 'SUM(qty_ordered)',
            );

            $select->reset();
            $select->from($this->getMainTable(), $columns)
                ->where('store_id <> 0');

            if ($subSelect !== null) {
                $select->where($this->_makeConditionFromDateRangeSelect($subSelect, 'period'));
            }

            $select->group(array(
                'period',
                'product_id'
            ));

            $sql = $select->insertFromSelect($this->getMainTable(), array_keys($columns));
            $this->_getWriteAdapter()->query($sql);


            // update rating
            $this->_updateRatingPos(self::AGGREGATION_DAILY);
            $this->_updateRatingPos(self::AGGREGATION_MONTHLY);
            $this->_updateRatingPos(self::AGGREGATION_YEARLY);


            $this->_setFlagData(Mage_Reports_Model_Flag::REPORT_BESTSELLERS_FLAG_CODE);
        } catch (Exception $e) {
            //$this->_getWriteAdapter()->rollBack();
            throw $e;
        }

        //$this->_getWriteAdapter()->commit();
        return $this;
    }
}
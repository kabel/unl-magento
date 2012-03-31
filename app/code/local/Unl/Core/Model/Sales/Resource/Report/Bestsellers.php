<?php

class Unl_Core_Model_Sales_Resource_Report_Bestsellers extends Mage_Sales_Model_Resource_Report_Bestsellers
{
    /* Overrides
     * @see Mage_Sales_Model_Resource_Report_Bestsellers::aggregate()
     * by using source_store
     */
    public function aggregate($from = null, $to = null)
    {
        // convert input dates to UTC to be comparable with DATETIME fields in DB
        $from    = $this->_dateToUtc($from);
        $to      = $this->_dateToUtc($to);

        $this->_checkDates($from, $to);
        $adapter = $this->_getWriteAdapter();
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
            // convert dates from UTC to current admin timezone
            $periodExpr = $adapter->getDatePartSql(
                $this->getStoreTZOffsetQuery(
                    array('source_table' => $this->getTable('sales/order')),
                    'source_table.created_at', $from, $to
                )
            );

            $helper                        = Mage::getResourceHelper('core');
            $select = $adapter->select();

            $select->group(array(
                $periodExpr,
                'order_item.source_store_view',
                'order_item.product_id'
            ));

            $columns = array(
                'period'                 => $periodExpr,
                'store_id'               => 'order_item.source_store_view',
                'product_id'             => 'order_item.product_id',
                'product_name'           => new Zend_Db_Expr(
                    sprintf('MIN(%s)',
                        $adapter->getIfNullSql('product_name.value','product_default_name.value')
                    )
                ),
                'product_price'          => new Zend_Db_Expr(
                        sprintf('%s * %s',
                            $helper->prepareColumn(
                                sprintf('MIN(%s)',
                                    $adapter->getIfNullSql(
                                        $adapter->getIfNullSql('product_price.value','product_default_price.value'),0)
                                ),
                                $select->getPart(Zend_Db_Select::GROUP)
                            ),
                            $helper->prepareColumn(
                                sprintf('MIN(%s)',
                                    $adapter->getIfNullSql('source_table.base_to_global_rate', '0')
                                ),
                                $select->getPart(Zend_Db_Select::GROUP)
                        )
                    )
                ),
                'qty_ordered'            => new Zend_Db_Expr('SUM(order_item.qty_ordered)')
            );

            $select
                ->from(
                    array(
                        'source_table' => $this->getTable('sales/order')),
                    $columns)
                ->joinInner(
                    array(
                        'order_item' => $this->getTable('sales/order_item')),
                    'order_item.order_id = source_table.entity_id',
                    array()
                )
                ->where('source_table.state != ?', Mage_Sales_Model_Order::STATE_CANCELED);


            /** @var Mage_Catalog_Model_Resource_Product $product */
            $product  = Mage::getResourceSingleton('catalog/product');

            $productTypes = array(
                Mage_Catalog_Model_Product_Type::TYPE_GROUPED,
                Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE,
                Mage_Catalog_Model_Product_Type::TYPE_BUNDLE,
            );

            $joinExpr = array(
                'product.entity_id = order_item.product_id',
                $adapter->quoteInto('product.entity_type_id = ?', $product->getTypeId()),
                $adapter->quoteInto('product.type_id NOT IN(?)', $productTypes)
            );

            $joinExpr = implode(' AND ', $joinExpr);
            $select->joinInner(
                array(
                    'product' => $this->getTable('catalog/product')),
                $joinExpr,
                array()
            );

            // join product attributes Name & Price
            $attr     = $product->getAttribute('name');
            $joinExprProductName       = array(
                'product_name.entity_id = product.entity_id',
                'product_name.store_id = source_table.store_id',
                $adapter->quoteInto('product_name.entity_type_id = ?', $product->getTypeId()),
                $adapter->quoteInto('product_name.attribute_id = ?', $attr->getAttributeId())
            );
            $joinExprProductName        = implode(' AND ', $joinExprProductName);
            $joinExprProductDefaultName = array(
                'product_default_name.entity_id = product.entity_id',
                'product_default_name.store_id = 0',
                $adapter->quoteInto('product_default_name.entity_type_id = ?', $product->getTypeId()),
                $adapter->quoteInto('product_default_name.attribute_id = ?', $attr->getAttributeId())
            );
            $joinExprProductDefaultName = implode(' AND ', $joinExprProductDefaultName);
            $select->joinLeft(
                array(
                    'product_name' => $attr->getBackend()->getTable()),
                $joinExprProductName,
                array()
            )
            ->joinLeft(
                array(
                    'product_default_name' => $attr->getBackend()->getTable()),
                $joinExprProductDefaultName,
                array()
            );
            $attr                    = $product->getAttribute('price');
            $joinExprProductPrice    = array(
                'product_price.entity_id = product.entity_id',
                'product_price.store_id = source_table.store_id',
                $adapter->quoteInto('product_price.entity_type_id = ?', $product->getTypeId()),
                $adapter->quoteInto('product_price.attribute_id = ?', $attr->getAttributeId())
            );
            $joinExprProductPrice    = implode(' AND ', $joinExprProductPrice);

            $joinExprProductDefPrice = array(
                'product_default_price.entity_id = product.entity_id',
                'product_default_price.store_id = 0',
                $adapter->quoteInto('product_default_price.entity_type_id = ?', $product->getTypeId()),
                $adapter->quoteInto('product_default_price.attribute_id = ?', $attr->getAttributeId())
            );
            $joinExprProductDefPrice = implode(' AND ', $joinExprProductDefPrice);
            $select->joinLeft(
                array('product_price' => $attr->getBackend()->getTable()),
                $joinExprProductPrice,
                array()
            )
            ->joinLeft(
                array('product_default_price' => $attr->getBackend()->getTable()),
                $joinExprProductDefPrice,
                array()
            );

            if ($subSelect !== null) {
                $select->having($this->_makeConditionFromDateRangeSelect($subSelect, 'period'));
            }


            $select->useStraightJoin();  // important!
            $insertQuery = $helper->getInsertFromSelectUsingAnalytic($select, $this->getMainTable(),
                array_keys($columns));
            $adapter->query($insertQuery);


            $columns = array(
                'period'                         => 'period',
                'store_id'                       => new Zend_Db_Expr(Mage_Core_Model_App::ADMIN_STORE_ID),
                'product_id'                     => 'product_id',
                'product_name'                   => new Zend_Db_expr('MIN(product_name)'),
                'product_price'                  => new Zend_Db_expr('MIN(product_price)'),
                'qty_ordered'                    => new Zend_Db_expr('SUM(qty_ordered)'),
            );

            $select->reset();
            $select->from($this->getMainTable(), $columns)
                ->where('store_id <> ?', 0);

            if ($subSelect !== null) {
                $select->where($this->_makeConditionFromDateRangeSelect($subSelect, 'period'));
            }

            $select->group(array(
                'period',
                'product_id'
            ));

            $insertQuery = $helper->getInsertFromSelectUsingAnalytic($select, $this->getMainTable(),
                array_keys($columns));
            $adapter->query($insertQuery);

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

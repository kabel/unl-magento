<?php

class Unl_Inventory_Model_Resource_Products_Collection extends Mage_Catalog_Model_Resource_Product_Collection
{
    protected function _initSelect()
    {
        parent::_initSelect();

        $this
            ->addAttributeToSelect('sku')
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('price')
            ->addAttributeToSelect('audit_inventory');

        Mage::getModel('cataloginventory/stock_item')->addCatalogInventoryToProductCollection($this);

        $adapter = $this->getConnection();

        $this->addExpressionAttributeToSelect('audit_active',
            $adapter->getCheckSql($this->getAuditExpression(), '1', '2'), array('audit_inventory'));

        return $this;
    }

    public function getAuditExpression()
    {
        // borrow from stock_item
        $adapter = $this->getConnection();
        $isManageStock = (int)Mage::getStoreConfig(Mage_CatalogInventory_Model_Stock_Item::XML_PATH_MANAGE_STOCK);
        $auditExpr = $adapter->getCheckSql('cisi.use_config_manage_stock = 1', $isManageStock, 'cisi.manage_stock');
        $auditExpr = $adapter->getCheckSql("({$auditExpr} = 1)", '{{audit_inventory}}', '0');

        return $auditExpr;
    }

    public function joinValuation()
    {
        $indexSelect = Mage::getModel('unl_inventory/index')->getCollection()->selectValuation()->getSelect();
        $this->getSelect()->joinLeft(
            array('iv' => $indexSelect),
            'iv.product_id = e.entity_id',
            array('qty', 'value', 'avg_cost')
        );

        return $this;
    }

    public function joinAuditAndIndex()
    {
        $auditSelect = Mage::getModel('unl_inventory/audit')->getCollection()->selectProducts()->getSelect();
        $indexSelect = Mage::getModel('unl_inventory/index')->getCollection()->selectQtyOnHand()->getSelect();

        $adapter   = $this->getConnection();
        $auditExpr = $this->getAuditExpression();

        $this->getSelect()
            ->joinLeft(array('ia' => $auditSelect), 'ia.product_id=e.entity_id', array())
            ->joinLeft(array('ii' => $indexSelect), 'ii.product_id=e.entity_id', array());

        $this->addExpressionAttributeToSelect('qty_on_hand',
            $adapter->getCheckSql($auditExpr, $adapter->getIfNullSql('ii.qty', '0'), 'cisi.qty'), array('audit_inventory'));

        $this->getSelect()->where('(ia.product_id IS NOT NULL OR ' .
            str_replace('{{audit_inventory}}', $this->_getAttributeFieldName('audit_inventory'), $auditExpr) . ')');

        return $this;
    }
}

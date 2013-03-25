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
        $valuationSelect = Mage::getResourceModel('unl_inventory/purchase')->getValuationSelect();
        $this->getSelect()->joinLeft(
            array('iv' => $valuationSelect),
            'iv.product_id = e.entity_id',
            array('qty', 'value', 'avg_cost')
        );

        return $this;
    }

    public function joinAuditAndStock()
    {
        $auditSelect = Mage::getResourceModel('unl_inventory/audit')->getProductsSelect();
        $purchaseSelect = Mage::getResourceModel('unl_inventory/purchase')->getOnHandSelect();
        $backorderSelect = Mage::getResourceModel('unl_inventory/backorder')->getBackorderedSelect();

        $adapter   = $this->getConnection();
        $auditExpr = $this->getAuditExpression();

        $this->getSelect()
            ->joinLeft(array('ia' => $auditSelect), 'ia.product_id=e.entity_id', array())
            ->joinLeft(array('ip' => $purchaseSelect), 'ip.product_id=e.entity_id', array())
            ->joinLeft(array('ib' => $backorderSelect), 'ib.product_id=e.entity_id', array());

        $onHandExpr = $adapter->getIfNullSql('ip.qty_stocked', '0') . ' - ' . $adapter->getIfNullSql('ib.qty_backordered', '0');
        $this->addExpressionAttributeToSelect('qty_on_hand',
            $adapter->getCheckSql($auditExpr, $onHandExpr, 'cisi.qty'), array('audit_inventory'));

        $this->getSelect()->where('(ia.product_id IS NOT NULL OR ' .
            str_replace('{{audit_inventory}}', $this->_getAttributeFieldName('audit_inventory'), $auditExpr) . ')');

        return $this;
    }

    public function joinNewCost()
    {
        $costSelect = Mage::getResourceModel('unl_inventory/purchase')->getNewProductCostSelect();

        $this->getSelect()
            ->joinLeft(array('ic' => $costSelect), 'ic.product_id=e.entity_id', array('new_cost'));

        return $this;
    }

    public function addManageStockFilter()
    {
        $this->getSelect()->where('cisi.use_config_manage_stock');

        return $this;
    }
}

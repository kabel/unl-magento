<?php

class Unl_CustomerTag_Model_Resource_Tag extends Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Initialize resource connection and define table resource
     *
     */
    protected function _construct()
    {
        $this->_init('unl_customertag/tag', 'tag_id');
    }

    /**
     * Retreive products with tagged access
     *
     * @param Mage_Catalog_Model_Product $model
     * @return array
     */
    public function getProductAccess($model)
    {
        return $this->_getLinks('product', $model);
    }

	/**
     * Retreive products with tagged access
     *
     * @param Mage_Catalog_Model_Category $model
     * @return array
     */
    public function getCategoryAccess($model)
    {
        return $this->_getLinks('category', $model);
    }

    /**
     * Retreive Tagged Customers
     *
     * @param Unl_CustomerTag_Model_Tag $model
     * @return array
     */
    public function getCustomerIds($model)
    {
        return $this->_getLinks('', $model);
    }

    /**
     * Enter description here ...
     *
     * @param string $type
     * @param Mage_Core_Model_Abstract $model
     * @return array
     */
    protected function _getLinks($type, $model, $typeId = null)
    {
        $col = 'tag_id';
        if ($model instanceof Unl_CustomerTag_Model_Tag) {
            $type = '';
            $typeId = $model->getIdFieldName();
            $col = 'customer_id';
        } else {
            $type = rtrim($type, '_') . '_';
        }

        if (null === $typeId) {
            $typeId = (empty($type) ? 'customer_' : $type) . 'id';
        }

        $adapter = $this->_getWriteAdapter();

        $select = $adapter->select()
            ->from($this->getTable("unl_customertag/{$type}link"), $col)
            ->where($adapter->prepareSqlCondition($typeId, $model->getId()));

        return $adapter->fetchCol($select);
    }

    /**
     * Add entity to TAG link
     *
     * @param string $type
     * @param Mage_Core_Model_Abstract $model
     * @param array $links
     * @param array $baseData
     * @param string $typeId
     * @return Unl_CustomerTag_Model_Resource_Tag
     */
    protected function _addLinks($type, $model, $links, $baseData = array(), $typeId = null)
    {
        $oldLinkIds = $this->_getLinks($type, $model);

        $col = 'tag_id';
        if ($model instanceof Unl_CustomerTag_Model_Tag) {
            $type = '';
            $typeId = $model->getIdFieldName();
            $col = 'customer_id';
        } else {
            $type = rtrim($type, '_') . '_';
        }

        if (null === $typeId) {
            $typeId = (empty($type) ? 'customer_' : $type) . 'id';
        }

        $adapter = $this->_getWriteAdapter();

        $insert = array_diff($links, $oldLinkIds);
        $delete = array_diff($oldLinkIds, $links);

        if (!empty($insert)) {
            $insertData = array();
            foreach ($insert as $value) {
                $insertData[] = array_merge($baseData, array(
                    $typeId  => $model->getId(),
                    $col     => $value
                ));
            }
            $adapter->insertMultiple($this->getTable("unl_customertag/{$type}link"), $insertData);
        }

        if (!empty($delete)) {
            $adapter->delete($this->getTable("unl_customertag/{$type}link"), array(
                $adapter->prepareSqlCondition($col, array('in' => $delete)),
                $adapter->prepareSqlCondition($typeId, $model->getId())
            ));
        }

        return $this;
    }

	/**
     * Add CATEGORY to TAG link
     *
     * @param Mage_Catalog_Model_Category $model
     * @return Unl_CustomerTag_Model_Resource_Tag
     */
    public function addCategoryLinks($model)
    {
        return $this->_addLinks('category', $model, $model->getAccessTagIds());
    }

    /**
     * Add PRODUCT to TAG link
     *
     * @param Mage_Catalog_Model_Product $model
     * @return Unl_CustomerTag_Model_Resource_Tag
     */
    public function addProductLinks($model)
    {
        return $this->_addLinks('product', $model, $model->getAccessTagIds());
    }

    /**
     * Add CUSTOMER to TAG link
     *
     * @param Mage_Customer_Model_Customer $model
     * @return Unl_CustomerTag_Model_Resource_Tag
     */
    public function addTagLinks($model)
    {
        return $this->_addLinks('', $model, $model->getAddedCustomerTagIds(), array(
            'created_at' => $this->formatDate(Mage::getModel('core/date')->gmtTimestamp())
        ));
    }

    /**
     * Add TAG to CUSTOMER link
     *
     * @param Unl_CustomerTag_Model_Tag $model
     * @return Unl_CustomerTag_Model_Resource_Tag
     */
    public function addCustomerLinks($model)
    {
        return $this->_addLinks('', $model, $model->getAddedCustomerIds(), array(
            'created_at' => $this->formatDate(Mage::getModel('core/date')->gmtTimestamp())
        ));
    }
}

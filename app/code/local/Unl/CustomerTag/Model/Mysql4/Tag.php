<?php

class Unl_CustomerTag_Model_Mysql4_Tag extends Mage_Core_Model_Mysql4_Abstract
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
        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('unl_customertag/product_link'), 'tag_id')
            ->where("product_id=?", $model->getId());

        return $this->_getReadAdapter()->fetchCol($select);
    }

	/**
     * Retreive products with tagged access
     *
     * @param Mage_Catalog_Model_Category $model
     * @return array
     */
    public function getCategoryAccess($model)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('unl_customertag/category_link'), 'tag_id')
            ->where("category_id=?", $model->getId());

        return $this->_getReadAdapter()->fetchCol($select);
    }

    /**
     * Retreive Tagged Customers
     *
     * @param Unl_CustomerTag_Model_Tag $model
     * @return array
     */
    public function getCustomerIds($model)
    {
        $select = $this->_getReadAdapter()->select()
            ->from($this->getTable('unl_customertag/link'), 'customer_id')
            ->where("tag_id=?", $model->getId());

        return $this->_getReadAdapter()->fetchCol($select);
    }

	/**
     * Add CATEGORY to TAG link
     *
     * @param Mage_Catalog_Model_Category $model
     * @return Unl_CustomerTag_Model_Mysql4_Tag
     */
    public function addCategoryLinks($model)
    {
        $addedIds = $model->getAccessTagIds();

        $select = $this->_getWriteAdapter()->select()
            ->from($this->getTable('unl_customertag/category_link'), 'tag_id')
            ->where("category_id = ?", $model->getId());
        $oldLinkIds = $this->_getWriteAdapter()->fetchCol($select);

        $insert = array_diff($addedIds, $oldLinkIds);
        $delete = array_diff($oldLinkIds, $addedIds);

        if (!empty($insert)) {
            $insertData = array();
            foreach ($insert as $value) {
                $insertData[] = array(
                    'category_id' => $model->getId(),
                    'tag_id'     => $value
                );
            }
            $this->_getWriteAdapter()->insertMultiple($this->getTable('unl_customertag/category_link'), $insertData);
        }

        if (!empty($delete)) {
            $this->_getWriteAdapter()->delete($this->getTable('unl_customertag/category_link'), array(
                $this->_getWriteAdapter()->quoteInto('tag_id IN (?)', $delete),
                $this->_getWriteAdapter()->quoteInto('category_id = ?', $model->getId())
            ));
        }

        return $this;
    }

    /**
     * Add PRODUCT to TAG link
     *
     * @param Mage_Catalog_Model_Product $model
     * @return Unl_CustomerTag_Model_Mysql4_Tag
     */
    public function addProductLinks($model)
    {
        $addedIds = $model->getAccessTagIds();

        $select = $this->_getWriteAdapter()->select()
            ->from($this->getTable('unl_customertag/product_link'), 'tag_id')
            ->where("product_id = ?", $model->getId());
        $oldLinkIds = $this->_getWriteAdapter()->fetchCol($select);

        $insert = array_diff($addedIds, $oldLinkIds);
        $delete = array_diff($oldLinkIds, $addedIds);

        if (!empty($insert)) {
            $insertData = array();
            foreach ($insert as $value) {
                $insertData[] = array(
                    'product_id' => $model->getId(),
                    'tag_id'     => $value
                );
            }
            $this->_getWriteAdapter()->insertMultiple($this->getTable('unl_customertag/product_link'), $insertData);
        }

        if (!empty($delete)) {
            $this->_getWriteAdapter()->delete($this->getTable('unl_customertag/product_link'), array(
                $this->_getWriteAdapter()->quoteInto('tag_id IN (?)', $delete),
                $this->_getWriteAdapter()->quoteInto('product_id = ?', $model->getId())
            ));
        }

        return $this;
    }

    /**
     * Add CUSTOMER to TAG link
     *
     * @param Mage_Customer_Model_Customer $model
     */
    public function addTagLinks($model)
    {
        $addedIds = $model->getAddedCustomerTagIds();

        $select = $this->_getWriteAdapter()->select()
            ->from($this->getTable('unl_customertag/link'), 'tag_id')
            ->where("customer_id = ?", $model->getId());
        $oldLinkIds = $this->_getWriteAdapter()->fetchCol($select);

        $insert = array_diff($addedIds, $oldLinkIds);
        $delete = array_diff($oldLinkIds, $addedIds);

        if (!empty($insert)) {
            $insertData = array();
            foreach ($insert as $value) {
                $insertData[] = array(
                    'tag_id'        => $value,
                    'customer_id'   => $model->getId(),
                    'created_at'    => $this->formatDate(time())
                );
            }
            $this->_getWriteAdapter()->insertMultiple($this->getTable('unl_customertag/link'), $insertData);
        }

        if (!empty($delete)) {
            $this->_getWriteAdapter()->delete($this->getTable('unl_customertag/link'), array(
                $this->_getWriteAdapter()->quoteInto('tagIid IN (?)', $delete),
                $this->_getWriteAdapter()->quoteInto('customer_id = ?', $model->getId())
            ));
        }

        return $this;
    }

    /**
     * Add TAG to CUSTOMER link
     *
     * @param Unl_CustomerTag_Model_Tag $model
     */
    public function addCustomerLinks($model)
    {
        $addedIds = $model->getAddedCustomerIds();

        $select = $this->_getWriteAdapter()->select()
            ->from($this->getTable('unl_customertag/link'), 'customer_id')
            ->where("tag_id = ?", $model->getId());
        $oldLinkIds = $this->_getWriteAdapter()->fetchCol($select);

        $insert = array_diff($addedIds, $oldLinkIds);
        $delete = array_diff($oldLinkIds, $addedIds);

        if (!empty($insert)) {
            $insertData = array();
            foreach ($insert as $value) {
                $insertData[] = array(
                    'tag_id'        => $model->getId(),
                    'customer_id'   => $value,
                    'created_at'    => $this->formatDate(time())
                );
            }
            $this->_getWriteAdapter()->insertMultiple($this->getTable('unl_customertag/link'), $insertData);
        }

        if (!empty($delete)) {
            $this->_getWriteAdapter()->delete($this->getTable('unl_customertag/link'), array(
                $this->_getWriteAdapter()->quoteInto('customer_id IN (?)', $delete),
                $this->_getWriteAdapter()->quoteInto('tag_id = ?', $model->getId())
            ));
        }

        return $this;
    }
}

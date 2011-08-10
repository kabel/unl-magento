<?php

class Unl_Core_Model_Catalog_Resource_Eav_Mysql4_Product_Link_Product_Collection
    extends Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Link_Product_Collection
{

    /* Overrides
     * @see Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Link_Product_Collection::addAttributeToFilter()
     * by adding special logic for the position link attribute
     */
    public function addAttributeToFilter($attribute, $condition=null, $joinType='inner')
    {
        /**
         * Position is not eav attribute (it is links attribute) so we cannot use default attributes to sort
         */
        if ($attribute == 'position') {
            if ($this->_hasLinkFilter) {
                $this->getSelect()->where($this->_getConditionSql('link_attribute_position_int.value', $condition));
            }
        } else {
            parent::addAttributeToFilter($attribute, $condition, $joinType);
        }
        return $this;
    }
}

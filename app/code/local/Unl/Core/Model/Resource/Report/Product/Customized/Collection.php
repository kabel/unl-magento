<?php

class Unl_Core_Model_Resource_Report_Product_Customized_Collection extends Unl_Core_Model_Resource_Report_Product_Orderdetails_Collection
{
    /* PHP required logic to remove items from the collection after load.
     * @see Mage_Sales_Model_Resource_Order_Item_Collection::_afterLoad()
     * WARNING: This breaks the standard paging mechanisms of the collection
     */
    protected function _afterLoad()
    {
        foreach ($this as $key => $item) {
            /* @var $item Mage_Sales_Model_Order_Item */
            $options = $item->getProductOptionByCode('options');
            if (empty($options)) {
                $this->removeItemByKey($key);
            }
        }

        return parent::_afterLoad();
    }
}

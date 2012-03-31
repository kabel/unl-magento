<?php

class Unl_Core_Model_Sales_Quote_Config extends Mage_Sales_Model_Quote_Config
{
    const XML_PATH_QUOTE_SET_PRODUCT_ATTRIBUTES = 'global/sales/quote/item/set_product_attributes';
    const XML_PATH_QUOTE_ADDRESS_ITEM_PARENT_ATTRIBUTES = 'global/sales/quote/item/address_item_parent_attributes';

    public function getSetProductAttributes()
    {
        $attributes = Mage::getConfig()->getNode(self::XML_PATH_QUOTE_SET_PRODUCT_ATTRIBUTES)->asArray();
        return array_keys($attributes);
    }

    public function getAddressItemParentAttributes()
    {
        $attributes = Mage::getConfig()->getNode(self::XML_PATH_QUOTE_ADDRESS_ITEM_PARENT_ATTRIBUTES)->asArray();
        return array_merge($this->getSetProductAttributes(), array_keys($attributes));
    }
}

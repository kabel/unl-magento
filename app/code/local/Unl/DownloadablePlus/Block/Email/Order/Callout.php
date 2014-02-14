<?php

/**
 * @method Mage_Sales_Model_Order getOrder()
 */
class Unl_DownloadablePlus_Block_Email_Order_Callout extends Mage_Sales_Block_Items_Abstract
{
    protected function _construct()
    {
        $this->setTemplate('downloadable/email/order/callout.phtml');
        parent::_construct();
    }

    public function hasDowloadableItems()
    {
        /* @var $_items Mage_Sales_Model_Order_Item[] */
        $_items = $this->getOrder()->getAllItems();
        foreach ($_items as $item) {
            if ($this->_getItemType($item) == Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE) {
                return true;
            }
        }

        return false;
    }

    public function canShow()
    {
        if ($this->hasDowloadableItems()) {
            return true;
        }

        return false;
    }
}

<?php
class Unl_Core_Block_Bundle_Adminhtml_Sales_Order_Items_Renderer extends Mage_Bundle_Block_Adminhtml_Sales_Order_Items_Renderer
{
    /**
     * Getting all available childs for Invoice, Shipmen or Creditmemo item
     *
     * @param Varien_Object $item
     * @return array
     */
    public function getChilds($item)
    {
        $_itemsArray = array();

        $_items = null;
        if ($item instanceof Mage_Sales_Model_Order_Invoice_Item) {
            $_items = $item->getInvoice()->getAllItems();
        } else if ($item instanceof Mage_Sales_Model_Order_Shipment_Item) {
            $_items = $item->getShipment()->getAllItems();
        } else if ($item instanceof Mage_Sales_Model_Order_Creditmemo_Item) {
            $_items = $item->getCreditmemo()->getAllItems();
        } else if ($item instanceof Mage_Sales_Model_Order_Item) {
            $_itemsArray = array_merge(array($item), $item->getChildrenItems());
        }

        if ($_items) {
            foreach ($_items as $_item) {
                $offsetItem = $_item->getOrderItem()->getParentItem();
                if (!$offsetItem) {
                    $offsetItem = $_item->getOrderItem();
                }
                
                if ($offsetItem->getId() != $item->getOrderItem()->getId()) {
                    continue;
                }
                    
                $_itemsArray[$_item->getOrderItemId()] = $_item;
            }
        }

        if (!empty($_itemsArray)) {
            uasort($_itemsArray, array($this, '_sortChilds'));
            return $_itemsArray;
        } else {
            return null;
        }
    }
    
	/**
     * A custom sort callback used to ensure bundle selections for the
     * same option_id are together in the result array
     * 
     * @param Varien_Object $a
     * @param Varien_Object $b
     */
    protected function _sortChilds($a, $b)
    {
        $aAttributes = $this->getSelectionAttributes($a);
        $bAttributes = $this->getSelectionAttributes($b);
        
        if (!$aAttributes) {
            return (!$bAttributes) ? 0 : -1;
        } else {
            if (!$bAttributes) {
                return 1;
            }
            
            if ($aAttributes['option_id'] == $bAttributes['option_id']) {
                return 0;
            }
            return ($aAttributes['option_id'] < $bAttributes['option_id']) ? -1 : 1;
        }
    }
}

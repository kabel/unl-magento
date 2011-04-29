<?php

class Unl_BundlePlus_Block_Adminhtml_Sales_Order_View_Items_Renderer
    extends Mage_Bundle_Block_Adminhtml_Sales_Order_View_Items_Renderer
{
    public function getChilds($item)
    {
        $_itemsArray = array_merge(array($item), $item->getChildrenItems());
        uasort($_itemsArray, array($this, '_sortChilds'));
        return $_itemsArray;
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

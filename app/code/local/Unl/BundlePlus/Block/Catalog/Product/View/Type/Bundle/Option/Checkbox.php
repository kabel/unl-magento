<?php

class Unl_BundlePlus_Block_Catalog_Product_View_Type_Bundle_Option_Checkbox
    extends Unl_BundlePlus_Block_Catalog_Product_View_Type_Bundle_Option
{
    public function _construct()
    {
        $this->setTemplate('bundle/catalog/product/view/type/bundle/option/checkbox.phtml');
    }

    /**
     * Returns the selection qty for a checkbox selection
     *
     * @param Mage_Bundle_Model_Selection $selection
     * @return number
     */
    protected function _getSelectionQty($selection)
    {
        if ($this->getProduct()->hasPreconfiguredValues()) {
            $selectedQty = $this->getProduct()->getPreconfiguredValues()
                ->getData('bundle_option_qty/' . $this->getOption()->getId());
            if (is_array($selectedQty)) {
                if (isset($selectedQty[$selection->getSelectionId()])) {
                    $selectedQty = $selectedQty[$selection->getSelectionId()];
                } else {
                    $selectedQty = 0;
                }
            }

            $selectedQty = (float)$selectedQty;
            if ($selectedQty < 0) {
                $selectedQty = 0;
            }
        } else {
            $selectedQty = 0;
        }

        return $selectedQty;
    }
    
    /**
     * Gets the default values for a checkbox selection
     *
     * @param Mage_Bundle_Model_Selection $selection
     */
    protected function _getSelectionDefaultValues($selection)
    {
        $selectedOptions = $this->_getSelectedOptions();
        $_canChangeQty = (bool)$selection->getSelectionCanChangeQty();
    
        if ((empty($selectedOptions) && $selection->getIsDefault()) || !$_canChangeQty) {
            $_defaultQty = $selection->getSelectionQty()*1;
        } else {
            $_defaultQty = $this->_getSelectionQty($selection)*1;
        }
        
        
        return array($_defaultQty, $_canChangeQty);
    }
}

<?php

class Unl_BundlePlus_Block_Adminhtml_Catalog_Product_Composite_Fieldset_Options_Type_Checkbox
    extends Mage_Bundle_Block_Adminhtml_Catalog_Product_Composite_Fieldset_Options_Type_Checkbox
{
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
}

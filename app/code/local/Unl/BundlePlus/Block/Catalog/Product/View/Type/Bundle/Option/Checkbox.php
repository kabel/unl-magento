<?php

class Unl_BundlePlus_Block_Catalog_Product_View_Type_Bundle_Option_Checkbox
    extends Unl_BundlePlus_Block_Catalog_Product_View_Type_Bundle_Option
{
    public function _construct()
    {
        $this->setTemplate('bundle/catalog/product/view/type/bundle/option/checkbox.phtml');
    }

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

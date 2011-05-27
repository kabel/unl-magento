<?php

class Unl_Core_Block_Catalog_Layer_View extends Mage_Catalog_Block_Layer_View
{
    protected function _prepareLayout()
    {
        $alphaBlock = $this->getLayout()->createBlock('unl_core/catalog_layer_filter_alpha')
            ->setLayer($this->getLayer())
            ->init();

        $this->setChild('alpha_filter', $alphaBlock);

        return parent::_prepareLayout();
    }

    public function getFilters()
    {
        $filters = parent::getFilters();
        if ($alphaFilter = $this->_getAlphaFilter()) {
            array_unshift($filters, $alphaFilter);
        }

        return $filters;
    }

    protected function _getAlphaFilter()
    {
        return $this->getChild('alpha_filter');
    }
}

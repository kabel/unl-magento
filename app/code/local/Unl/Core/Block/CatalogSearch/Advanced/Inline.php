<?php

class Unl_Core_Block_CatalogSearch_Advanced_Inline extends Mage_CatalogSearch_Block_Advanced_Form
{
    public function _prepareLayout()
    {
        if (!$this->getTemplate()) {
            $this->setTemplate('catalogsearch/advanced/inline.phtml');
        }

        return $this;
    }

    public function getSearchPostUrl()
    {
        return $this->getUrl('catalogsearch/advanced/result');
    }
}

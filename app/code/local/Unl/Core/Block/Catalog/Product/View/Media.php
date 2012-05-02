<?php

class Unl_Core_Block_Catalog_Product_View_Media extends Mage_Catalog_Block_Product_View_Media
{
    public function getGalleryUrl($image = null)
    {
        $params = array('id' => $this->getProduct()->getId());
        if ($image) {
            $params['image'] = $image->getValueId();
        }
        return $this->getUrl('catalog/product/gallery', $params);
    }
}

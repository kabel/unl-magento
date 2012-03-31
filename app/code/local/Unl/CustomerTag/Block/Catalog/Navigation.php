<?php

class Unl_CustomerTag_Block_Catalog_Navigation extends Mage_Catalog_Block_Navigation
{
    /* Overrides
     * @see Mage_Catalog_Block_Navigation::getCacheKeyInfo()
     * by adding access keys to the cache key
     */
    public function getCacheKeyInfo()
    {
        $tags = array();
        $session = Mage::getSingleton('customer/session');
        if ($session->getCustomerId()) {
            $tags = Mage::helper('unl_customertag')->getTagIdsByCustomer($session->getCustomer());
        }

        $shortCacheId = array(
            'CATALOG_NAVIGATION',
            Mage::app()->getStore()->getId(),
            Mage::getDesign()->getPackageName(),
            Mage::getDesign()->getTheme('template'),
            Mage::getSingleton('customer/session')->getCustomerGroupId(),
            'access_tags' => implode(',', $tags),
            'template' => $this->getTemplate(),
            'name' => $this->getNameInLayout(),
            $this->getCurrenCategoryKey()
        );
        $cacheId = $shortCacheId;

        $shortCacheId = array_values($shortCacheId);
        $shortCacheId = implode('|', $shortCacheId);
        $shortCacheId = md5($shortCacheId);

        $cacheId['category_path'] = $this->getCurrenCategoryKey();
        $cacheId['short_cache_id'] = $shortCacheId;

        return $cacheId;
    }
}

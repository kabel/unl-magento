<?php

class Unl_Core_Helper_Tax extends Mage_Core_Helper_Abstract
{
    const CACHE_FLAG = 'unl_tax';

    const TRANSLATE_CACHE_TAG = 'UNL_TAX_TRANSLATE';
    const TRANSLATE_CACHE_PREFIX = 'unl_tax_translate_';

    const BOUNDARY_CACHE_TAG = 'UNL_TAX_BOUNDARY';
    const BOUNDARY_CACHE_PREFIX = 'unl_tax_boundary_';

    protected $_zipListingRegistry = array();

    public function getDefaultCacheLifetime()
    {
        return 7 * 24 * 60 * 60; // 7 days
    }

    public function zipRegister($zip, $result)
    {
        $this->_zipListingRegistry[$zip] = $result;

        if ($this->isCacheEnabled()) {
            $lifetime = $this->getDefaultCacheLifetime();

            $this->_saveCache($result, self::TRANSLATE_CACHE_PREFIX . $zip, array(self::TRANSLATE_CACHE_TAG), $lifetime);
        }

        return $this;
    }

    public function zipRegistry($zip)
    {
        if (isset($this->_zipListingRegistry[$zip])) {
            return $this->_zipListingRegistry[$zip];
        }

        if ($this->isCacheEnabled()) {
            $cached = $this->_loadCache(self::TRANSLATE_CACHE_PREFIX . $zip);
            if ($cached !== false) {
                $this->_zipListingRegistry[$zip] = $cached;
                return $cached;
            }
        }

        return false;
    }

    public function isCacheEnabled()
    {
        return Mage::app()->useCache(self::CACHE_FLAG);
    }

    public function saveAddressCache($key, $value)
    {
        if ($this->isCacheEnabled()) {
            $lifetime = $this->getDefaultCacheLifetime();

            return $this->_saveCache($value, self::BOUNDARY_CACHE_PREFIX . $key, array(self::BOUNDARY_CACHE_TAG), $lifetime);
        }

        return $this;
    }

    public function loadAddressCache($key)
    {
        if ($this->isCacheEnabled()) {
            return $this->_loadCache(self::BOUNDARY_CACHE_PREFIX . $key);
        }

        return false;
    }

    public function cleanCache()
    {
        Mage::app()->getCacheInstance()->cleanType(self::CACHE_FLAG);

        return $this;
    }
}

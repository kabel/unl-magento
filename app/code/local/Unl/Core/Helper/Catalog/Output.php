<?php

class Unl_Core_Helper_Catalog_Output extends Mage_Core_Helper_Abstract
{
    public function productAttribute($helper, $result, $params)
    {
        if ($params['attribute'] == 'buros_url') {
            if (!preg_match('#^https?://#', $result)) {
                $result = 'http://' . $result;
            }

            $result = '<a href="' . $result . '" target="_blank" class="external">' . $result . '</a>';
        }

        return $result;
    }
}

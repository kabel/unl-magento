<?php

class Unl_Ship_Block_Order_View_Info extends Mage_Adminhtml_Block_Sales_Order_View_Info
{
    public function getUrl($route='', $params=array())
    {
        $route = preg_replace('/^\*\//', 'adminhtml/', $route);

        return parent::getUrl($route, $params);
    }
}

<?php

class Unl_Core_Block_Page_Html_Affiliatecrumbs extends Mage_Page_Block_Html_Breadcrumbs
{
    function __construct()
    {
        parent::__construct();
        $this->setTemplate('page/html/affiliatecrumbs.phtml');
    }
}

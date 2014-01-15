<?php

class Unl_Core_Block_Tag_Customer_Recent extends Mage_Tag_Block_Customer_Recent
{
    /* Overrides
     * @see Mage_Tag_Block_Customer_Recent::_toHtml()
     * by skipping right to the parent implementation
     */
    protected function _toHtml()
    {
        return Mage_Core_Block_Template::_toHtml();
    }
}

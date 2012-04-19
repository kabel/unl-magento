<?php

class Unl_Core_Block_Update extends Mage_Core_Block_Abstract
{
    public function sortParentGroups()
    {
        /* @var $block Mage_Page_Block_Switch */
        $block = $this->getParentBlock();

        $groups = $block->getGroups();
        if (count($groups) > 1) {
            $helper = Mage::helper('unl_core');
            usort($groups, array($helper, 'compareStoreGroups'));
            $block->setData('groups', $groups);
        }
    }
}

<?php

class Unl_Core_Block_Update extends Mage_Core_Block_Abstract
{
    public function sortParentGroups()
    {
        /* @var $block Mage_Page_Block_Switch */
        $block = $this->getParentBlock();

        $groups = $block->getGroups();
        if (count($groups) > 1) {
            usort($groups, array($this, '_compareStores'));
            $block->setData('groups', $groups);
        }
    }

    /**
     *
     * @param Mage_Core_Model_Store_Group $a
     * @param Mage_Core_Model_Store_Group $b
     * @return int
     */
    protected function _compareStores($a, $b)
    {
        $sortA = $a->getDefaultStore()->getSortOrder();
        $sortB = $b->getDefaultStore()->getSortOrder();

        if ($sortA == $sortB) {
            return 0;
        }
        return ($sortA > $sortB) ? 1 : -1;
    }
}

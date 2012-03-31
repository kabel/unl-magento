<?php

class Unl_Core_Helper_Catalog_Category extends Mage_Catalog_Helper_Category
{
    public function getStoreCategories($sorted=false, $asCollection=false, $toLoad=true)
    {
        $allowedCategories = array();
        $categories = parent::getStoreCategories($sorted, $asCollection, $toLoad);
        foreach ($categories as $k => $child) {
            $acl = $this->_filterCategories($child);
            if ($asCollection) {
                if (!$acl) {
                    if ($categories instanceof Varien_Data_Tree_Node_Collection) {
                        $categories->delete($child);
                    } else {
                        $categories->removeItemByKey($k);
                    }
                }
            } else if ($acl) {
                $allowedCategories[] = $child;
            }
        }

        if ($asCollection) {
            return $categories;
        }

        return $allowedCategories;
    }

    protected function _filterCategories($category)
    {
        $acl = Mage::helper('unl_core')->isCustomerAllowedCategory($category);

        if (!$acl) {
            return $acl;
        }

        if ($category instanceof Varien_Data_Tree_Node) {
            foreach ($category->getChildren() as $child) {
                if (!$this->_filterCategories($child)) {
                    $category->removeChild($child);
                }
            }
        }

        return $acl;
    }
}

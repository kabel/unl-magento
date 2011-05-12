<?php

class Unl_Core_Block_Adminhtml_Catalog_Category_Tree extends Mage_Adminhtml_Block_Catalog_Category_Tree
{
    protected function _getNodeJson($node, $level = 0)
    {
        // create a node from data array
        if (is_array($node)) {
            $node = new Varien_Data_Tree_Node($node, 'entity_id', new Varien_Data_Tree);
        }

        $item = array();
        $item['text'] = $this->buildNodeName($node);

        //$rootForStores = Mage::getModel('core/store')->getCollection()->loadByCategoryIds(array($node->getEntityId()));
        $rootForStores = in_array($node->getEntityId(), $this->getRootIds());

        $item['id']  = $node->getId();
        $item['store']  = (int) $this->getStore()->getId();
        $item['path'] = $node->getData('path');

        $item['cls'] = 'folder ' . ($node->getIsActive() ? 'active-category' : 'no-active-category');
        //$item['allowDrop'] = ($level<3) ? true : false;
        $allowMove = $this->_isCategoryMoveable($node);
        $item['allowDrop'] = $allowMove;
        // disallow drag if it's first level and category is root of a store
        $item['allowDrag'] = $allowMove && (($node->getLevel()==1 && $rootForStores) ? false : true);

        if ((int)$node->getChildrenCount()>0) {
            $item['children'] = array();
        }

        $isParent = $this->_isParentSelectedCategory($node);

        if ($node->hasChildren()) {
            $item['children'] = array();
            if (!($this->getUseAjax() && $node->getLevel() > 1 && !$isParent)) {
                foreach ($node->getChildren() as $child) {
                    if ($this->_isNodeInScope($child)) {
                        $item['children'][] = $this->_getNodeJson($child, $level+1);
                    }
                }
            }
        }

        if ($isParent || $node->getLevel() < 2) {
            $item['expanded'] = true;
        }

        return $item;
    }

    /**
     * Checks if the given node is a store root and in scope
     *
     * @param Varien_Data_Tree_Node $node
     */
    protected function _isNodeInScope($node)
    {
        if ($node->getLevel() == 1 && !in_array($node->getEntityId(), $this->getScopeRootIds())) {
            return false;
        }

        return true;
    }

    public function getScopeRootIds()
    {
        $ids = $this->getData('scope_root_ids');
        if (is_null($ids)) {
            $scope = $this->getUserScope();
            if (!$scope) {
                $ids = $this->getRootIds();
            } else {
                $ids = array();
                foreach ($scope as $groupId) {
                    $ids[] = Mage::app()->getGroup($groupId)->getRootCategoryId();
                }
            }
            $this->setData('scope_root_ids', $ids);
        }

        return $ids;
    }

    public function getUserScope()
    {
        $scope = $this->getData('user_scope');
        if (is_null($scope)) {
            $scope = Mage::helper('unl_core')->getAdminUserScope(true);
            if (!$scope) {
                $scope = array();
            }
            $this->setData('user_scope', $scope);
        }
        return $scope;
    }
}

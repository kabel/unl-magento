<?php

class Unl_Core_Model_Catalog_Resource_Eav_Mysql4_Category_Tree extends Mage_Catalog_Model_Resource_Eav_Mysql4_Category_Tree
{
    /**
     * Load whole category tree, that will include specified categories ids.
     *
     * @param array $ids
     * @param bool $addCollectionData
     * @return Unl_Core_Model_Catalog_Resource_Eav_Mysql4_Category_Tree
     */
    public function loadByIds($ids, $addCollectionData = true, $updateAnchorProductCount = true)
    {
        // load first two levels, if no ids specified
        if (empty($ids)) {
            $select = $this->_conn->select()
                ->from($this->_table, 'entity_id')
                ->where('`level` <= 2');
            $ids = $this->_conn->fetchCol($select);
        }
        if (!is_array($ids)) {
            $ids = array($ids);
        }
        foreach ($ids as $key => $id) {
            $ids[$key] = (int)$id;
        }

        // collect paths of specified IDs and prepare to collect all their parents and neighbours
        $select = $this->_conn->select()
            ->from($this->_table, array('path', 'level'))
            ->where(sprintf('entity_id IN (%s)', implode(', ', $ids)));
        $where = array('`level`=0' => true);
        $storeRootPaths = array();
        foreach ($this->_conn->fetchAll($select) as $item) {
            $path  = explode('/', $item['path']);
            $level = (int)$item['level'];
            while ($level > 0) {
                if ($level == 1) {
                    if (empty($storeRootPaths[$path[1]])) {
                        $storeRootPaths[$path[1]] = $path;
                    }
                } else {
                    $storeRootPaths[$path[1]] = true;
                }
                $path[count($path) - 1] = '%';
                $where[sprintf("`level`=%d AND `path` LIKE '%s'", $level, implode('/', $path))] = true;
                array_pop($path);
                $level--;
            }
        }
        foreach ($storeRootPaths as $key => $path) {
            if (is_array($path)) {
                $where[sprintf("`level`=2 AND `path` LIKE '%s/%%'", implode('/', $path))] = true;
            }
        }
        
        $where = array_keys($where);

        // get all required records
        if ($addCollectionData) {
            $select = $this->_createCollectionDataSelect();
        }
        else {
            $select = clone $this->_select;
            $select->order($this->_orderField . ' ASC');
        }
        $select->where(implode(' OR ', $where));

        // get array of records and add them as nodes to the tree
        $arrNodes = $this->_conn->fetchAll($select);
        if (!$arrNodes) {
            return false;
        }
        if ($updateAnchorProductCount) {
            $this->_updateAnchorProductCount($arrNodes);
        }
        $childrenItems = array();
        foreach ($arrNodes as $key => $nodeInfo) {
            $pathToParent = explode('/', $nodeInfo[$this->_pathField]);
            array_pop($pathToParent);
            $pathToParent = implode('/', $pathToParent);
            $childrenItems[$pathToParent][] = $nodeInfo;
        }
        $this->addChildNodes($childrenItems, '', null);

        return $this;
    }
}

<?php

class Unl_Core_Model_CatalogSearch_Resource_Helper_Mysql4 extends Mage_CatalogSearch_Model_Resource_Helper_Mysql4
{
    public function chooseFulltext($table, $alias, $select, $booleanMode = false)
    {
        $field = new Zend_Db_Expr('MATCH ('.$alias.'.data_index) AGAINST (:query)');
        $select->columns(array('relevance' => $field));

        if ($booleanMode) {
            return new Zend_Db_Expr('MATCH ('.$alias.'.data_index) AGAINST (:query IN BOOLEAN MODE)');
        }

        return $field;
    }
}

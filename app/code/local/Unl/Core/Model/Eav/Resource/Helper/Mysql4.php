<?php

class Unl_Core_Model_Eav_Resource_Helper_Mysql4 extends Mage_Eav_Model_Resource_Helper_Mysql4
{
     /* Overrides
      * @see Mage_Eav_Model_Resource_Helper_Mysql4::getLoadAttributesSelectGroups()
      * by separating numeric MySQL datatypes. This prevents type coercion.
      */
     public function getLoadAttributesSelectGroups($selects) {
         $mainGroup  = array();
         $altGroups = array();
         foreach ($selects as $eavType => $selectGroup) {
             if ($eavType == 'int' || $eavType == 'decimal') {
                 $altGroups[] = $selectGroup;
             } else {
                 $mainGroup = array_merge($mainGroup, $selectGroup);
             }
         }
         $altGroups[] = $mainGroup;
         return $altGroups;
     }
}

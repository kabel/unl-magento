<?php

class Unl_Core_Model_Catalog_Category_Attribute_Backend_Groupacl
    extends Mage_Eav_Model_Entity_Attribute_Backend_Abstract
{
    public function beforeSave($object)
    {
        $attributeCode = $this->getAttribute()->getName();
        $data = $object->getData($attributeCode);
        if (!is_array($data)) {
            $data = array();
        }
        $object->setData($attributeCode, join(',', $data));
        if (is_null($object->getData($attributeCode))) {
            $object->setData($attributeCode, false);
        }
        return $this;
    }

    public function afterLoad($object)
    {
        $attributeCode = $this->getAttribute()->getName();
        $data = $object->getData($attributeCode);
        if ($data) {
            $object->setData($attributeCode, explode(',', $data));
        }
        return $this;
    }
}

<?php

class Unl_Core_Model_Catalog_Category_Attribute_Backend_Image
    extends Mage_Catalog_Model_Category_Attribute_Backend_Image
{
    /* Extends
     * @see Mage_Catalog_Model_Category_Attribute_Backend_Image::afterSave()
     * by stopping logic if there are no uploads
     */
    public function afterSave($object)
    {
        $value = $object->getData($this->getAttribute()->getName());

        if (is_array($value) && !empty($value['delete'])) {
            return parent::afterSave($object);
        }

        if (empty($_FILES)) {
            //prevent core exception being logged all the time
            return;
        }

        return parent::afterSave($object);
    }
}

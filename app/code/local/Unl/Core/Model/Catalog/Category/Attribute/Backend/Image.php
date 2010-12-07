<?php

class Unl_Core_Model_Catalog_Category_Attribute_Backend_Image  extends Mage_Catalog_Model_Category_Attribute_Backend_Image
{

    /**
     * Save uploaded file and set its name to category
     *
     * @param Varien_Object $object
     */
    public function afterSave($object)
    {
        $value = $object->getData($this->getAttribute()->getName());

        if (is_array($value) && !empty($value['delete'])) {
            $object->setData($this->getAttribute()->getName(), '');
            $this->getAttribute()->getEntity()
                ->saveAttribute($object, $this->getAttribute()->getName());
            return;
        }

        if (empty($_FILES)) {
            //prevent core exception being logged all the time
            return;
        }

        $path = Mage::getBaseDir('media') . DS . 'catalog' . DS . 'category' . DS;

        try {
            $uploader = new Varien_File_Uploader($this->getAttribute()->getName());
            $uploader->setAllowedExtensions(array('jpg','jpeg','gif','png'));
            $uploader->setAllowRenameFiles(true);
            $uploader->save($path);

            $object->setData($this->getAttribute()->getName(), $uploader->getUploadedFileName());
            $this->getAttribute()->getEntity()->saveAttribute($object, $this->getAttribute()->getName());
        } catch (Exception $e) {
            if ($e->getCode() == UPLOAD_ERR_NO_FILE) {
                return;
            }

            Mage::logException($e);
            /** @TODO ??? */
            return;
        }
    }
}

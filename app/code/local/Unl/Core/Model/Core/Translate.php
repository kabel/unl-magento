<?php

class Unl_Core_Model_Core_Translate extends Mage_Core_Model_Translate
{
    /* Extended the logic of
     * @see Mage_Core_Model_Translate::getTemplateFile()
     * by checking the design if the locale is null
     */
    public function getTemplateFile($file, $type, $localeCode=null)
    {
        if (!is_null($localeCode)) {
            return parent::getTemplateFile($file, $type, $localeCode);
        }

        $designPackage = Mage::getDesign();
        $filePath = $designPackage->getLocaleFileName('template' . DS . $type . DS . $file);

        if (!file_exists($filePath)) {  // If no template for current design package, use original logic
            return parent::getTemplateFile($file, $type, $localeCode);
        }

        $ioAdapter = new Varien_Io_File();
        $ioAdapter->open(array('path' => Mage::getBaseDir('design')));

        return (string) $ioAdapter->read($filePath);
    }
}

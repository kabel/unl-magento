<?php

class Unl_Email_Model_Core_Translate extends Mage_Core_Model_Translate
{
    /* Extends
     * @see Mage_Core_Model_Translate::getTemplateFile()
     * by first checking the design package for locale files
     */
    public function getTemplateFile($file, $type, $localeCode=null)
    {
        $designPackage = Mage::getDesign();
        $filePath = $designPackage->getLocaleFileName('template' . DS . $type . DS . $file);

        if (!file_exists($filePath)) {
            // If no template for current design package, use original logic
            return parent::getTemplateFile($file, $type, $localeCode);
        }

        $ioAdapter = new Varien_Io_File();
        $ioAdapter->open(array('path' => Mage::getBaseDir('design')));

        return (string) $ioAdapter->read($filePath);
    }
}

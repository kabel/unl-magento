<?php

class Unl_Core_Model_Core_Translate extends Mage_Core_Model_Translate
{
    /**
     * Retrive translated template file
     *
     * @param string $file
     * @param string $type
     * @param string $localeCode
     * @return string
     */
    public function getTemplateFile($file, $type, $localeCode=null)
    {
        if (is_null($localeCode) || preg_match('/[^a-zA-Z_]/', $localeCode)) {
            $localeCode = $this->getLocale();
        }
        
        $designPackage = Mage::getModel('core/design_package');
        $filePath = Mage::getBaseDir('design') . DS . 'frontent' .  DS
                  . $designPackage->getPackageName() . DS . $designPackage->getTheme('locale') . DS . 'locale' . DS
                  . $localeCode . DS . 'template' . DS . $type . DS . $file;

        if (!file_exists($filePath)) {  // If no template for current design package theme, try default
            $filePath = Mage::getBaseDir('design') . DS . 'frontent' .  DS
                  . $designPackage->getPackageName() . DS . 'default' . DS . 'locale' . DS
                  . $localeCode . DS . 'template' . DS . $type . DS . $file;
        }

        if (!file_exists($filePath)) {  // If no template for current design package, use original logic
            return parent::getTemplateFile($file, $type, $localeCode);
        }

        $ioAdapter = new Varien_Io_File();
        $ioAdapter->open(array('path' => Mage::getBaseDir('locale')));

        return (string) $ioAdapter->read($filePath);
    }
}
<?php

abstract class Unl_Core_Model_Sales_Order_Pdf_Items_Abstract extends Mage_Sales_Model_Order_Pdf_Items_Abstract
{
    const DEFAULT_FONT_SIZE   = 10;
    const DEFAULT_LINE_HEIGHT = 13;
    
    protected function _setFontRegular($size = self::DEFAULT_FONT_SIZE)
    {
        $font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/LinLibertineFont/LinLibertineC_Re-2.8.0.ttf');
        $this->getPage()->setFont($font, $size);
        return $font;
    }

    protected function _setFontBold($size = self::DEFAULT_FONT_SIZE)
    {
        $font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/LinLibertineFont/LinLibertine_Bd-2.8.1.ttf');
        $this->getPage()->setFont($font, $size);
        return $font;
    }

    protected function _setFontItalic($size = self::DEFAULT_FONT_SIZE)
    {
        $font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/LinLibertineFont/LinLibertine_It-2.8.2.ttf');
        $this->getPage()->setFont($font, $size);
        return $font;
    }
}

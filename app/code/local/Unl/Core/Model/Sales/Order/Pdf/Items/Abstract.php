<?php

abstract class Unl_Core_Model_Sales_Order_Pdf_Items_Abstract extends Mage_Sales_Model_Order_Pdf_Items_Abstract
{
    const DEFAULT_FONT_SIZE   = 10;
    const DEFAULT_LINE_HEIGHT = 13;

    const DEFAULT_OFFSET_OPTION  = 35;
    const DEFAULT_OFFSET_PAD     = 10;

    const DEFAULT_TRIM_OPTION    = 78;
    const DEFAULT_TRIM_VALUE     = 85;

    const FONT_SIZE_GIFTMESSAGE   = 8;
    const LINE_HEIGHT_GIFTMESSAGE = 10;

    protected function _setFontRegular($size = self::DEFAULT_FONT_SIZE)
    {
        $font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/LinLibertineFont513/LinLibertine_aS.ttf');
        $this->getPage()->setFont($font, $size);
        return $font;
    }

    protected function _setFontBold($size = self::DEFAULT_FONT_SIZE)
    {
        $font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/LinLibertineFont513/LinLibertine_RB.ttf');
        $this->getPage()->setFont($font, $size);
        return $font;
    }

    protected function _setFontItalic($size = self::DEFAULT_FONT_SIZE)
    {
        $font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/LinLibertineFont513/LinLibertine_RI.ttf');
        $this->getPage()->setFont($font, $size);
        return $font;
    }

    public function drawOptions()
    {
        $pdf     = $this->getPdf();
        $page    = $this->getPage();
        $options = $this->getItemOptions();
        $blocks  = array();

        if ($options) {
            foreach ($options as $option) {
                $lines = array();

                // draw options label
                $lines[][] = array(
                    'text' => Mage::helper('core/string')->str_split(strip_tags($option['label']), self::DEFAULT_TRIM_OPTION, true, true),
                    'font' => 'italic',
                    'feed' => self::DEFAULT_OFFSET_OPTION
                );

                if ($option['value']) {
                    $_printValue = isset($option['print_value']) ? $option['print_value'] : strip_tags($option['value']);
//                     $values = explode(', ', $_printValue);
//                     foreach ($values as $value) {
                        $lines[][] = array(
                            'text' => Mage::helper('core/string')->str_split($_printValue, self::DEFAULT_TRIM_VALUE, true, true),
                            'feed' => self::DEFAULT_OFFSET_OPTION + self::DEFAULT_OFFSET_PAD
                        );
//                     }
                }

                $blocks[] = array(
                    'lines'  => $lines,
                    'height' => self::DEFAULT_LINE_HEIGHT
                );
            }

            $page = $pdf->drawLineBlocks($page, $blocks, array('table_header' => true));
            $this->setPage($page);
        }
    }

    public function drawGiftMessage()
    {
        $pdf    = $this->getPdf();
        $page   = $this->getPage();
        $item   = $this->getItem();
        $lines  = array();
        $helper = Mage::helper('giftmessage/message');

        if ($helper->getIsMessagesAvailable('order_item', $item->getOrderItem()) && $item->getOrderItem()->getGiftMessageId()) {
            $giftMessage = $helper->getGiftMessageForEntity($item->getOrderItem());
            $lines[][] = array(
                'text'  => Mage::helper('core/string')->str_split($helper->__('Gift Message'), self::DEFAULT_TRIM_OPTION, true, true),
                'font'  => 'italic',
                'feed'  => self::DEFAULT_OFFSET_OPTION
            );

            $text = array_merge(Mage::helper('core/string')->str_split($helper->__('From: ') . $giftMessage->getSender(), self::DEFAULT_TRIM_VALUE, true, true),
                Mage::helper('core/string')->str_split($helper->__('To: ') . $giftMessage->getRecipient(), self::DEFAULT_TRIM_VALUE, true, true));
            foreach (explode("\n", $giftMessage->getMessage()) as $line) {
                foreach (Mage::helper('core/string')->str_split(strip_tags($line), self::DEFAULT_TRIM_VALUE, true, true) as $value) {
                    $text[] = $value;
                }
            }
            $lines[][] = array(
                'text'  => $text,
                'font_size' => self::FONT_SIZE_GIFTMESSAGE,
                'height' => self::LINE_HEIGHT_GIFTMESSAGE,
                'feed'  => self::DEFAULT_OFFSET_OPTION + self::DEFAULT_OFFSET_PAD
            );

            $lineBlock = array(
                'lines'  => $lines,
                'height' => self::DEFAULT_LINE_HEIGHT
            );

            $page = $pdf->drawLineBlocks($page, array($lineBlock), array('table_header' => true));
            $this->setPage($page);
        }
    }
}

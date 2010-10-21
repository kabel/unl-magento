<?php

class Unl_Core_Model_Sales_Order_Pdf_Items_Shipment_Default extends Unl_Core_Model_Sales_Order_Pdf_Items_Abstract
{
    const DEFAULT_OFFSET_QTY      = 35;
    const DEFAULT_OFFSET_PRODUCT  = 65;
    const DEFAULT_OFFSET_SKU      = 440;
    
    const DEFAULT_OFFSET_PAD      = 10;
    
    const DEFAULT_TRIM_PRODUCT    = 60;
    const DEFAULT_TRIM_OPTION     = 50;
    const DEFAULT_TRIM_SKU        = 25;
    const DEFAULT_TRIM_MESSAGE    = 80;
    
    const FONT_SIZE_GIFTMESSAGE   = 8;
    const LINE_HEIGHT_GIFTMESSAGE = 10;
    
    /**
     * Draw item line
     *
     */
    public function draw()
    {
        $item   = $this->getItem();
        $pdf    = $this->getPdf();
        $page   = $this->getPage();
        $lines  = array();

        // draw Product name
        $lines[0] = array(array(
            'text' => Mage::helper('core/string')->str_split($item->getName(), self::DEFAULT_TRIM_PRODUCT, true, true),
            'feed' => self::DEFAULT_OFFSET_PRODUCT,
        ));

        // draw QTY
        $lines[0][] = array(
            'text'  => $item->getQty()*1,
            'feed'  => self::DEFAULT_OFFSET_QTY
        );

        // draw SKU
        $lines[0][] = array(
            'text'  => Mage::helper('core/string')->str_split($this->getSku($item), self::DEFAULT_TRIM_SKU),
            'feed'  => self::DEFAULT_OFFSET_SKU
        );

        // Custom options
        $options = $this->getItemOptions();
        if ($options) {
            foreach ($options as $option) {
                // draw options label
                $lines[][] = array(
                    'text' => Mage::helper('core/string')->str_split(strip_tags($option['label']), self::DEFAULT_TRIM_PRODUCT, true, true),
                    'font' => 'italic',
                    'feed' => self::DEFAULT_OFFSET_PRODUCT
                );

                // draw options value
                if ($option['value']) {
                    $_printValue = isset($option['print_value']) ? $option['print_value'] : strip_tags($option['value']);
                    $values = explode(', ', $_printValue);
                    foreach ($values as $value) {
                        $lines[][] = array(
                            'text' => Mage::helper('core/string')->str_split($value, self::DEFAULT_TRIM_OPTION, true, true),
                            'feed' => self::DEFAULT_OFFSET_PRODUCT + self::DEFAULT_OFFSET_PAD
                        );
                    }
                }
            }
        }
        
        // gift message
        $helper = Mage::helper('giftmessage/message'); 
        if ($helper->getIsMessagesAvailable('order_item', $item->getOrderItem()) && $item->getOrderItem()->getGiftMessageId()) {
            $giftMessage = $helper->getGiftMessageForEntity($item->getOrderItem());
            $lines[][] = array(
                'text'  => Mage::helper('core/string')->str_split($helper->__('Gift Message'), self::DEFAULT_TRIM_PRODUCT, true, true),
                'font'  => 'italic',
                'feed'  => self::DEFAULT_OFFSET_PRODUCT
            );
            
            $text = array_merge(Mage::helper('core/string')->str_split($helper->__('From: ') . $giftMessage->getSender(), self::DEFAULT_TRIM_MESSAGE, true, true), 
                Mage::helper('core/string')->str_split($helper->__('To: ') . $giftMessage->getRecipient(), self::DEFAULT_TRIM_MESSAGE, true, true));
            foreach (explode("\n", $giftMessage->getMessage()) as $line) {
                foreach (Mage::helper('core/string')->str_split(strip_tags($line), self::DEFAULT_TRIM_MESSAGE, true, true) as $value) {
                    $text[] = $value;
                }
            }
            $lines[][] = array(
                'text'  => $text,
                'font_size' => self::FONT_SIZE_GIFTMESSAGE,
                'height' => self::LINE_HEIGHT_GIFTMESSAGE,
                'feed'  => self::DEFAULT_OFFSET_PRODUCT + self::DEFAULT_OFFSET_PAD
            );
        }

        $lineBlock = array(
            'lines'  => $lines,
            'height' => self::DEFAULT_LINE_HEIGHT
        );

        $page = $pdf->drawLineBlocks($page, array($lineBlock), array('table_header' => true));
        $this->setPage($page);
    }
}

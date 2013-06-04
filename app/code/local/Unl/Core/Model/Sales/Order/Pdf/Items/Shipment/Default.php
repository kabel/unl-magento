<?php

class Unl_Core_Model_Sales_Order_Pdf_Items_Shipment_Default extends Unl_Core_Model_Sales_Order_Pdf_Items_Abstract
{
    const DEFAULT_OFFSET_QTY      = 35;
    const DEFAULT_OFFSET_PRODUCT  = 65;
    const DEFAULT_OFFSET_SKU      = 440;

    const DEFAULT_OFFSET_PAD      = 10;

    const DEFAULT_TRIM_PRODUCT    = 60;
    const DEFAULT_TRIM_SKU        = 25;

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

        $lineBlock = array(
            'lines'  => $lines,
            'height' => self::DEFAULT_LINE_HEIGHT
        );

        $page = $pdf->drawLineBlocks($page, array($lineBlock), array('table_header' => true));
        $this->setPage($page);

        $this->drawOptions();
        $this->drawGiftMessage();
    }
}

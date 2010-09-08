<?php

class Unl_Core_Model_Sales_Order_Pdf_Items_Invoice_Default extends Unl_Core_Model_Sales_Order_Pdf_Items_Abstract
{
    const DEFAULT_OFFSET_PRODUCT  = 35;
    const DEFAULT_OFFSET_SKU      = 255;
    const DEFAULT_OFFSET_QTY      = 435;
    const DEFAULT_OFFSET_PRICE    = 405;
    const DEFAULT_OFFSET_TAX      = 500;
    const DEFAULT_OFFSET_SUBTOTAL = 575;
    
    const DEFAULT_OFFSET_PAD      = 10;
    
    const DEFAULT_TRIM_PRODUCT    = 48;
    const DEFAULT_TRIM_OPTION     = 30;
    const DEFAULT_TRIM_SKU        = 20;
    
    
    /**
     * Draw item line
     *
     */
    public function draw()
    {
        $order  = $this->getOrder();
        $item   = $this->getItem();
        $pdf    = $this->getPdf();
        $page   = $this->getPage();
        $lines  = array();

        // draw Product name
        $lines[0] = array(array(
            'text' => Mage::helper('core/string')->str_split($item->getName(), self::DEFAULT_TRIM_PRODUCT, true, true),
            'feed' => self::DEFAULT_OFFSET_PRODUCT,
        ));

        // draw SKU
        $lines[0][] = array(
            'text'  => Mage::helper('core/string')->str_split($this->getSku($item), self::DEFAULT_TRIM_SKU),
            'feed'  => self::DEFAULT_OFFSET_SKU
        );

        // draw QTY
        $lines[0][] = array(
            'text'  => $item->getQty()*1,
            'feed'  => self::DEFAULT_OFFSET_QTY
        );

        // draw Price
        $lines[0][] = array(
            'text'  => $order->formatPriceTxt($item->getPrice()),
            'feed'  => self::DEFAULT_OFFSET_PRICE,
            'font'  => 'bold',
            'align' => 'right'
        );

        // draw Tax
        $lines[0][] = array(
            'text'  => $order->formatPriceTxt($item->getTaxAmount()),
            'feed'  => self::DEFAULT_OFFSET_TAX,
            'font'  => 'bold',
            'align' => 'right'
        );

        // draw Subtotal
        $lines[0][] = array(
            'text'  => $order->formatPriceTxt($item->getRowTotal()),
            'feed'  => self::DEFAULT_OFFSET_SUBTOTAL,
            'font'  => 'bold',
            'align' => 'right'
        );

        // custom options
        $options = $this->getItemOptions();
        if ($options) {
            foreach ($options as $option) {
                // draw options label
                $lines[][] = array(
                    'text' => Mage::helper('core/string')->str_split(strip_tags($option['label']), self::DEFAULT_TRIM_PRODUCT, true, true),
                    'font' => 'italic',
                    'feed' => self::DEFAULT_OFFSET_PRODUCT
                );

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

        $lineBlock = array(
            'lines'  => $lines,
            'height' => self::DEFAULT_LINE_HEIGHT
        );

        $page = $pdf->drawLineBlocks($page, array($lineBlock), array('table_header' => true));
        $this->setPage($page);
    }
}

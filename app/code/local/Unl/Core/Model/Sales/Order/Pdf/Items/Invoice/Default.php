<?php

class Unl_Core_Model_Sales_Order_Pdf_Items_Invoice_Default extends Unl_Core_Model_Sales_Order_Pdf_Items_Abstract
{
    const DEFAULT_OFFSET_PRODUCT  = 35;
    const DEFAULT_OFFSET_SKU      = 255;
    const DEFAULT_OFFSET_QTY      = 435;
    const DEFAULT_OFFSET_PRICE    = 405;
    const DEFAULT_OFFSET_TAX      = 500;
    const DEFAULT_OFFSET_SUBTOTAL = 575;

    const DEFAULT_TRIM_PRODUCT    = 46;
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
            'feed'  => self::DEFAULT_OFFSET_QTY,
            'font'  => 'bold'
        );

        // draw Price
        $i = 0;
        $prices = $this->getItemPricesForDisplay();
        foreach ($prices as $priceData){
            if (isset($priceData['label'])) {
                // draw Price label
                $lines[$i][] = array(
                    'text'  => $priceData['label'],
                    'feed'  => self::DEFAULT_OFFSET_PRICE,
                    'align' => 'right'
                );
                // draw Subtotal label
                $lines[$i][] = array(
                    'text'  => $priceData['label'],
                    'feed'  => self::DEFAULT_OFFSET_SUBTOTAL,
                    'align' => 'right'
                );
                $i++;
            }
            // draw Price
            $lines[$i][] = array(
                'text'  => $priceData['price'],
                'feed'  => self::DEFAULT_OFFSET_PRICE,
                'font'  => 'bold',
                'align' => 'right'
            );
            // draw Subtotal
            $lines[$i][] = array(
                'text'  => $priceData['subtotal'],
                'feed'  => self::DEFAULT_OFFSET_SUBTOTAL,
                'font'  => 'bold',
                'align' => 'right'
            );
            $i++;
        }

        // draw Tax
        $lines[0][] = array(
            'text'  => $order->formatPriceTxt($item->getTaxAmount()),
            'feed'  => self::DEFAULT_OFFSET_TAX,
            'font'  => 'bold',
            'align' => 'right'
        );

        $lineBlock = array(
            'lines'  => $lines,
            'height' => self::DEFAULT_LINE_HEIGHT
        );

        $page = $pdf->drawLineBlocks($page, array($lineBlock), array('table_header' => true));
        $this->setPage($page);

        $this->drawOptions();
    }
}

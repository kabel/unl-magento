<?php

class Unl_Core_Model_Sales_Order_Pdf_Items_Creditmemo_Default extends Unl_Core_Model_Sales_Order_Pdf_Items_Abstract
{
    const DEFAULT_OFFSET_PRODUCT  = 35;
    const DEFAULT_OFFSET_SKU      = 255;
    const DEFAULT_OFFSET_TOTAL_EX = 355;
    const DEFAULT_OFFSET_DISCOUNT = 405;
    const DEFAULT_OFFSET_QTY      = 455;
    const DEFAULT_OFFSET_TAX      = 475;
    const DEFAULT_OFFSET_SUBTOTAL = 575;
    
    const DEFAULT_WIDTH_TOTAL_EX  = 50;
    const DEFAULT_WIDTH_DISCOUNT  = 50;
    const DEFAULT_WIDTH_QTY       = 30;
    const DEFAULT_WIDTH_TAX       = 45;
    
    const DEFAULT_OFFSET_PAD      = 10;
    
    const DEFAULT_TRIM_PRODUCT    = 45;
    const DEFAULT_TRIM_OPTION     = 30;
    const DEFAULT_TRIM_SKU        = 20;
    
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

        // draw Total (ex)
        $lines[0][] = array(
            'text'  => $order->formatPriceTxt($item->getRowTotal()),
            'feed'  => self::DEFAULT_OFFSET_TOTAL_EX,
            'font'  => 'bold',
            'align' => 'right',
            'width' => self::DEFAULT_WIDTH_TOTAL_EX,
        );

        // draw Discount
        $lines[0][] = array(
            'text'  => $order->formatPriceTxt(-$item->getDiscountAmount()),
            'feed'  => self::DEFAULT_OFFSET_DISCOUNT,
            'font'  => 'bold',
            'align' => 'right',
            'width' => self::DEFAULT_WIDTH_DISCOUNT,
        );

        // draw QTY
        $lines[0][] = array(
            'text'  => $item->getQty()*1,
            'feed'  => self::DEFAULT_OFFSET_QTY,
            'font'  => 'bold',
            'align' => 'center',
            'width' => self::DEFAULT_WIDTH_QTY,
        );

        // draw Tax
        $lines[0][] = array(
            'text'  => $order->formatPriceTxt($item->getTaxAmount()),
            'feed'  => self::DEFAULT_OFFSET_TAX,
            'font'  => 'bold',
            'align' => 'right',
            'width' => self::DEFAULT_WIDTH_TAX,
        );

        // draw Subtotal
        $lines[0][] = array(
            'text'  => $order->formatPriceTxt($item->getRowTotal() + $item->getTaxAmount() - $item->getDiscountAmount()),
            'feed'  => self::DEFAULT_OFFSET_SUBTOTAL,
            'font'  => 'bold',
            'align' => 'right'
        );

        // draw options
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
                $_printValue = isset($option['print_value']) ? $option['print_value'] : strip_tags($option['value']);
                $lines[][] = array(
                    'text' => Mage::helper('core/string')->str_split($_printValue, self::DEFAULT_TRIM_OPTION, true, true),
                    'feed' => self::DEFAULT_OFFSET_PRODUCT + self::DEFAULT_OFFSET_PAD
                );
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

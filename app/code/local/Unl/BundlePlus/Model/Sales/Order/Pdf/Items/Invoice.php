<?php

class Unl_BundlePlus_Model_Sales_Order_Pdf_Items_Invoice
    extends Unl_BundlePlus_Model_Sales_Order_Pdf_Items_Abstract
{
    const DEFAULT_OFFSET_PRODUCT  = 35;
    const DEFAULT_OFFSET_SKU      = 255;
    const DEFAULT_OFFSET_QTY      = 435;
    const DEFAULT_OFFSET_PRICE    = 405;
    const DEFAULT_OFFSET_TAX      = 500;
    const DEFAULT_OFFSET_SUBTOTAL = 575;

    const DEFAULT_OFFSET_PAD      = 10;

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

        $this->_setFontRegular();
        $items = $this->getChilds($item);

        $_prevOptionId = '';
        $drawItems = array();

        foreach ($items as $_item) {
            $line   = array();

            $attributes = $this->getSelectionAttributes($_item);
            if (is_array($attributes)) {
                $optionId   = $attributes['option_id'];
            }
            else {
                $optionId = 0;
            }

            if (!isset($drawItems[$optionId])) {
                $drawItems[$optionId] = array(
                    'lines'  => array(),
                    'height' => self::DEFAULT_LINE_HEIGHT
                );
            }

            if ($_item->getOrderItem()->getParentItem()) {
                if ($_prevOptionId != $attributes['option_id']) {
                    $line[0] = array(
                        'font'  => 'italic',
                        'text'  => Mage::helper('core/string')->str_split($attributes['option_label'], self::DEFAULT_TRIM_PRODUCT, true, true),
                        'feed'  => self::DEFAULT_OFFSET_PRODUCT
                    );

                    $drawItems[$optionId] = array(
                        'lines'  => array($line),
                        'height' => self::DEFAULT_LINE_HEIGHT
                    );

                    $line = array();

                    $_prevOptionId = $attributes['option_id'];
                }
            }

            if ($_item->getOrderItem()->getParentItem()) {
                $feed = self::DEFAULT_OFFSET_PRODUCT + self::DEFAULT_OFFSET_PAD;
                $name = $this->getValueHtml($_item);
            } else {
                $feed = self::DEFAULT_OFFSET_PRODUCT;
                $name = $_item->getName();
            }
            $line[] = array(
                'text'  => Mage::helper('core/string')->str_split($name, self::DEFAULT_TRIM_PRODUCT, true, true),
                'feed'  => $feed
            );

            // draw SKUs
            if (!$_item->getOrderItem()->getParentItem()) {
                $text = array();
                foreach (Mage::helper('core/string')->str_split($item->getSku(), self::DEFAULT_TRIM_SKU) as $part) {
                    $text[] = $part;
                }
                $line[] = array(
                    'text'  => $text,
                    'feed'  => self::DEFAULT_OFFSET_SKU
                );
            }

            // draw prices
            if ($this->canShowPriceInfo($_item)) {
                $price = $order->formatPriceTxt($_item->getPrice());
                $line[] = array(
                    'text'  => $price,
                    'feed'  => self::DEFAULT_OFFSET_PRICE,
                    'font'  => 'bold',
                    'align' => 'right'
                );
                $line[] = array(
                    'text'  => $_item->getQty()*1,
                    'feed'  => self::DEFAULT_OFFSET_QTY,
                    'font'  => 'bold',
                );

                $tax = $order->formatPriceTxt($_item->getTaxAmount());
                $line[] = array(
                    'text'  => $tax,
                    'feed'  => self::DEFAULT_OFFSET_TAX,
                    'font'  => 'bold',
                    'align' => 'right'
                );

                $row_total = $order->formatPriceTxt($_item->getRowTotal());
                $line[] = array(
                    'text'  => $row_total,
                    'feed'  => self::DEFAULT_OFFSET_SUBTOTAL,
                    'font'  => 'bold',
                    'align' => 'right'
                );
            }

            $drawItems[$optionId]['lines'][] = $line;
        }

        $page = $pdf->drawLineBlocks($page, $drawItems, array('table_header' => true));
        $this->setPage($page);

        $this->drawOptions();
    }
}

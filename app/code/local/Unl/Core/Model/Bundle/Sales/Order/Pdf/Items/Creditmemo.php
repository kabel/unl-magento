<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Bundle
 * @copyright   Copyright (c) 2010 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Sales Order Creditmemo Pdf default items renderer
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Unl_Core_Model_Bundle_Sales_Order_Pdf_Items_Creditmemo extends Unl_Core_Model_Bundle_Sales_Order_Pdf_Items_Abstract
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

            // draw selection attributes
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

            // draw product titles
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
                // draw Total(ex)
                $text = $order->formatPriceTxt($_item->getRowTotal());
                $line[] = array(
                    'text'  => $text,
                    'feed'  => self::DEFAULT_OFFSET_TOTAL_EX,
                    'font'  => 'bold',
                    'align' => 'right',
                    'width' => self::DEFAULT_WIDTH_TOTAL_EX
                );

                // draw Discount
                $text = $order->formatPriceTxt(-$_item->getDiscountAmount());
                $line[] = array(
                    'text'  => $text,
                    'feed'  => self::DEFAULT_OFFSET_DISCOUNT,
                    'font'  => 'bold',
                    'align' => 'right',
                    'width' => self::DEFAULT_WIDTH_DISCOUNT
                );

                // draw QTY
                $text = $_item->getQty() * 1;
                $line[] = array(
                    'text'  => $_item->getQty()*1,
                    'feed'  => self::DEFAULT_OFFSET_QTY,
                    'font'  => 'bold',
                    'align' => 'center',
                    'width' => self::DEFAULT_WIDTH_QTY
                );

                // draw Tax
                $text = $order->formatPriceTxt($_item->getTaxAmount());
                $line[] = array(
                    'text'  => $text,
                    'feed'  => self::DEFAULT_OFFSET_TAX,
                    'font'  => 'bold',
                    'align' => 'right',
                    'width' => self::DEFAULT_WIDTH_TAX
                );

                // draw Total(inc)
                $text = $order->formatPriceTxt($_item->getRowTotal()+$_item->getTaxAmount()-$_item->getDiscountAmount());
                $line[] = array(
                    'text'  => $text,
                    'feed'  => self::DEFAULT_OFFSET_SUBTOTAL,
                    'font'  => 'bold',
                    'align' => 'right',
                );
            }

            $drawItems[$optionId]['lines'][] = $line;

        }

        // custom options
        $options = $item->getOrderItem()->getProductOptions();
        if ($options) {
            if (isset($options['options'])) {
                foreach ($options['options'] as $option) {
                    $lines = array();
                    $lines[][] = array(
                        'text'  => Mage::helper('core/string')->str_split(strip_tags($option['label']), self::DEFAULT_TRIM_PRODUCT, true, true),
                        'font'  => 'italic',
                        'feed'  => self::DEFAULT_OFFSET_PRODUCT
                    );

                    if ($option['value']) {
                        $text = array();
                        $_printValue = isset($option['print_value']) ? $option['print_value'] : strip_tags($option['value']);
                        $values = explode(', ', $_printValue);
                        foreach ($values as $value) {
                            foreach (Mage::helper('core/string')->str_split($value, self::DEFAULT_TRIM_OPTION, true, true) as $_value) {
                                $text[] = $_value;
                            }
                        }

                        $lines[][] = array(
                            'text'  => $text,
                            'feed'  => self::DEFAULT_OFFSET_PRODUCT + self::DEFAULT_OFFSET_PAD
                        );
                    }

                    $drawItems[] = array(
                        'lines'  => $lines,
                        'height' => self::DEFAULT_LINE_HEIGHT
                    );
                }
            }
        }

        $page = $pdf->drawLineBlocks($page, $drawItems, array('table_header' => true));
        $this->setPage($page);
    }
}

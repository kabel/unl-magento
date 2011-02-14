<?php

class Unl_Core_Model_Sales_Order_Pdf_Creditmemo extends Unl_Core_Model_Sales_Order_Pdf_Abstract
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

    public function getPdf($creditmemos = array())
    {
        $this->_beforeGetPdf();
        $this->_initRenderer('creditmemo');

        $pdf = new Zend_Pdf();
        $this->_setPdf($pdf);

        foreach ($creditmemos as $creditmemo) {
            if ($creditmemo->getStoreId()) {
                Mage::app()->getLocale()->emulate($creditmemo->getStoreId());
            }
            $page = $pdf->newPage(Zend_Pdf_Page::SIZE_LETTER);
            $pdf->pages[] = $page;

            $order = $creditmemo->getOrder();

            /* Add image */
            $this->insertLogo($page, $creditmemo->getStore());

            /* Add address */
            $this->insertAddress($page, $creditmemo->getStore());

            /* Add head */
            $this->insertOrder($page, $order, Mage::getStoreConfigFlag(self::XML_PATH_SALES_PDF_CREDITMEMO_PUT_ORDER_ID, $order->getStoreId()));

            $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
            $this->_setFontRegular($page);
            $text = Mage::helper('sales')->__('Credit Memo # ') . $creditmemo->getIncrementId();
            $feed = self::DEFAULT_PAGE_MARGIN_RIGHT - self::DEFAULT_BOX_PAD - $this->widthForStringUsingFontSize($text, $page->getFont(), $page->getFontSize());
            $page->drawText($text, $feed, self::DEFAULT_PAGE_TOP - self::DEFAULT_LOGO_HEIGHT - self::DEFAULT_LOGO_MARGIN - self::DEFAULT_LINE_HEIGHT, 'UTF-8');

            /* Add table head */
            $page->setFillColor(new Zend_Pdf_Color_RGB(0.93, 0.92, 0.92));
            $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
            $page->setLineWidth(0.5);
            $page->drawRectangle(self::DEFAULT_PAGE_MARGIN_LEFT, $this->y, self::DEFAULT_PAGE_MARGIN_RIGHT, $this->y - self::DEFAULT_LINE_HEIGHT - self::DEFAULT_BOX_PAD);
            $this->y -= self::DEFAULT_LINE_HEIGHT;
            $page->setFillColor(new Zend_Pdf_Color_RGB(0.4, 0.4, 0.4));
            $this->_drawHeader($page);
            $this->y -= self::DEFAULT_LINE_HEIGHT + self::DEFAULT_BOX_PAD;

            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));

            /* Add body */
            foreach ($creditmemo->getAllItems() as $item){
                if ($item->getOrderItem()->getParentItem()) {
                    continue;
                }

                if ($this->y < self::DEFAULT_LINE_SHIFT) {
                    $page = $this->newPage(array('table_header' => true));
                }

                /* Draw item */
                $page = $this->_drawItem($item, $page, $order);
            }

            /* Add totals */
            $page = $this->insertTotals($page, $creditmemo);
        }

        $this->_afterGetPdf();

        if ($creditmemo->getStoreId()) {
            Mage::app()->getLocale()->revert();
        }
        return $pdf;
    }

    protected function _drawHeader(Zend_Pdf_Page $page)
    {
        $font = $page->getFont();
        $size = $page->getFontSize();

        $page->drawText(Mage::helper('sales')->__('Products'), self::DEFAULT_OFFSET_PRODUCT, $this->y, 'UTF-8');

        $page->drawText(Mage::helper('sales')->__('SKU'), self::DEFAULT_OFFSET_SKU, $this->y, 'UTF-8');

        $text = Mage::helper('sales')->__('Total (ex)');
        $page->drawText($text, $this->getAlignRight($text, self::DEFAULT_OFFSET_TOTAL_EX, self::DEFAULT_WIDTH_TOTAL_EX, $font, $size), $this->y, 'UTF-8');

        $text = Mage::helper('sales')->__('Discount');
        $page->drawText($text, $this->getAlignRight($text, self::DEFAULT_OFFSET_DISCOUNT, self::DEFAULT_WIDTH_DISCOUNT, $font, $size), $this->y, 'UTF-8');

        $text = Mage::helper('sales')->__('Qty');
        $page->drawText($text, $this->getAlignCenter($text, self::DEFAULT_OFFSET_QTY, self::DEFAULT_WIDTH_QTY, $font, $size), $this->y, 'UTF-8');

        $text = Mage::helper('sales')->__('Tax');
        $page->drawText($text, $this->getAlignRight($text, self::DEFAULT_OFFSET_TAX, self::DEFAULT_WIDTH_TAX, $font, $size), $this->y, 'UTF-8');

        $text = Mage::helper('sales')->__('Total (inc)');
        $page->drawText($text, self::DEFAULT_OFFSET_SUBTOTAL - $this->widthForStringUsingFontSize($text, $font, $size), $this->y, 'UTF-8');
    }

    /**
     * Create new page and assign to PDF object
     *
     * @param array $settings
     * @return Zend_Pdf_Page
     */
    public function newPage(array $settings = array())
    {
        $page = parent::newPage($settings);

        if (!empty($settings['table_header'])) {
            $this->_setFontRegular($page);
            $page->setFillColor(new Zend_Pdf_Color_RGB(0.93, 0.92, 0.92));
            $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
            $page->setLineWidth(0.5);
            $page->drawRectangle(self::DEFAULT_PAGE_MARGIN_LEFT, $this->y, self::DEFAULT_PAGE_MARGIN_RIGHT, $this->y - self::DEFAULT_LINE_HEIGHT - self::DEFAULT_BOX_PAD);
            $this->y -= self::DEFAULT_LINE_HEIGHT;
            $page->setFillColor(new Zend_Pdf_Color_RGB(0.4, 0.4, 0.4));
            $this->_drawHeader($page);
            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
            $this->y -= 2 * self::DEFAULT_LINE_HEIGHT;
        }

        return $page;
    }
}

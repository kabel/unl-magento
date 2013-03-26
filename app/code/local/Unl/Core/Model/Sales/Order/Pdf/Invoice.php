<?php

class Unl_Core_Model_Sales_Order_Pdf_Invoice extends Unl_Core_Model_Sales_Order_Pdf_Abstract
{
    const DEFAULT_OFFSET_PRODUCTS = 35;
    const DEFAULT_OFFSET_SKU      = 255;
    const DEFAULT_OFFSET_PRICE    = 380;
    const DEFAULT_OFFSET_QTY      = 430;
    const DEFAULT_OFFSET_TAX      = 480;
    const DEFAULT_OFFSET_SUBTOTAL = 535;

    public function getPdf($invoices = array())
    {
        $this->_beforeGetPdf();
        $this->_initRenderer('invoice');

        $pdf = new Zend_Pdf();
        $this->_setPdf($pdf);

        foreach ($invoices as $invoice) {
            if ($invoice->getStoreId()) {
                Mage::app()->getLocale()->emulate($invoice->getStoreId());
                Mage::app()->setCurrentStore($invoice->getStoreId());
            }
            $page = $pdf->newPage(Zend_Pdf_Page::SIZE_LETTER);
            $pdf->pages[] = $page;

            $order = $invoice->getOrder();

            if ($invoice->getState() == Mage_Sales_Model_Order_Invoice::STATE_PAID) {
                $page->setFillColor(new Zend_Pdf_Color_Rgb(1, 0, 0));
                $page->setLineColor(new Zend_Pdf_Color_Rgb(1, 0, 0));
                $this->_setFontItalic($page, 15);
                $text = Mage::helper('sales')->__('Paid: ') . Mage::helper('core')->formatDate($invoice->getPaidAt(), 'short', false);
                $feed = self::DEFAULT_PAGE_MARGIN_RIGHT - self::DEFAULT_BOX_PAD - $this->widthForStringUsingFontSize($text, $page->getFont(), $page->getFontSize());
                $page->setLineWidth(1.5);
                $page->drawRoundedRectangle($feed - self::DEFAULT_BOX_PAD - 3, self::DEFAULT_PAGE_TOP - 18 - 2 * self::DEFAULT_BOX_PAD, self::DEFAULT_PAGE_MARGIN_RIGHT + 3, self::DEFAULT_PAGE_TOP + 3, 5, Zend_Pdf_Page::SHAPE_DRAW_STROKE);
                $page->setLineWidth(0.5);
                $page->drawRoundedRectangle($feed - self::DEFAULT_BOX_PAD, self::DEFAULT_PAGE_TOP - 15 - 2 * self::DEFAULT_BOX_PAD, self::DEFAULT_PAGE_MARGIN_RIGHT, self::DEFAULT_PAGE_TOP, 5, Zend_Pdf_Page::SHAPE_DRAW_STROKE);
                $page->drawText($text, $feed, self::DEFAULT_PAGE_TOP - 13 - self::DEFAULT_BOX_PAD, 'UTF-8');
            } else {
                $order->setHighlightPayment(true);
            }

            /* Add image */
            $this->insertLogo($page, $invoice->getStore());

            /* Add address */
            $this->insertAddress($page, $invoice->getStore());

            /* Add head */
            $this->insertOrder($page, $order, Mage::getStoreConfigFlag(self::XML_PATH_SALES_PDF_INVOICE_PUT_ORDER_ID, $order->getStoreId()));


            $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
            $this->_setFontRegular($page);
            $text = Mage::helper('sales')->__('Invoice # ') . $invoice->getIncrementId();
            $feed = self::DEFAULT_PAGE_MARGIN_RIGHT - self::DEFAULT_BOX_PAD - $this->widthForStringUsingFontSize($text, $page->getFont(), $page->getFontSize());
            $page->drawText($text, $feed, self::DEFAULT_PAGE_TOP - self::DEFAULT_LOGO_HEIGHT - self::DEFAULT_LOGO_MARGIN - self::DEFAULT_LINE_HEIGHT, 'UTF-8');

            /* Add table */
            $page->setFillColor(new Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
            $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
            $page->setLineWidth(0.5);

            $page->drawRectangle(self::DEFAULT_PAGE_MARGIN_LEFT, $this->y, self::DEFAULT_PAGE_MARGIN_RIGHT, $this->y - self::DEFAULT_LINE_HEIGHT - self::DEFAULT_BOX_PAD);
            $this->y -= self::DEFAULT_LINE_HEIGHT;

            /* Add table head */
            $page->setFillColor(new Zend_Pdf_Color_Rgb(0.4, 0.4, 0.4));
            $this->_drawHeader($page);

            $this->y -= self::DEFAULT_LINE_HEIGHT + self::DEFAULT_BOX_PAD;

            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));

            /* Add body */
            foreach ($invoice->getAllItems() as $item){
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
            $page = $this->insertTotals($page, $invoice);

            if ($invoice->getStoreId()) {
                Mage::app()->getLocale()->revert();
            }
        }

        $this->_afterGetPdf();

        return $pdf;
    }

    protected function _drawHeader(Zend_Pdf_Page $page)
    {
        $page->drawText(Mage::helper('sales')->__('Product'), self::DEFAULT_OFFSET_PRODUCTS, $this->y, 'UTF-8');
        $page->drawText(Mage::helper('sales')->__('SKU'), self::DEFAULT_OFFSET_SKU, $this->y, 'UTF-8');
        $page->drawText(Mage::helper('sales')->__('Price'), self::DEFAULT_OFFSET_PRICE, $this->y, 'UTF-8');
        $page->drawText(Mage::helper('sales')->__('Qty'), self::DEFAULT_OFFSET_QTY, $this->y, 'UTF-8');
        $page->drawText(Mage::helper('sales')->__('Tax'), self::DEFAULT_OFFSET_TAX, $this->y, 'UTF-8');
        $page->drawText(Mage::helper('sales')->__('Subtotal'), self::DEFAULT_OFFSET_SUBTOTAL, $this->y, 'UTF-8');
    }

    /**
     * Create new page and assign to PDF object
     *
     * @param array $settings
     * @return Zend_Pdf_Page
     */
    public function newPage(array $settings = array())
    {
        /* Add new table head */
        $page = $this->_getPdf()->newPage(Zend_Pdf_Page::SIZE_LETTER);
        $this->_getPdf()->pages[] = $page;
        $this->y = self::DEFAULT_PAGE_TOP_NEW;

        if (!empty($settings['table_header'])) {
            $this->_setFontRegular($page);
            $page->setFillColor(new Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
            $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
            $page->setLineWidth(0.5);
            $page->drawRectangle(self::DEFAULT_PAGE_MARGIN_LEFT, $this->y, self::DEFAULT_PAGE_MARGIN_RIGHT, $this->y - self::DEFAULT_LINE_HEIGHT - self::DEFAULT_BOX_PAD);
            $this->y -= self::DEFAULT_LINE_HEIGHT;

            $page->setFillColor(new Zend_Pdf_Color_Rgb(0.4, 0.4, 0.4));
            $this->_drawHeader($page);

            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
            $this->y -= 2 * self::DEFAULT_LINE_HEIGHT;
        }

        return $page;
    }
}

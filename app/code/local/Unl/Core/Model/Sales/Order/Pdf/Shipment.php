<?php

class Unl_Core_Model_Sales_Order_Pdf_Shipment extends Unl_Core_Model_Sales_Order_Pdf_Abstract
{
    const DEFAULT_OFFSET_QTY     = 35;
    const DEFAULT_OFFSET_PRODUCT = 65;
    const DEFAULT_OFFSET_SKU     = 440;

    public function getPdf($shipments = array())
    {
        $this->_beforeGetPdf();
        $this->_initRenderer('shipment');

        $pdf = new Zend_Pdf();
        $this->_setPdf($pdf);

        foreach ($shipments as $shipment) {
            if ($shipment->getStoreId()) {
                Mage::app()->getLocale()->emulate($shipment->getStoreId());
            }
            $page = $pdf->newPage(Zend_Pdf_Page::SIZE_LETTER);
            $pdf->pages[] = $page;

            $order = $shipment->getOrder();

            /* Add image */
            $this->insertLogo($page, $shipment->getStore());

            /* Add address */
            $this->insertAddress($page, $shipment->getStore());

            /* Add head */
            $this->insertOrder($page, $order, Mage::getStoreConfigFlag(self::XML_PATH_SALES_PDF_SHIPMENT_PUT_ORDER_ID, $order->getStoreId()));

            $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
            $this->_setFontRegular($page);

            if ($order->getAwDeliverydateDate()) {
                $text = Mage::helper('deliverydate')->__('Delivery Date') . ': ' . Mage::helper('core')->formatDate($order->getAwDeliverydateDate(), 'medium', false);
                if ($order->getAwDeliverydateNotice()) {
                    $text .= "**";
                }
                $feed = self::DEFAULT_PAGE_MARGIN_RIGHT - self::DEFAULT_BOX_PAD - $this->widthForStringUsingFontSize($text, $page->getFont(), $page->getFontSize());

                $page->drawText($text, $feed, self::DEFAULT_PAGE_TOP - self::DEFAULT_LOGO_HEIGHT - self::DEFAULT_LOGO_MARGIN - self::DEFAULT_LINE_HEIGHT * 3, 'UTF-8');
            }

            $text = Mage::helper('sales')->__('Packingslip # ') . $shipment->getIncrementId();
            $feed = self::DEFAULT_PAGE_MARGIN_RIGHT - self::DEFAULT_BOX_PAD - $this->widthForStringUsingFontSize($text, $page->getFont(), $page->getFontSize());
            $page->drawText($text, $feed, self::DEFAULT_PAGE_TOP - self::DEFAULT_LOGO_HEIGHT - self::DEFAULT_LOGO_MARGIN - self::DEFAULT_LINE_HEIGHT, 'UTF-8');

            /* Add table */
            $page->setFillColor(new Zend_Pdf_Color_RGB(0.93, 0.92, 0.92));
            $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
            $page->setLineWidth(0.5);


            /* Add table head */
            $page->drawRectangle(self::DEFAULT_PAGE_MARGIN_LEFT, $this->y, self::DEFAULT_PAGE_MARGIN_RIGHT, $this->y - self::DEFAULT_LINE_HEIGHT - self::DEFAULT_BOX_PAD);
            $this->y -= self::DEFAULT_LINE_HEIGHT;
            $page->setFillColor(new Zend_Pdf_Color_RGB(0.4, 0.4, 0.4));
            $this->_drawHeader($page);

            $this->y -= self::DEFAULT_LINE_HEIGHT + self::DEFAULT_BOX_PAD;

            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));

            /* Add body */
            foreach ($shipment->getAllItems() as $item){
                if ($item->getOrderItem()->getParentItem()) {
                    continue;
                }

                if ($this->y < self::DEFAULT_LINE_SHIFT) {
                    $page = $this->newPage(array('table_header' => true));
                }

                /* Draw item */
                $page = $this->_drawItem($item, $page, $order);
            }
        }

        $this->_afterGetPdf();

        if ($shipment->getStoreId()) {
            Mage::app()->getLocale()->revert();
        }
        return $pdf;
    }

    protected function _drawHeader(Zend_Pdf_Page $page)
    {
        $page->drawText(Mage::helper('sales')->__('Qty'), self::DEFAULT_OFFSET_QTY, $this->y, 'UTF-8');
        $page->drawText(Mage::helper('sales')->__('Products'), self::DEFAULT_OFFSET_PRODUCT, $this->y, 'UTF-8');
        $page->drawText(Mage::helper('sales')->__('SKU'), self::DEFAULT_OFFSET_SKU, $this->y, 'UTF-8');
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

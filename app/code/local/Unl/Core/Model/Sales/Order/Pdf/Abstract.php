<?php

abstract class Unl_Core_Model_Sales_Order_Pdf_Abstract extends Mage_Sales_Model_Order_Pdf_Abstract
{
    const DEFAULT_LINE_HEIGHT = 13;
    const DEFAULT_FONT_SIZE   = 10;
    const DEFAULT_LINE_SHIFT  = 18;
    const DEFAULT_BOX_PAD     = 5;

    const FONT_SIZE_ADDRESS   = 8;
    const LINE_HEIGHT_ADDRESS = 10;

    const FONT_SIZE_CARRIER   = 9;
    const LINE_HEIGHT_CARRIER = 12;

    const DEFAULT_LOGO_WIDTH  = 100;
    const DEFAULT_LOGO_HEIGHT = 25;
    const DEFAULT_LOGO_MARGIN = 10;
    const DEFAULT_LOGO_TOP    = 775;

    const DEFAULT_PAGE_MARGIN_TOP   = 775;
    const DEFAULT_PAGE_MARGIN_LEFT  = 25;
    const DEFAULT_PAGE_MARGIN_RIGHT = 587;
    const DEFAULT_PAGE_TOP          = 770;
    const DEFAULT_PAGE_TOP_NEW      = 750;
    const DEFAULT_PAGE_LEFT         = 35;
    const DEFAULT_PAGE_COL2_MARGIN  = 280;
    const DEFAULT_PAGE_COL2         = 290;
    const DEFAULT_PAGE_COL2_TLEFT   = 375;
    const DEFAULT_PAGE_COL2_TRIGHT  = 505;

    const DEFAULT_TOTALS_OFFSET_LABEL = 475;
    const DEFAULT_TOTALS_OFFSET_VALUE = 575;

    /**
     *
     * @param $page Zend_Pdf_Page
     * @param $store
     */
    protected function insertLogo(&$page, $store = null)
    {
        $image = Mage::getStoreConfig('sales/identity/logo', $store);
        if ($image) {
            $image = Mage::getStoreConfig('system/filesystem/media', $store) . '/sales/store/logo/' . $image;
            if (is_file($image)) {
                $image = Zend_Pdf_Image::imageWithPath($image);
                $page->drawImage($image, self::DEFAULT_PAGE_MARGIN_LEFT, self::DEFAULT_PAGE_MARGIN_TOP - self::DEFAULT_LOGO_HEIGHT, self::DEFAULT_PAGE_MARGIN_LEFT + self::DEFAULT_LOGO_WIDTH, self::DEFAULT_PAGE_MARGIN_TOP);
            }
        }
        //return $page;
    }

    /**
     *
     * @param $page Zend_Pdf_Page
     * @param $store
     */
    protected function insertAddress(&$page, $store = null)
    {
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($page, self::FONT_SIZE_ADDRESS);

        $page->setLineWidth(0.5);
        $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
        $x = self::DEFAULT_PAGE_MARGIN_LEFT + self::DEFAULT_LOGO_WIDTH + self::DEFAULT_LOGO_MARGIN;
        $page->drawLine($x, self::DEFAULT_PAGE_MARGIN_TOP, $x, self::DEFAULT_PAGE_TOP - self::DEFAULT_LOGO_HEIGHT - self::DEFAULT_LOGO_MARGIN);

        $page->setLineWidth(0);
        $this->y = self::DEFAULT_PAGE_TOP;
        foreach (explode("\n", Mage::getStoreConfig('sales/identity/address', $store)) as $value){
            if ($value!=='') {
                $page->drawText(trim(strip_tags($value)), $x + self::DEFAULT_BOX_PAD, $this->y, 'UTF-8');
                $this->y -= self::LINE_HEIGHT_ADDRESS;
            }
        }
        //return $page;
    }

    /**
     *
     * @param $page Zend_Pdf_Page
     * @param $obj Mage_Sales_Model_Order|Mage_Sales_Model_Order_Shipment
     * @param $putOrderId
     */
    protected function insertOrder(&$page, $obj, $putOrderId = true)
    {
        if ($obj instanceof Mage_Sales_Model_Order) {
            $shipment = null;
            $order = $obj;
        } elseif ($obj instanceof Mage_Sales_Model_Order_Shipment) {
            $shipment = $obj;
            $order = $shipment->getOrder();
        }

        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0.5));

        $y = self::DEFAULT_PAGE_TOP - self::DEFAULT_LOGO_HEIGHT - self::DEFAULT_LOGO_MARGIN;
        $page->drawRectangle(self::DEFAULT_PAGE_MARGIN_LEFT, $y, self::DEFAULT_PAGE_MARGIN_RIGHT, $y - 3 * self::DEFAULT_LINE_HEIGHT - self::DEFAULT_BOX_PAD);

        $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
        $this->_setFontRegular($page);


        if ($putOrderId) {
            $page->drawText(Mage::helper('sales')->__('Order # ').$order->getRealOrderId(), self::DEFAULT_PAGE_LEFT, $y - 2 * self::DEFAULT_LINE_HEIGHT, 'UTF-8');
        }
        $page->drawText(Mage::helper('sales')->__('Order Date: ') . Mage::helper('core')->formatDate($order->getCreatedAtStoreDate(), 'medium', false), self::DEFAULT_PAGE_LEFT, $y - 3 * self::DEFAULT_LINE_HEIGHT, 'UTF-8');

        $y -= 3 * self::DEFAULT_LINE_HEIGHT + self::DEFAULT_BOX_PAD;
        $page->setFillColor(new Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
        $page->setLineColor(new Zend_Pdf_Color_GrayScale(0.5));
        $page->setLineWidth(0.5);
        $page->drawRectangle(self::DEFAULT_PAGE_MARGIN_LEFT, $y, self::DEFAULT_PAGE_COL2_MARGIN, $y - self::DEFAULT_LINE_HEIGHT - 4 * self::DEFAULT_BOX_PAD);
        $page->drawRectangle(self::DEFAULT_PAGE_COL2_MARGIN, $y, self::DEFAULT_PAGE_MARGIN_RIGHT, $y - self::DEFAULT_LINE_HEIGHT - 4 * self::DEFAULT_BOX_PAD);

        /* Calculate blocks info */

        /* Billing Address */
        $billingAddress = $this->_formatAddress($order->getBillingAddress()->format('pdf'));

        /* Payment */
        $paymentInfo = Mage::helper('payment')->getInfoBlock($order->getPayment())
            ->setIsSecureMode(true)
            ->toPdf();
        $payment = explode('{{pdf_row_separator}}', $paymentInfo);
        foreach ($payment as $key=>$value){
            if (strip_tags(trim($value))==''){
                unset($payment[$key]);
            }
        }
        reset($payment);

        /* Shipping Address and Method */
        if (!$order->getIsVirtual()) {
            /* Shipping Address */
            $shippingAddress = $this->_formatAddress($order->getShippingAddress()->format('pdf'));

            $shippingMethod  = $order->getShippingDescription();
        }

        $y -= self::DEFAULT_LINE_HEIGHT + self::DEFAULT_BOX_PAD;
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($page);
        $page->drawText(Mage::helper('sales')->__('SOLD TO:'), self::DEFAULT_PAGE_LEFT, $y, 'UTF-8');

        if (!$order->getIsVirtual()) {
            $page->drawText(Mage::helper('sales')->__('SHIP TO:'), self::DEFAULT_PAGE_COL2, $y , 'UTF-8');
        }
        else {
            $page->drawText(Mage::helper('sales')->__('Payment Method:'), self::DEFAULT_PAGE_COL2, $y , 'UTF-8');
        }

        if (!$order->getIsVirtual()) {
            $y2 = $y - self::DEFAULT_LINE_HEIGHT - (max(count($billingAddress), count($shippingAddress)) * self::DEFAULT_LINE_HEIGHT + self::DEFAULT_BOX_PAD);
        }
        else {
            $y2 = $y - self::DEFAULT_LINE_HEIGHT - (count($billingAddress) * self::DEFAULT_LINE_HEIGHT + self::DEFAULT_BOX_PAD);
        }

        $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));
        $page->drawRectangle(self::DEFAULT_PAGE_MARGIN_LEFT, $y - self::DEFAULT_LINE_HEIGHT, self::DEFAULT_PAGE_MARGIN_RIGHT, $y2);
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($page);
        $this->y = $y - (2 * self::DEFAULT_LINE_HEIGHT);

        foreach ($billingAddress as $value){
            if ($value!=='') {
                $page->drawText(strip_tags(ltrim($value)), self::DEFAULT_PAGE_LEFT, $this->y, 'UTF-8');
                $this->y -= self::DEFAULT_LINE_HEIGHT;
            }
        }

        if (!$order->getIsVirtual()) {
            $this->y = $y - (2 * self::DEFAULT_LINE_HEIGHT);
            foreach ($shippingAddress as $value){
                if ($value!=='') {
                    $page->drawText(strip_tags(ltrim($value)), self::DEFAULT_PAGE_COL2, $this->y, 'UTF-8');
                    $this->y -= self::DEFAULT_LINE_HEIGHT;
                }

            }

            $page->setFillColor(new Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
            $page->setLineWidth(0.5);
            $page->drawRectangle(self::DEFAULT_PAGE_MARGIN_LEFT, $this->y, self::DEFAULT_PAGE_COL2_MARGIN, $this->y - 2 * self::DEFAULT_LINE_HEIGHT - self::DEFAULT_BOX_PAD);
            $page->drawRectangle(self::DEFAULT_PAGE_COL2_MARGIN, $this->y, self::DEFAULT_PAGE_MARGIN_RIGHT, $this->y - 2 * self::DEFAULT_LINE_HEIGHT - self::DEFAULT_BOX_PAD);

            $this->y -= self::DEFAULT_LINE_HEIGHT + self::DEFAULT_BOX_PAD;
            $this->_setFontBold($page);
            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
            $page->drawText(Mage::helper('sales')->__('Payment Method'), self::DEFAULT_PAGE_LEFT, $this->y, 'UTF-8');
            $page->drawText(Mage::helper('sales')->__('Shipping Method:'), self::DEFAULT_PAGE_COL2, $this->y , 'UTF-8');

            $this->y -= self::DEFAULT_LINE_HEIGHT;
            $page->setFillColor(new Zend_Pdf_Color_GrayScale(1));

            $this->_setFontRegular($page);
            $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));

            $paymentLeft = self::DEFAULT_PAGE_LEFT;
            $yPayments   = $this->y - self::DEFAULT_LINE_HEIGHT - self::DEFAULT_BOX_PAD;
        }
        else {
            $yPayments   = $y - (2 * self::DEFAULT_LINE_HEIGHT);
            $paymentLeft = self::DEFAULT_PAGE_COL2;
        }

        foreach ($payment as $value){
            if (trim($value)!=='') {
                $page->drawText(strip_tags(trim($value)), $paymentLeft, $yPayments, 'UTF-8');
                $yPayments -= self::DEFAULT_LINE_HEIGHT;
            }
        }

        if (!$order->getIsVirtual()) {
            $this->y -= self::DEFAULT_LINE_HEIGHT + self::DEFAULT_BOX_PAD;

            $page->drawText($shippingMethod, self::DEFAULT_PAGE_COL2, $this->y, 'UTF-8');

            $yShipments = $this->y;


            $totalShippingChargesText = "(" . Mage::helper('sales')->__('Total Shipping Charges') . " " . $order->formatPriceTxt($order->getShippingAmount()) . ")";

            $page->drawText($totalShippingChargesText, self::DEFAULT_PAGE_COL2, $yShipments-self::DEFAULT_FONT_SIZE, 'UTF-8');
            $yShipments -= self::DEFAULT_LINE_HEIGHT;

            $tracks = array();
            if ($shipment) {
                $tracks = $shipment->getAllTracks();
            }
            if (count($tracks)) {
                $page->setFillColor(new Zend_Pdf_Color_Rgb(0.93, 0.92, 0.92));
                $page->setLineWidth(0.5);
                $page->drawRectangle(self::DEFAULT_PAGE_COL2, $yShipments, self::DEFAULT_PAGE_COL2_TRIGHT, $yShipments - self::DEFAULT_LINE_HEIGHT);
                $page->drawLine(self::DEFAULT_PAGE_COL2_TLEFT, $yShipments, self::DEFAULT_PAGE_COL2_TLEFT, $yShipments - self::DEFAULT_LINE_HEIGHT);

                $this->_setFontRegular($page);
                $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
                $page->drawText(Mage::helper('sales')->__('Carrier'), self::DEFAULT_PAGE_COL2 + self::DEFAULT_BOX_PAD, $yShipments - self::DEFAULT_FONT_SIZE , 'UTF-8');
//                $page->drawText(Mage::helper('sales')->__('Title'), 290, $yShipments - 7, 'UTF-8');
                $page->drawText(Mage::helper('sales')->__('Number'), self::DEFAULT_PAGE_COL2_TLEFT + self::DEFAULT_BOX_PAD, $yShipments - self::DEFAULT_FONT_SIZE, 'UTF-8');

                $yShipments -= self::DEFAULT_LINE_HEIGHT + self::DEFAULT_FONT_SIZE;
                $this->_setFontRegular($page, self::FONT_SIZE_CARRIER);
                foreach ($order->getTracksCollection() as $track) {

                    $CarrierCode = $track->getCarrierCode();
                    if ($CarrierCode!='custom')
                    {
                        $carrier = Mage::getSingleton('shipping/config')->getCarrierInstance($CarrierCode);
                        $carrierTitle = $carrier->getConfigData('title');
                    }
                    else
                    {
                        $carrierTitle = Mage::helper('sales')->__('Custom Value');
                    }

                    //$truncatedCarrierTitle = substr($carrierTitle, 0, 35) . (strlen($carrierTitle) > 35 ? '...' : '');
                    $truncatedTitle = substr($track->getTitle(), 0, 45) . (strlen($track->getTitle()) > 45 ? '...' : '');
                    //$page->drawText($truncatedCarrierTitle, 285, $yShipments , 'UTF-8');
                    $page->drawText($truncatedTitle, self::DEFAULT_PAGE_COL2 + 3 * self::DEFAULT_BOX_PAD, $yShipments , 'UTF-8');
                    $page->drawText($track->getNumber(), self::DEFAULT_PAGE_COL2_TLEFT + 3 * self::DEFAULT_BOX_PAD, $yShipments , 'UTF-8');
                    $yShipments -= self::DEFAULT_FONT_SIZE;
                }
            } else {
                $yShipments -= self::DEFAULT_FONT_SIZE;
            }

            $currentY = min($yPayments, $yShipments);

            // replacement of Shipments-Payments rectangle block
            $page->drawLine(self::DEFAULT_PAGE_MARGIN_LEFT, $this->y + self::DEFAULT_LINE_HEIGHT + self::DEFAULT_BOX_PAD, self::DEFAULT_PAGE_MARGIN_LEFT, $currentY);
            $page->drawLine(self::DEFAULT_PAGE_MARGIN_LEFT, $currentY, self::DEFAULT_PAGE_MARGIN_RIGHT, $currentY);
            $page->drawLine(self::DEFAULT_PAGE_MARGIN_RIGHT, $currentY, self::DEFAULT_PAGE_MARGIN_RIGHT, $this->y + self::DEFAULT_LINE_HEIGHT + self::DEFAULT_BOX_PAD);

            $this->y = $currentY;
            $this->y -= self::DEFAULT_LINE_HEIGHT + self::DEFAULT_BOX_PAD;
        }
    }

    protected function insertTotals($page, $source){
        $order = $source->getOrder();
        $totals = $this->_getTotalsList($source);
        $lineBlock = array(
            'lines'  => array(),
            'height' => self::DEFAULT_LINE_SHIFT
        );
        foreach ($totals as $total) {
            $total->setOrder($order)
                ->setSource($source);

            if ($total->canDisplay()) {
                foreach ($total->getTotalsForDisplay() as $totalData) {
                    $lineBlock['lines'][] = array(
                        array(
                            'text'      => $totalData['label'],
                            'feed'      => self::DEFAULT_TOTALS_OFFSET_LABEL,
                            'align'     => 'right',
                            'font_size' => $totalData['font_size'],
                            'font'      => 'bold'
                        ),
                        array(
                            'text'      => $totalData['amount'],
                            'feed'      => self::DEFAULT_TOTALS_OFFSET_VALUE,
                            'align'     => 'right',
                            'font_size' => $totalData['font_size'],
                            'font'      => 'bold'
                        ),
                    );
                }
            }
        }

        $page = $this->drawLineBlocks($page, array($lineBlock));
        return $page;
    }

    /**
     * Create new page and assign to PDF object
     *
     * @param array $settings
     * @return Zend_Pdf_Page
     */
    public function newPage(array $settings = array())
    {
        $pageSize = !empty($settings['page_size']) ? $settings['page_size'] : Zend_Pdf_Page::SIZE_LETTER;
        $page = $this->_getPdf()->newPage($pageSize);
        $this->_getPdf()->pages[] = $page;
        $this->y = self::DEFAULT_PAGE_TOP_NEW;

        return $page;
    }

    protected function _setFontRegular($object, $size = self::DEFAULT_FONT_SIZE)
    {
        $font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/LinLibertineFont/LinLibertineC_Re-2.8.0.ttf');
        $object->setFont($font, $size);
        return $font;
    }

    protected function _setFontBold($object, $size = self::DEFAULT_FONT_SIZE)
    {
        $font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/LinLibertineFont/LinLibertine_Bd-2.8.1.ttf');
        $object->setFont($font, $size);
        return $font;
    }

    protected function _setFontItalic($object, $size = self::DEFAULT_FONT_SIZE)
    {
        $font = Zend_Pdf_Font::fontWithPath(Mage::getBaseDir() . '/lib/LinLibertineFont/LinLibertine_It-2.8.2.ttf');
        $object->setFont($font, $size);
        return $font;
    }

    /**
     * Draw lines
     *
     * draw items array format:
     * lines        array;array of line blocks (required)
     * shift        int; full line height (optional)
     * height       int;line spacing (default DEFAULT_LINE_HEIGHT)
     *
     * line block has line columns array
     *
     * column array format
     * text         string|array; draw text (required)
     * feed         int; x position (required)
     * font         string; font style, optional: bold, italic, regular
     * font_file    string; path to font file (optional for use your custom font)
     * font_size    int; font size (default DEFAULT_FONT_SIZE)
     * align        string; text align (also see feed parametr), optional left, right
     * height       int;line spacing (default DEFAULT_LINE_HEIGHT)
     *
     * @param Zend_Pdf_Page $page
     * @param array $draw
     * @param array $pageSettings
     * @throws Mage_Core_Exception
     * @return Zend_Pdf_Page
     */
    public function drawLineBlocks(Zend_Pdf_Page $page, array $draw, array $pageSettings = array())
    {
        foreach ($draw as $itemsProp) {
            if (!isset($itemsProp['lines']) || !is_array($itemsProp['lines'])) {
                Mage::throwException(Mage::helper('sales')->__('Invalid draw line data. Please define "lines" array.'));
            }
            $lines  = $itemsProp['lines'];
            $height = isset($itemsProp['height']) ? $itemsProp['height'] : self::DEFAULT_LINE_HEIGHT;

            if (empty($itemsProp['shift'])) {
                $shift = 0;
                foreach ($lines as $line) {
                    $maxHeight = 0;
                    foreach ($line as $column) {
                        $lineSpacing = !empty($column['height']) ? $column['height'] : $height;
                        if (!is_array($column['text'])) {
                            $column['text'] = array($column['text']);
                        }
                        $top = 0;
                        foreach ($column['text'] as $part) {
                            $top += $lineSpacing;
                        }

                        $maxHeight = $top > $maxHeight ? $top : $maxHeight;
                    }
                    $shift += $maxHeight;
                }
                $itemsProp['shift'] = $shift;
            }

            if ($this->y - $itemsProp['shift'] < self::DEFAULT_LINE_SHIFT) {
                $page = $this->newPage($pageSettings);
            }

            foreach ($lines as $line) {
                $maxHeight = 0;
                foreach ($line as $column) {
                    $fontSize = empty($column['font_size']) ? self::DEFAULT_FONT_SIZE : $column['font_size'];
                    if (!empty($column['font_file'])) {
                        $font = Zend_Pdf_Font::fontWithPath($column['font_file']);
                        $page->setFont($font, $fontSize);
                    }
                    else {
                        $fontStyle = empty($column['font']) ? 'regular' : $column['font'];
                        switch ($fontStyle) {
                            case 'bold':
                                $font = $this->_setFontBold($page, $fontSize);
                                break;
                            case 'italic':
                                $font = $this->_setFontItalic($page, $fontSize);
                                break;
                            default:
                                $font = $this->_setFontRegular($page, $fontSize);
                                break;
                        }
                    }

                    if (!is_array($column['text'])) {
                        $column['text'] = array($column['text']);
                    }

                    $lineSpacing = !empty($column['height']) ? $column['height'] : $height;
                    $top = 0;
                    foreach ($column['text'] as $part) {
                        $feed = $column['feed'];
                        $textAlign = empty($column['align']) ? 'left' : $column['align'];
                        $width = empty($column['width']) ? 0 : $column['width'];
                        switch ($textAlign) {
                            case 'right':
                                if ($width) {
                                    $feed = $this->getAlignRight($part, $feed, $width, $font, $fontSize);
                                }
                                else {
                                    $feed = $feed - $this->widthForStringUsingFontSize($part, $font, $fontSize);
                                }
                                break;
                            case 'center':
                                if ($width) {
                                    $feed = $this->getAlignCenter($part, $feed, $width, $font, $fontSize);
                                }
                                break;
                        }
                        $page->drawText($part, $feed, $this->y-$top, 'UTF-8');
                        $top += $lineSpacing;
                    }

                    $maxHeight = $top > $maxHeight ? $top : $maxHeight;
                }
                $this->y -= $maxHeight;
            }
        }

        return $page;
    }
}

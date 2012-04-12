<?php

class Unl_Ship_Sales_Order_PackageController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Returns a UNL Package instance or collection from the id request param
     *
     * @param boolean $asCollection
     * @return boolean|Unl_Ship_Model_Shipment_Package|Unl_Ship_Model_Resource_Shipment_Package_Collection
     */
    protected function _initPkg($asCollection = false)
    {
        $id = $this->getRequest()->getParam('id');

        if (empty($id)) {
            return false;
        }

        if ($asCollection) {
            $pkg = Mage::getModel('unl_ship/shipment_package')->getCollection();
            $pkg->addFieldToFilter('package_id', array('in' => explode('|', $id)));

            return $pkg;
        }

        $pkg = Mage::getModel('unl_ship/shipment_package')->load($id);

        if (!$pkg->getId()) {
            return false;
        }

        return $pkg;
    }

    protected function _initPkgs()
    {
        $id = $this->getRequest()->getParam('shipment_id');

        if (empty($id)) {
            return $this->_initPkg(true);
        }

        $pkgs = Mage::getModel('unl_ship/shipment_package')->getCollection();
        $pkgs->addFieldToFilter('shipment_id', $id);

        return $pkgs;
    }

    protected function _initShipment()
    {
        $shipment = false;
        $shipmentId = $this->getRequest()->getParam('shipment_id');

        if ($shipmentId) {
            $shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);
        }

        return $shipment;
    }

    public function highValueAction()
    {
        $pkg = $this->_initPkg();
        if (!$pkg)  {
            $this->_redirect('*/sales_shipment/');
        }

        $img = $pkg->getInsDoc();
        if (empty($img)) {
            $this->_redirect('*/sales_shipment/');
        }

        $this->_prepareInlineResponse('highValueReport' . $pkg->getTrackingNumber() . '.html', $img, 'text/html');
    }

    public function customsAction()
    {
    $pkg = $this->_initPkg();
        if (!$pkg)  {
            $this->_redirect('*/sales_shipment/');
        }

        $img = $pkg->getIntlDoc();
        if (empty($img)) {
            $this->_redirect('*/sales_shipment/');
        }

        $this->_prepareInlineResponse('customsForms' . $pkg->getTrackingNumber() . '.pdf', $img, 'application/pdf');
    }

    public function labelAction()
    {
        $pkg = $this->_initPkg();
        if (!$pkg)  {
            $this->_redirect('*/sales_shipment/');
        }


		$imgtype = strtolower($pkg->getLabelFormat());
		$img = $pkg->getLabelImage();

		if (empty($imgtype) || empty($img))  {
			$this->_redirect('*/sales_shipment/');
		}

		//output image
		$this->_prepareInlineResponse('label' . $pkg->getTrackingNumber() . '.' . $imgtype, $img, 'image/' . $imgtype);
    }

    public function labelPdfAction()
    {
        //label size in a pts (1/72 inch) - 6" x 4"
		$label4x6landscape = '432:288:';
		$label4x6portrait = '288:432:';

		$pkgs = $this->_initPkgs();
		if (!$pkgs)  {
		    $this->_redirect('*/sales_shipment/');
		}

		//create a PDF of the label images to be printed
		$pdf = new Zend_Pdf();

		//add new page for each package id
		while ($pkg = $pkgs->fetchItem())  {
			$pdfimg = $pkg->getPdfImage();

			//if id is invalid return error
			if (empty($pdfimg))  {
				continue;
			}

			$width = $pdfimg->getPixelWidth();
			$height = $pdfimg->getPixelHeight();

			// scale the image to proper width
			$ratio = 288 / $width;
			$width = $width * $ratio;
			$height = $height * $ratio;
			$offset = 432 - $height;

			//add a new page with the label image
			$layout = $label4x6portrait;
			$pdfpage = $pdf->newPage($layout);

			//get the image into a PdfImage object
			$pdfpage->drawImage($pdfimg, 0, $offset, $width, $height + $offset);

			$pdf->pages[] = $pdfpage;

			$fileName = 'label' . $pkg->getTrackingNumber() . '.pdf';
		}

		if ($shipment = $this->_initShipment()) {
		    $fileName = 'ShippingLabel(' . $shipment->getIncrementId() . ').pdf';
		} else {
    		$pageCount = count($pdf->pages);
    		if ($pageCount > 1) {
    		    $fileName = 'ShippingLabels.pdf';
    		} elseif ($pageCount < 1) {
    		    $this->_redirect('*/sales_shipment/');
    		}
		}

		//output the PDF
		$this->_prepareInlineResponse($fileName, $pdf->render(), 'application/pdf');
    }

    protected function _prepareInlineResponse(
        $fileName,
        $content,
        $contentType = 'application/octet-stream',
        $contentLength = null)
    {
        $this->getResponse()
            ->setHeader('Pragma', 'public', true)
            ->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true)
            ->setHeader('Content-type', $contentType, true)
            ->setHeader('Content-Length', is_null($contentLength) ? strlen($content) : $contentLength)
            ->setHeader('Content-Disposition', 'inline; filename="'.$fileName.'"')
            ->setHeader('Last-Modified', date('r'))
            ->setBody($content);

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/label_ship');
    }
}

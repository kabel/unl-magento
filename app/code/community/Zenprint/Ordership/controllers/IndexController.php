<?php
/**
 * Zenprint
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zenprint.com so we can send you a copy immediately.
 *
 * @copyright  Copyright (c) 2009 ZenPrint (http://www.zenprint.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Zenprint_Ordership_IndexController extends Mage_Adminhtml_Controller_Action  {

	public function indexAction()  {
		$this->loadLayout();
        $this->_setActiveMenu('sales');

        $this->_addBreadcrumb($this->__('Sales'), $this->__('Sales'));
        $this->_addBreadcrumb($this->__('Orders'), $this->__('Orders'));
        $this->_addBreadcrumb($this->__('Order Ship'), $this->__('Order Ship'));

        $this->_addContent($this->getLayout()->createBlock('adminhtml/ordership', 'ordership'));
        $this->renderLayout();

	}

	public function queueordersAction()
	{
	    $orderIds = $this->getRequest()->getPost('order_ids');
	    if (!empty($orderIds)) {
	        $session = $this->_getSession();
	        $queue = $session->getOrdershipQueue();
	        $count = 0;
	        if (!$queue) {
	            $queue = array();
	        }
            foreach ($orderIds as $orderId) {
                $order = Mage::getModel('sales/order')->load($orderId);
                if ($order->canShip()) {
                    $count++;
                    $queue[] = $orderId;
                }
            }
            if ($count) {
                $this->_getSession()->addSuccess($this->__('%s order(s) have been queued for auto ship.', $count));
                $orderId = array_shift($queue);
                $session->setOrdershipQueue($queue);
                return $this->_redirect('*/*/', array(
                    'order_id' => $orderId
                ));
            } else {
                $this->_getSession()->addError($this->__('There are no shippable orders in the selected orders.'));
                return $this->_redirect('*/*/');
            }

        }
        $this->_redirect('*/*/');
	}

	public function clearqueueAction()
	{
	    $this->_getSession()->unsOrdershipQueue();
	    $this->_getSession()->addSuccess($this->__('Successfully cleared the auto ship queue.'));
	    $this->_redirect('*/*/');
	}

	public function nextinqueueAction()
	{
	    $session = $this->_getSession();
        $queue = $session->getOrdershipQueue();
        if (!empty($queue)) {
            $orderId = array_shift($queue);
            $session->setOrdershipQueue($queue);
            $this->_redirect('*/*/', array(
                'order_id' => $orderId
            ));
        } else {
            $this->_getSession()->addError($this->__('The auto ship queue is empty.'));
            $this->_redirect('*/*/');
        }
	}

	public function highvalueAction() {
	   $pkgid = $this->getRequest()->getParam('id');
        if(empty($pkgid))  {
            echo "Invalid package id";
            exit();
        }

        $pkg = Mage::getModel('shipping/shipment_package')->load($pkgid);
        $img = $pkg->getInsDoc();

        if (empty($img)) {
            echo "Invalid package id";
            exit();
        }

        $this->getResponse()->setBody(base64_decode($img));
	}

    public function customsAction() {
       $pkgid = $this->getRequest()->getParam('id');
        if(empty($pkgid))  {
            echo "Invalid package id";
            exit();
        }

        $pkg = Mage::getModel('shipping/shipment_package')->load($pkgid);
        $img = $pkg->getIntlDoc();

        if (empty($img)) {
            echo "Invalid package id";
            exit();
        }

        $this->getResponse()->setHeader('Content-type', 'application/pdf');
        $this->getResponse()->setBody(base64_decode($img));
    }

	/**
	 * Shows the shipping label image for the specified package.
	 */
	public function labelAction()  {
		$pkgid = $this->getRequest()->getParam('id');
		if(empty($pkgid))  {
			echo "Invalid package id";
			exit();
		}

		//get image details
		$pkg = Mage::getModel('shipping/shipment_package')->load($pkgid);
		$imgtype = strtolower($pkg->getLabelFormat());
		$img = $pkg->getLabelImage();

		if(empty($imgtype) || empty($img))  {
			echo "Invalid package id";
			exit();
		}

		//output image
		$this->getResponse()->setHeader('Content-type', $imgtype);
		$this->getResponse()->setBody(base64_decode($img));
	}

	/**
	 * Shows the shipping label PDF so it can be printed for the specified package(s).
	 */
	public function labelPdfAction()  {

		//label size in a pts (1/72 inch) - 6" x 4"
		$label4x6landscape = '432:288:';
		$label4x6portrait = '288:432:';

		$packageids = explode('|', $this->getRequest()->getParam('id'));
		if(empty($packageids))  {
			echo "Invalid package id.";
			exit();
		}

		//create a PDF of the label images to be printed
		$pdf = new Zend_Pdf();

		//add new page for each package id
		foreach ($packageids as $pkgid)  {
			//retrieve the package object
			$pkg = Mage::getModel('shipping/shipment_package')->load($pkgid);
			$imgformat = $pkg->getLabelFormat();
			$imgstr = $pkg->getLabelImage();

			//if id is invalid return error
			if(empty($imgformat) || empty($imgstr))  {
				echo "Invalid package id.";
				exit();
			}

			//get the image in the proper format
			$pngimagepath = $pkg->getLabelImageAsPng();

			//add a new page with the label image
			$layout = $label4x6portrait;
			if($pkg->getCarrier() == 'ups')  {
				//rotate the image so it fits properly and can be oriented the same as Fedex (portrait)
				if(function_exists('imagerotate'))  {
					$pngsrc = imagecreatefrompng($pngimagepath);
					$rotate = imagerotate($pngsrc, 270, 0);
					imagepng($rotate, $pngimagepath);
				}
				else  {
					$layout = $label4x6landscape;
				}
			}
			$pdfpage = $pdf->newPage($layout);

			//get the image into a PdfImage object
			$pdfimg = Zend_Pdf_Image::imageWithPath($pngimagepath);  //jpg, png, tiff images supported
			$pdfpage->drawImage($pdfimg, 0, 0, $pdfpage->getWidth(), $pdfpage->getHeight());

			$pdf->pages[] = $pdfpage;

			//clean up the pngimagepath
			unlink($pngimagepath);
		}

		//output the PDF
		$this->getResponse()->setHeader('Content-type', 'application/pdf');
		$this->getResponse()->setBody($pdf->render());
	}

	protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/ship');
    }
}
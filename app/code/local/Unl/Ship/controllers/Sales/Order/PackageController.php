<?php

class Unl_Ship_Sales_Order_PackageController extends Mage_Adminhtml_Controller_Action
{
    protected function _initOrder()
    {
        $this->_title($this->__('Sales'))->_title($this->__('Shipments'));
        $id = $this->getRequest()->getParam('order_id');
        $order = Mage::getModel('sales/order')->load($id);

        if (!$order->getId()) {
            return false;
        }
        Mage::register('sales_order', $order);
        Mage::register('current_order', $order);

        $shipment = Mage::getModel('sales/service_order', $order)->prepareShipment();
        Mage::register('current_shipment', $shipment);

        return $order;
    }

    public function indexAction()
    {
        if ($order = $this->_initOrder()) {
            if (!$order->canShip()) {
                if (!Mage::helper('unl_ship')->isUnlShipQueueEmpty()) {
                    $this->_getSession()->addError($this->__('Could not do shipment for order #%s. Skipped.', $order->getRealOrderId()));
                    $this->_forward('nextInQueue');
                } else {
                    $this->_getSession()->addError($this->__('Cannot do shipment for this order.'));
                    return $this->_redirect('adminhtml/sales_order/view', array('order_id'=>$this->getRequest()->getParam('order_id')));
                }
            }

            $this->_title($this->__('Auto Ship'));

            if (!Mage::helper('unl_ship')->isOrderSupportAutoShip($order)) {
                $this->_getSession()->addError(Mage::helper('unl_ship')->__('This order cannot be processed because the shipping carrier does not support online label generation.'));
            }

            $this->loadLayout();
            $this->_setActiveMenu('sales/order')
                ->renderLayout();
        } else {
            $this->_redirect('adminhtml/sales_order/');
        }
    }

    public function createAction()
    {
        if (($order = $this->_initOrder()) && $this->getRequest()->getParam('isAjax')) {
            $resp = Mage::getSingleton('unl_ship/shipment_package_create')->createShipment($order, $this->getRequest()->getPost());
            $this->getResponse()->setBody($resp);
        } else {
            $this->_forward('noRoute');
        }
    }

    public function queueOrdersAction()
    {
        $orderIds = $this->getRequest()->getPost('order_ids');
	    if (!empty($orderIds)) {
	        $helper = Mage::helper('unl_ship');
	        $queue = $helper->getUnlShipQueue();
	        $count = 0;
	        if (!$queue) {
	            $queue = array();
	        }

	        $collection = Mage::getModel('sales/order')->getResourceCollection();
	        $collection->addFieldToFilter('entity_id', array('in' => $orderIds));
            foreach ($collection as $order) {
                if ($order->canShip()) {
                    $count++;
                    $queue[] = $order->getId();
                }
            }
            if ($count) {
                $this->_getSession()->addSuccess($this->__('%s order(s) have been queued for auto ship.', $count));
                $orderId = array_shift($queue);
                $helper->setUnlShipQueue($queue);
                return $this->_redirect('*/*/', array(
                    'order_id' => $orderId
                ));
            } else {
                $this->_getSession()->addError($this->__('There are no shippable orders in the selected orders.'));
                return $this->_redirect('adminhtml/sales_order/');
            }

        }

        $this->_redirect('adminhtml/sales_order/');
    }

    public function nextInQueueAction()
    {
        $helper = Mage::helper('unl_ship');
        if (!$helper->isUnlShipQueueEmpty()) {
            $orderId = $helper->dequeueUnlShipQueue();
            $this->_redirect('*/*/', array(
                'order_id' => $orderId
            ));
        } else {
            $this->_getSession()->addError($this->__('The auto ship queue is empty.'));
            $this->_redirect('adminhtml/sales_order/');
        }
    }

    public function clearQueueAction()
    {
        Mage::helper('unl_ship')->getUnlShipQueue(true);
	    $this->_getSession()->addSuccess($this->__('Successfully cleared the auto ship queue.'));
	    $this->_redirect('adminhtml/sales_order/');
    }

    public function highValueAction()
    {
        $pkgid = $this->getRequest()->getParam('id');
        if(empty($pkgid))  {
            $this->_forward('noRoute');
        }

        $pkg = Mage::getModel('unl_ship/shipment_package')->load($pkgid);
        $img = $pkg->getInsDoc();

        if (empty($img)) {
            $this->_forward('noRoute');
        }

        $this->getResponse()->setBody(base64_decode($img));
    }

    public function customsAction()
    {
        $pkgid = $this->getRequest()->getParam('id');
        if(empty($pkgid))  {
            $this->_forward('noRoute');
        }

        $pkg = Mage::getModel('unl_ship/shipment_package')->load($pkgid);
        $img = $pkg->getIntlDoc();

        if (empty($img)) {
            $this->_forward('noRoute');
        }

        $this->getResponse()->setHeader('Content-type', 'application/pdf');
        $this->getResponse()->setBody(base64_decode($img));
    }

    public function labelAction()
    {
        $pkgid = $this->getRequest()->getParam('id');
		if(empty($pkgid))  {
			echo "Invalid package id";
			exit();
		}

		//get image details
		$pkg = Mage::getModel('unl_ship/shipment_package')->load($pkgid);
		$imgtype = strtolower($pkg->getLabelFormat());
		$img = $pkg->getLabelImage();

		if(empty($imgtype) || empty($img))  {
			echo "Invalid package id";
			exit();
		}

		//output image
		$this->getResponse()->setHeader('Content-type', 'image/'.$imgtype);
		$this->getResponse()->setBody(base64_decode($img));
    }

    public function labelPdfAction()
    {
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
			$pkg = Mage::getModel('unl_ship/shipment_package')->load($pkgid);
			$imgformat = strtolower($pkg->getLabelFormat());
			$imgstr = $pkg->getLabelImage();

			//if id is invalid return error
			if(empty($imgformat) || empty($imgstr))  {
				echo "Invalid package id.";
				exit();
			}


			//add a new page with the label image
			$layout = $label4x6portrait;
			$pdfpage = $pdf->newPage($layout);

			//get the image into a PdfImage object
			$pngimagepath = $pkg->getLabelImagePngPath();
			$pdfimg = new Zend_Pdf_Resource_Image_Png($pngimagepath);
			$pdfpage->drawImage($pdfimg, 0, 0, $pdfpage->getWidth(), $pdfpage->getHeight());

			$pdf->pages[] = $pdfpage;
		}

		//output the PDF
		$this->getResponse()->setHeader('Content-type', 'application/pdf');
		$this->getResponse()->setBody($pdf->render());
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/label_ship');
    }
}

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
 
class Zenprint_Xajax_Model_Ordership extends Zenprint_Xajax_Model_Handler 
{
	public function __construct($path="/xjx/ordership")    
    {
        parent::__construct($path);
        $this->registerFunctions();
    }

    /**
     * Generates the HTML to be added to the message block on the page.
     *
     * @param string $message
     * @param string $type Values include 'success', 'error', 'warning'
     * @return string The HTML for the message block
     */
    protected function createMessage($message, $type='error')  {
    	switch ($type)  {
    		case 'success':
    			$type = 'success-msg';
    			break;
    		case 'warning':
    			$type = 'warning-msg';
    			break;
    		case 'error':
    		default:
    			$type = 'error-msg';
    			break;
    	}
    	
    	$html = '
			<ul class="messages">
				<li class="'.$type.'">
					<ul>
						<li>'.$message.'</li>
					</ul>
				</li>
			</ul>';    	
    	
    	return $html;
    }
    
    /**
     * Retrieve the containers for the specified carrier
     *
     * @param string $carrier Lower-case carrier name
     * @param boolean $optionshtml If true, the containers will be returned as select options
     * @return array
     */
    protected function getContainers($store, $carrier='ups', $optionshtml=true)  {
    	$carrierobj = Mage::getModel('usa/shipping_carrier_'.$carrier);

    	switch ($carrier)  {
    		case 'fedex':
    			$containers = $carrierobj->getCode('packaging', '', true);
    			$defaultcontainer = $store->getConfig('carriers/fedex/packaging');
    			break;
    			
    		case 'ups':
    		default:
    			$containers = $carrierobj->getCode('package_type');	
    			$defaultcontainer = $store->getConfig('carriers/ups/container');
    			break;
    	}
    	    	
    	if(!$optionshtml)  {
    		return $containers;
    	}
    	
    	//get the 
    	
    	//create options html
    	$retval = '';
    	foreach ($containers as $key => $cont)  {
    		$retval .= '<option id="package_container_'.$key.'" value="'.$key.'" name="package_container_'.$key.'"';
    		//if default container
    		if($defaultcontainer == $key)  {
    			$retval .= ' selected';
    		}
    		$retval .='>'.$cont.'</option>';
    	}
    	
    	return $retval;
    }
    
	/**
	 * Ajax function that will create a new order shipment.
	 *
	 * @param int $orderid 
	 * @param array $packages An array of info on what packages should be created for the order.
	 *	Has the following contents:
	 * 		$packages = array(
	 * 			'0' => array(
	 * 					'0' => 1,  //package number
	 * 					'1' => 'tshirt_yellow_large:2,1237-567:1,6700009:1',  //serialized list of item SKUs and they quantity that should be in pkg
	 * 					'2' => '3.4',  //weight of package
	 * 					'3' => '02',  //container code
	 * 				),
	 * 			... 
	 * 		);
	 * @return unknown
	 */
	public function axCreateShipment($orderid, $packages)  {
		if(empty($orderid))  {
			$this->_xresponse->assign('messages', 'innerHTML', $this->createMessage(Mage::helper('ordership')->__('Error creating shipment: Invalid Order ID.')));
			$this->_xresponse->script('jumpToTop();');
			return $this->_xresponse;	
		}
		
		$order = Mage::getModel('sales/order')->loadByIncrementId($orderid);
		if($order->getEntityId() == null)  {
			$this->_xresponse->assign('messages', 'innerHTML', $this->createMessage(Mage::helper('ordership')->__('Error creating shipment: Invalid Order ID.')));
			$this->_xresponse->script('jumpToTop();');
			return $this->_xresponse;	
		}
		
		//make sure the order can be shipped
		if(!$order->canShip())  {
			$this->_xresponse->assign('messages', 'innerHTML', $this->createMessage(Mage::helper('ordership')->__('Error creating shipment: Order cannot be shipped.')));
			$this->_xresponse->script('jumpToTop();');
			return $this->_xresponse;
		}
		
		if(empty($packages))  {
			$this->_xresponse->assign('messages', 'innerHTML', $this->createMessage(Mage::helper('ordership')->__('Error creating shipment: Invalid package data.')));
			$this->_xresponse->script('jumpToTop();');
			return $this->_xresponse;	
		}
		
		//setup the package objects
		$objpackages = Array();
		foreach ($packages as $pkg)  {
			//unserialize the items in the package
			$items = array();
			$its = explode(',', $pkg[1]);
			foreach ($its as $it)  {
				$det = explode(':', $it);
				//id => quantity
				$items[$det[0]] = $det[1]; 
			}
			
			//make sure all items in package are shippable
			foreach ($items as $id=>$qty)  {
				$objitem = Mage::getModel('sales/order_item')->load($id);
				if(!$objitem->canShip())  {
					$this->_xresponse->assign('messages', 'innerHTML', $this->createMessage(Mage::helper('ordership')->__('Error creating shipment: Unshippable package.')));
					$this->_xresponse->script('jumpToTop();');
					return $this->_xresponse;
				}
			}
			
			//create package object and all needed attributes
			$p = Mage::getModel('shipping/shipment_package')
				->setPackageNumber($pkg[0])
				->setItems($items)
				->setWeight($pkg[2])
				->setDescription('Package '.$pkg[0].' for order id '.$orderid)  //TODO: add to frontend for pkg
				->setContainerCode($pkg[3])
				->setContainerDescription('')  //TODO:  verify correct code by retrieving description
				//FIXME: The unit codes need to be read from the admin config
				->setWeightUnitCode('LBS')  //TODO: add to frontend for pkg
				->setDimensionUnitCode('IN')
				->setHeight($pkg[4])
				->setWidth($pkg[5])
				->setLength($pkg[6])
				->setConfirmationType('')  //TODO: add to frontend for pkg
				->setConfirmationNumber('')  //TODO: not sure what this is used for
				->setInsuranceCode('')  //TODO: add to frontend for pkg
				->setInsuranceCurrencyCode('')  //TODO: add to frontend for pkg
				->setInsuranceValue('')  //TODO: add to frontend for pkg
				->setReleaseWithoutSignature('')  //TODO: add to frontend for pkg
				->setVerballyConfirm(false)  //TODO: add to frontend for pkg
				->setReleaseWithoutSignature(false);  //TODO: add to frontend for pkg, only available in US and Puerto Rico
			
			$objpackages[] = $p;
		}
		
		//create the shipment
		$ship = explode('_', $order->getShippingMethod());
		$carrier = Mage::getModel('usa/shipping_carrier_'.strtolower($ship[0]));
		
		if(!in_array('createShipment', get_class_methods($carrier)))  {
			$this->_xresponse->assign('messages', 'innerHTML', $this->createMessage(Mage::helper('ordership')->__('Error creating shipment: Shipping carrier not supported here.')));
			$this->_xresponse->script('jumpToTop();');
			return $this->_xresponse;
		}
		
		$request = Mage::getModel('shipping/shipment_request')
			->setOrderId($orderid)
			->setPackages($objpackages);

		try {
			$results = $carrier->createShipment($request);
		}
		catch (Exception $e)  {
			$this->_xresponse->assign('messages', 'innerHTML', $this->createMessage($e->getMessage()));
			return $this->_xresponse;
		}
				
		//make sure results aren't empty
		if(empty($results))  {
			$this->_xresponse->assign('messages', 'innerHTML', $this->createMessage(Mage::helper('ordership')->__('Unknown error encountered.')));
			$this->_xresponse->script('jumpToTop();');
			return $this->_xresponse;
		}

		//parse the results
		$i = 1;
		$tracks = array();
		$packageids = '';
		foreach ($results as $res)  {
			$tracks[] = $res->getTrackingNumber();
			$this->_xresponse->assign('package_tracking_'.$i, 'innerHTML', $res->getTrackingNumber());
					
			//set the image for the label
			$this->_xresponse->assign('package_label_'.$i, 'innerHTML', '<a target="shipping_label" rel="lightbox" title="Shipping Label For Package '.$i.'" href="'.Mage::getBaseUrl().'ordership/index/label/id/'.$res->getPackageId().'">shipping label</a>');
			$this->_xresponse->assign('package_print_'.$i, 'innerHTML', '<a target="shipping_label_print" href="'.Mage::getBaseUrl().'ordership/index/labelpdf/id/'.$res->getPackageId().'">print</a>');
			
			//set the package id
			$packageids .= '|'.$res->getPackageId();
			$i++;
		}
		$packageids = ltrim($packageids, '|');
		
		//set the package ids
		$this->_xresponse->assign('package_ids', 'value', $packageids);
		
		//notify of success
		if(sizeof($tracks) > 1)  {
			$tracksmsg = sizeof($tracks).Mage::helper('usa')->__(' packages created with tracking numbers: ').implode(', ', $tracks);
		}
		else  {
			$tracksmsg = Mage::helper('usa')->__('1 package created with tracking number: ').$tracks[0];
		}
		$this->_xresponse->assign('messages', 'innerHTML', $this->createMessage($tracksmsg, 'success'));
		$this->_xresponse->script('jumpToTop();');
		$pkgscript = "$('create_shipment_top').style.display='none';$('print_labels').style.display='block';";
		$this->_xresponse->script($pkgscript);
		
		return $this->_xresponse;
	}
	    
	public function axGetOrderShipDetails($orderid)  {
		$order = Mage::getModel('sales/order')->loadByIncrementId($orderid);
		if(empty($orderid))  {
			$this->_xresponse->assign('messages', 'innerHTML', $this->createMessage(Mage::helper('ordership')->__('Please enter a valid Order ID.')));
			$this->_xresponse->script('jumpToTop();');
			return $this->_xresponse;	
		}
		if($order->getEntityId() == null)  {
			$this->_xresponse->assign('messages', 'innerHTML', $this->createMessage(Mage::helper('ordership')->__('Order could not be retrieved for that ID.')));
			$this->_xresponse->script('jumpToTop();');
			return $this->_xresponse;	
		}		
		
		$carrier = $order->getShippingCarrier();
		if(empty($carrier))  {
			$this->_xresponse->assign('messages', 'innerHTML', $this->createMessage(Mage::helper('ordership')->__('Order \''.$orderid.'\' cannot be processed here because it has no shipping carrier defined. ')));
			$this->_xresponse->script('jumpToTop();');
			return $this->_xresponse;
		}
		//make sure the carrier can create a shipment automatically
		if(!in_array('createShipment', get_class_methods($carrier)))  {
			$this->_xresponse->assign('messages', 'innerHTML', $this->createMessage(Mage::helper('ordership')->__('Order \''.$orderid.'\' cannot be processed here because the shipping carrier does not allow automatic shipment creation. ')));
			$this->_xresponse->script('jumpToTop();');
			return $this->_xresponse;
		}	
		
		$shipaddress = $order->getShippingAddress();
		
		//set the package dimensions based on the order's store settings and default units
		if($carrier->getDimensionUnits() == 'IN')  {  //inches
			$this->_xresponse->assign('package_dimensions', 'value', json_encode($carrier->getCode('package_dimensions_in')));
		}
		else  {
			$this->_xresponse->assign('package_dimensions', 'value', json_encode($carrier->getCode('package_dimensions_cm')));
		}
		
		//assign the elements to set
		$this->_xresponse->assign('curr_order_id', 'value', $orderid);
		$this->_xresponse->assign('order_status', 'innerHTML', $order->getStatusLabel());
		$this->_xresponse->assign('order_placed', 'innerHTML', $order->getCreatedAt());
		
		$this->_xresponse->assign('ship_from_name', 'innerHTML', $order->getStore()->getWebsite()->getName());
		$this->_xresponse->assign('ship_from_addr1', 'innerHTML', Mage::getStoreConfig('shipping/origin/address1'));
		$this->_xresponse->assign('ship_from_addr2', 'innerHTML', Mage::getStoreConfig('shipping/origin/address2'));
		$this->_xresponse->assign('ship_from_addr3', 'innerHTML', Mage::getStoreConfig('shipping/origin/address3'));
		$this->_xresponse->assign('ship_from_city', 'innerHTML', Mage::getStoreConfig('shipping/origin/city'));
		$this->_xresponse->assign('ship_from_region', 'innerHTML', Mage::getModel('directory/region')->load(Mage::getStoreConfig('shipping/origin/region_id'))->getDefaultName());
		$this->_xresponse->assign('ship_from_postal_code', 'innerHTML', Mage::getStoreConfig('shipping/origin/postcode'));
		$this->_xresponse->assign('ship_from_country', 'innerHTML', Mage::getStoreConfig('shipping/origin/country_id'));
		
		$this->_xresponse->assign('ship_to_name', 'innerHTML', $shipaddress->getName());
		$this->_xresponse->assign('ship_to_addr1', 'innerHTML', $shipaddress->getStreet(1));
		$this->_xresponse->assign('ship_to_addr2', 'innerHTML', $shipaddress->getStreet(2));
		$this->_xresponse->assign('ship_to_addr3', 'innerHTML', $shipaddress->getStreet(3));
		$this->_xresponse->assign('ship_to_city', 'innerHTML', $shipaddress->getCity());
		$this->_xresponse->assign('ship_to_region', 'innerHTML', $shipaddress->getRegionCode());
		$this->_xresponse->assign('ship_to_postal_code', 'innerHTML', $shipaddress->getPostcode());
		$this->_xresponse->assign('ship_to_country', 'innerHTML', $shipaddress->getCountryId());
		
		$ship = explode('_', $order->getShippingMethod());
		$shipmethod = $carrier->getCode('method', $ship[1]);
		if(empty($shipmethod))  {
			$shipmethod = $carrier->getCode('code_method', $ship[1]);
		}
		$this->_xresponse->assign('shipping_carrier', 'innerHTML', strtoupper($ship[0])); 
		$this->_xresponse->assign('shipping_method', 'innerHTML', $shipmethod);

		//get the code for the shipping items
		$i = 1;
		$pkgweight = 0;
		$items = '
						<table width="100%">
        					<tr style="font-weight: bold;">
        						<td>Package</td>
        						<td>SKU</td>
        						<td>Item</td>
        						<td>Weight</td>
        						<td align="center">Qty Invoiced</td>
        						<td align="center">Qty Shipped</td>
        						<td align="center">Quantity to Ship</td>
        					</tr>';
		foreach ($order->getAllVisibleItems() as $item)  {
			//TODO:  Check if the item has already shipped...if so indicate it here
			$qty = intval($item->getQtyInvoiced());
			$qtyshipped = intval($item->getQtyShipped());
			$qty2ship = intval($item->getQtyToShip());
			$wght = '0.0';
			$readonly = ' readonly';
			if($item->canShip())  {
				$wght = sprintf("%01.1f", round($item->getWeight(), 1));
				$readonly = '';
			}
			$items .= '
							<tr>	
								<input type="hidden" id="item_id_'.$i.'" value="'.$item->getId().'">
        						<td>';
			if($item->canShip())  {
				$items .= '
        							<select id="item_package_'.$i.'" name="item_package_'.$i.'" style="width: 50px;" onChange="calculatePackages();">
        								<option id="item_package_option_1" value="1">1</option>
        								<option id="item_package_option_2" value="2">2</option>
        							</select>';
			}
			$items .= '
        						</td>
        						<td id="item_sku_'.$i.'">'.$item->getSku().'</td>
        						<td id="item_description_'.$i.'">'.$item->getName().'</td>
        						<td>';
			if($item->canShip())  {
				$items .= '        						
	    							<span class="input-ele"><input class="input-text" id="item_weight_'.$i.'" name="item_weight_'.$i.'" style="width: 45px;" value="'.$wght.'" onBlur="calculatePackages();"'.$readonly.'/></span>';
			}
			$items .= '
								</td>
        						<td align="center" id="item_quantity_invoiced_'.$i.'">'.$qty.'</td>
        						<td align="center" id="item_quantity_shipped_'.$i.'">'.$qtyshipped.'</td>
        						<td align="center">';
			if($item->canShip())  {
				$items .= '
									<span class="input-ele"><input class="input-text" id="item_quantity_ship_'.$i.'" name="item_quantity_ship_'.$i.'" onkeyup="checkQuantityShip('.$i.');" onBlur="calculatePackages();" style="width: 45px;" value="'.$qty2ship.'"/></span>';
			}
			$items .= '
								</td>
        					</tr>';
			$pkgweight = $pkgweight + ($wght * $qty2ship);
			$i++;
		}
		$items .= '
						</table>';
		$this->_xresponse->assign('item_rows', 'innerHTML', $items);
		$this->_xresponse->assign('package_number_items_1', 'innerHTML', $i-1);  //add # items to 1st package
		$this->_xresponse->assign('package_weight_1', 'value', sprintf("%01.1f", $pkgweight));  //add total weight to 1st package
		$this->_xresponse->assign('number_of_items', 'value', $i-1);  //update number of items
		
		//set the containers for the carrier
		$containers = $this->getContainers($order->getStore(), strtolower($ship[0]), false);
		$this->_xresponse->assign('package_containers', 'value', json_encode($containers));
		$this->_xresponse->script('buildContainers(1);buildContainers(2);');
		
		//set the dimension units
		$this->_xresponse->assign('weight_header', 'innerHTML', Mage::helper('ordership')->__('Weight ').'('.$carrier->getWeightUnits().')');
		$this->_xresponse->assign('height_header', 'innerHTML', Mage::helper('ordership')->__('Height ').'('.$carrier->getDimensionUnits().')');
		$this->_xresponse->assign('width_header', 'innerHTML', Mage::helper('ordership')->__('Width ').'('.$carrier->getDimensionUnits().')');
		$this->_xresponse->assign('length_header', 'innerHTML', Mage::helper('ordership')->__('Length ').'('.$carrier->getDimensionUnits().')');
		
		//set the default package dimensions
		$this->_xresponse->script('setPackageDimensions(1);setPackageDimensions(2);');
		
		//display all the order details and buttons
		$pkgscript = "$('order_details').style.display='block';";
		//if there are items to ship, show the shipment button
		//DO not display if the order's status is not one that can ship (includes 'complete')
		if($order->canShip() && $order->canHold())  {
			$pkgscript .= "$('create_shipment_top').style.display='block';";
		}
		else  {
			$this->_xresponse->assign('messages', 'innerHTML', $this->createMessage(Mage::helper('usa')->__('Order \''.$orderid.'\' cannot be processed here because it is not in a shippable state.')));
			$this->_xresponse->script('jumpToTop();');
		}
		$this->_xresponse->script($pkgscript);
		
		return $this->_xresponse;
	}
	
}
?>

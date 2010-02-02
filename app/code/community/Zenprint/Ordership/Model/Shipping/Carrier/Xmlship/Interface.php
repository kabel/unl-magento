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
 
/**
 * Interface to be used by any carriers that offer shipping through a web service. Describes the functions that should
 * 	be implemented to make that universal.
 *
 */
interface Zenprint_Ordership_Model_Shipping_Carrier_Xmlship_Interface  {
	
	/**
     * Creates a shipment to be sent. Should initialize the shipment, retrieve tracking number, and get shipping label.
     * 
     * @return array An array of Mage_Shipping_Model_Shipment_Package objects. An exception should be thrown on error.
     */
    public function createShipment(Zenprint_Ordership_Model_Shipment_Request $request);
    
	public function getCode($type, $code='');
	
}

?>
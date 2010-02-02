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
 
class Zenprint_Ordership_Model_Shipment_Package extends Mage_Core_Model_Abstract  {
	
	protected function _construct()
	{
		$this->_init('shipping/shipment_package');
	}

	/**
	 * Retrieves the totals for all items in the package
	 *
	 * @return float
	 */
	public function getInvoiceTotal()  {
		$retval = 0.0;
		foreach ($this->getItems() as $id => $qty)  {
			$item = Mage::getModel('sales/order_item')->load($id);			
			$retval += $item->getPrice() * $qty;
		}
		
		return $retval;
	}
	
	/**
	 * Retrieves the label image as a PNG if possible
	 *
	 * @return string A reference to the filepath where the image is stored. False is returned if the image cannot be converted.
	 */
	public function getLabelImageAsPng($storepath=null)  {
		if(empty($storepath))  {
			$storepath = Mage::getConfig()->getVarDir().'/'.$this->getId().'_'.time();
		}
		$rawformat = strtolower($this->getLabelFormat());
		
		//determine if the image can be converted
		switch ($rawformat)  {
			case 'gif':
			case 'png':
				break;
			default:
				return false;
				break;
		}
		
		//store the raw file out so that it can be converted
		$rawpath = $storepath.'.'.$rawformat;
		if(file_put_contents($rawpath, base64_decode($this->getLabelImage())) === false)  {
			return false;
		}
		
		//if already a png, return it
		if($rawformat == 'png')  {
			return $rawpath;
		}
		
		//create the image object
		$zenimage = new Zenprint_Image($rawpath);

		//convert the raw file to PNG
		$newpath = $zenimage->saveAsFiletype(IMAGETYPE_PNG);
		
		//remove the raw image
		unlink($rawpath);
		
		return $newpath;		
	}
}
?>
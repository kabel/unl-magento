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
 
class Zenprint_Ordership_Model_Shipment_Confirmation extends Varien_Object 
{
	protected $_xml = null;
	protected $_rawresponse = null;
	protected $_errormsg = null;
	protected $_errorcode = null;
	
	public function setRawResponse($xmlResponse, $parse=true)  {
		$this->_rawresponse = $xmlResponse;
		
		//load the XML object
		if($parse)  {
			return $this->parseXml();
		}
		
		return true;
	}
	
	public function getRawResponse()  {
		return $this->_rawresponse;
	}
	
	public function getError()  {
		return $this->_errormsg;
	}
	
	public function getErrorCode()  {
		return $this->_errorcode;
	}
	
	public function setError($errmsg, $errcode=null, $throwexception=true)  {
		$this->_errormsg = $errmsg;
		$this->_errorcode = $errcode;
		
		if($throwexception)  {
			if(!empty($errcode))  {
				throw Mage::exception('Mage_Shipping', Mage::helper('ordership')->__('Shipment Confirmation Request Failed:  ('.$errcode.') '.$errmsg), (string)$errcode);
			}
			else  {
				throw Mage::exception('Mage_Shipping', Mage::helper('ordership')->__('Shipment Confirmation Request Failed:  '.$errmsg));
			}
		}
	}
	
	/**
	 * Parses the raw XML into a simple XML object.
	 *
	 * @return boolean True on success, otherwise false.
	 */
	protected function parseXml()  {
		$this->_xml = new Varien_Simplexml_Config();
		
		if($this->_xml->loadString($this->_rawresponse) === false)  {
			$this->_xml = null;
			
			//set error throw an exception
			$errmsg = Mage::helper('ordership')->__('Response was not a valid XML string.');
			$this->setError($errmsg);
			
			return false;
		}
		
		return true;
	}
	
	/**
	 * Retrieves the XML text from the specified SimpleXML path
	 *
	 * @param string $path The path to retrieve.
	 * @return string The value of the specified path. Null if empty or not found.
	 */
	public function getValueForXpath($path)  {
		if(empty($path) || empty($this->_xml))  {
			return null;
		}
		
		$path = '//'.ltrim(rtrim($path, '/').'/text()', '/');
		$vals = $this->_xml->getXpath($path);
		
		if($vals === false)  {
			return null;
		}
		
		return $vals[0][0];
	}
	
	public function getXpath($path)  {
		return $this->_xml->getXpath($path);
	}
	
}
?>
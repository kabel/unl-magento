<?php

class Unl_Ship_Model_Shipment_Confirmation extends Varien_Object
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
				throw Mage::exception('Mage_Shipping', Mage::helper('unl_ship')->__('Shipment Confirmation Request Failed:  ('.$errcode.') '.$errmsg), (string)$errcode);
			}
			else  {
				throw Mage::exception('Mage_Shipping', Mage::helper('unl_ship')->__('Shipment Confirmation Request Failed:  '.$errmsg));
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
			$errmsg = Mage::helper('unl_ship')->__('Response was not a valid XML string.');
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

		return (string)$vals[0];
	}

	public function getXpath($path)  {
		return $this->_xml->getXpath($path);
	}

}

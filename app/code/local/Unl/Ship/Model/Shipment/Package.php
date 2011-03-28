<?php

class Unl_Ship_Model_Shipment_Package extends Mage_Core_Model_Abstract
{

	protected function _construct()
	{
		$this->_init('unl_ship/shipment_package');
	}

	/**
	 * Retrieves the label image as a PNG data URI
	 *
	 * @return string
	 */
	public function getLabelImagePngPath()
	{
	    $rawformat = strtolower($this->getLabelFormat());
	    $imgstr = $this->getLabelImage();

	    if ($rawformat == 'gif') {
	        $pngimagepath = "data://image/gif;base64,{$imgstr}";
		    $handler = imagecreatefromgif($pngimagepath);
		    imageinterlace($handler, false);
		    //rotate the image so it fits properly and can be oriented the same as Fedex (portrait)
		    if($this->getCarrier() == 'ups')  {
		        $handler = imagerotate($handler, 270, 0);
		    }
		    ob_start();
		    imagepng($handler);
		    imagedestroy($handler);
		    $imgstr = base64_encode(ob_get_clean());
	    }

	    //get the image in the proper format
		$pngimagepath = "data://image/png;base64,{$imgstr}";

		return $pngimagepath;
	}
}

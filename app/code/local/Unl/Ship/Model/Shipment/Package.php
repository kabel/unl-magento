<?php

class Unl_Ship_Model_Shipment_Package extends Mage_Core_Model_Abstract
{

	protected function _construct()
	{
		$this->_init('unl_ship/shipment_package');
	}

	public function getPdfImage()
	{
	    $rawformat = strtolower($this->getLabelFormat());
	    $imgstr = $this->getLabelImage();

	    if (!$imgstr) {
	        return false;
	    }

	    if ($rawformat != 'png') {
	        $image = imagecreatefromstring($imgstr);
	        imageinterlace($image, false);

	        if ($this->getCarrier() == 'ups') {
	            $image = imagerotate($image, 270, 0);
	        }

	        ob_start();
	        imagepng($image);
	        $imgstr = ob_get_clean();
	        imagedestroy($image);
	        unset($image);
	    }

        return new Zend_Pdf_Resource_Image_Png('data://image/png;base64,' . base64_encode($imgstr));
	}
}

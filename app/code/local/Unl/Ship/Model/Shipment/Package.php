<?php

class Unl_Ship_Model_Shipment_Package extends Mage_Core_Model_Abstract
{

	protected function _construct()
	{
		$this->_init('unl_ship/shipment_package');
	}

	public function getPdfImage()
	{
	    $imgstr = $this->getLabelImage();

	    if (!$imgstr) {
	        return false;
	    }

        $image = imagecreatefromstring($imgstr);
        if (!$image) {
            return false;
        }

        imageinterlace($image, false);

        if (imagesx($image) > imagesy($image)) {
            $image = imagerotate($image, 270, 0);
        }

//         imagefilter($image, IMG_FILTER_GRAYSCALE);
        imagetruecolortopalette($image, false, 256);

        ob_start();
        imagepng($image, null, 9, PNG_ALL_FILTERS);
        $imgstr = ob_get_clean();
        imagedestroy($image);
        unset($image);

        return new Zend_Pdf_Resource_Image_Png('data://image/png;base64,' . base64_encode($imgstr));
	}
}

<?php

class Unl_Ship_Helper_Pdf extends Mage_Core_Helper_Abstract
{
    public function attachImagePage(Zend_Pdf $pdf, Zend_Pdf_Resource_Image $pdfimg)
    {
        //label size in a pts (1/72 inch) - 6" x 4"
        $label4x6landscape = '432:288:';
        $label4x6portrait = '288:432:';

        $pdfpage = $pdf->newPage($label4x6portrait);

        $width = $pdfimg->getPixelWidth();
        $height = $pdfimg->getPixelHeight();

        // scale the image to proper width
        $ratio = 288 / $width;
        $width = $width * $ratio;
        $height = $height * $ratio;
        $offset = 432 - $height;

        //get the image into a PdfImage object
        $pdfpage->drawImage($pdfimg, 0, $offset, $width, $height + $offset);

        $pdf->pages[] = $pdfpage;

        return $this;
    }
}

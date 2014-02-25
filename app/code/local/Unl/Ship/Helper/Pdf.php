<?php

class Unl_Ship_Helper_Pdf extends Mage_Core_Helper_Abstract
{
    //label size in a pts (1/72 inch) - 4" x 6"
    const LABEL_4X6_WIDTH = 288;
    const LABEL_4X6_HEIGHT = 432;

    public function attachImagePage(Zend_Pdf $pdf, Zend_Pdf_Resource_Image $pdfimg)
    {
        $pdfpage = $pdf->newPage(self::LABEL_4X6_WIDTH, self::LABEL_4X6_HEIGHT);

        $width = $pdfimg->getPixelWidth();
        $height = $pdfimg->getPixelHeight();

        // scale the image to proper width
        $ratio = self::LABEL_4X6_WIDTH / $width;
        $width = $width * $ratio;
        $height = $height * $ratio;
        $offset = self::LABEL_4X6_HEIGHT - $height;

        //get the image into a PdfImage object
        $pdfpage->drawImage($pdfimg, 0, $offset, $width, $height + $offset);

        $pdf->pages[] = $pdfpage;

        return $this;
    }
}

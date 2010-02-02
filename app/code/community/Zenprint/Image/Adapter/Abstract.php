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

abstract class Zenprint_Image_Adapter_Abstract
{
    public $fileName = null;
    public $imageBackgroundColor = 0;

    const POSITION_TOP_LEFT = 'top-left';
    const POSITION_TOP_RIGHT = 'top-right';
    const POSITION_BOTTOM_LEFT = 'bottom-left';
    const POSITION_BOTTOM_RIGHT = 'bottom-right';
    const POSITION_STRETCH = 'stretch';
    const POSITION_TILE = 'tile';

    protected $_fileType = null;
    protected $_fileMimeType = null;
    protected $_fileSrcName = null;
    protected $_fileSrcPath = null;
    protected $_imageHandler = null;
    protected $_imageSrcWidth = null;
    protected $_imageSrcHeight = null;
    protected $_imageSrcResolution = null;
    protected $_requiredExtensions = null;
    protected $_watermarkPosition = array();
    protected $_watermarkWidth = array();
    protected $_watermarkHeigth = array();
    protected $_keepProportion = true;

    abstract public function open($fileName);

    abstract public function saveAsFiletype($filtype=null, $destination=null, $newName=null);
    
    abstract public function save($destination=null, $newName=null);

    abstract public function checkDependencies();

    public function getMimeType()
    {
        if( $this->_fileType ) {
            return $this->_fileType;
        } else {
            list($this->_imageSrcWidth, $this->_imageSrcHeight, $this->_fileType, $this->_imageSrcResolution ) = getimagesize($this->fileName);
            $this->_fileMimeType = image_type_to_mime_type($this->_fileType);
            return $this->_fileMimeType;
        }
    }
    
    public function getImageWidth()
    {
        return $this->_imageSrcWidth;
    }
 
    public function getImageHeight()
    {
        return $this->_imageSrcHeight;
    }
    
    public function getImageResolution()
    {
        return $this->_imageSrcResolution;
    }

    public function setWatermarkPosition($index,$position)
    {
        $this->_watermarkPosition[$index] = $position;
        return $this;
    }

    public function getWatermarkPosition($index)
    {
        return $this->_watermarkPosition[$index];
    }

    public function setWatermarkWidth($index, $width)
    {
        $this->_watermarkWidth[$index] = $width;
        return $this;
    }

    public function getWatermarkWidth($index)
    {
        return $this->_watermarkWidth[$index];
    }

    public function setWatermarkHeigth($index,$heigth)
    {
        $this->_watermarkHeigth[$index] = $heigth;
        return $this;
    }

    public function getWatermarkHeigth($index)
    {
        return $this->_watermarkHeigth[$index];
    }

    public function setKeepProportion($flag)
    {
        $this->_keepProportion = $flag;
        return $this;
    }

    public function keepProportion()
    {
        return $this->_keepProportion;
    }

    protected function _getFileAttributes()
    {
        $pathinfo = pathinfo($this->fileName);

        $this->_fileSrcPath = $pathinfo['dirname'];
        $this->_fileSrcName = $pathinfo['basename'];
    }

}
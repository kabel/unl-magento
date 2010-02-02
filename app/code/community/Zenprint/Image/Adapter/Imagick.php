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
 * Product Service
 *
 * @category   Zenprint
 * @package    Zenprint_Imagick
 */

class Zenprint_Image_Adapter_Imagick extends Zenprint_Image_Adapter_Abstract
{

    protected $_requiredExtensions = Array("Imagick");
    protected $_draw;
    protected $_pixel;
    protected $_magickwand;
    protected $_drawwand;
    protected $_pixelwand;
    protected $_useMagickSave = false;
    
	const COMPRESSION_QUALITY = 90;

    function __construct()
    {
		$this->checkDependencies();
    }

    public function open($filename)
    {
        $this->fileName = $filename;
        $this->getMimeType();
        $this->_getFileAttributes();
        switch( $this->_fileType ) {
            case IMAGETYPE_GIF:
			case IMAGETYPE_JPEG:
			case IMAGETYPE_PNG:
			case IMAGETYPE_XBM:		
			case IMAGETYPE_WBMP:
				$this->_imageHandler = new Imagick($this->fileName);
                break;
            default:
                throw new Exception("Unsupported image format.");
                break;
        }
    }
    
    /**
     * Saves the image out as the designated filetype.
     *
     * @param string $filetype Acceptable values are IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_XBM, IMAGETYPE_WBMP
     * @param string $destination
     * @param string $newName Should not include and extension
     * @return string The filepath where the image was saved or false on error.
     */
    public function saveAsFiletype($filetype=null, $destination=null, $newName=null)  {
    	if(empty($filetype))  {
    		$filetype = $this->_fileType;
    	}
    	
    	//set the output folder
    	if(!empty($destination) && isset($destination))  {
    		$fileName = rtrim($destination, '/').'/';
    	}
    	else  {
    		$fileName = rtrim($this->_fileSrcPath, '/').'/';
    	}
    	//set the output filename
    	if(!empty($newName))  {
    		$fileName .= $newName;
    	}
    	else  {
    		$fileName .= ltrim(substr($this->_fileSrcName, 0, strpos($this->_fileSrcName, '.')), '/');
    	}
    	
    	switch($filetype) {
            case IMAGETYPE_GIF:
            	$fileName .= '.gif';
				$this->_imageHandler->setImageFormat('gif');
                break;

            case IMAGETYPE_JPEG:
            	$fileName .= '.jpg';
				$this->_imageHandler->setCompressionQuality(self::COMPRESSION_QUALITY);
				$this->_imageHandler->setImageFormat('jpeg');
                break;

            case IMAGETYPE_PNG:
            	$fileName .= '.png';
				$this->_imageHandler->setImageFormat('png');
                break;

            case IMAGETYPE_XBM:
            	$fileName .= '.xbm';
				$this->_imageHandler->setImageFormat('xbm');
                break;

            case IMAGETYPE_WBMP:
            	$fileName .= '.wbmp';
				$this->_imageHandler->setImageFormat('wbmp');
                break;

            default:
                throw new Exception("Unsupported image format.");
                break;
        }
    	
        $this->_imageHandler->writeImage($fileName);
        
    	return $fileName;
    }

    public function save($destination=null, $newName=null)
    {
        if($this->_useMagickSave) {
            $this->saveWand($destination,$newName);
            return true;
        }
        $fileName = ( !isset($destination) ) ? $this->fileName : $destination;

        if( isset($destination) && isset($newName) ) {
            $fileName = $destination . "/" . $fileName;
        } elseif( isset($destination) && !isset($newName) ) {
            $info = pathinfo($destination);
            $fileName = $destination;
            $destination = $info['dirname'];
        } elseif( !isset($destination) && isset($newName) ) {
            $fileName = $this->_fileSrcPath . "/" . $newName;
        } else {
            $fileName = $this->_fileSrcPath . $this->_fileSrcName;
        }

        $destinationDir = ( isset($destination) ) ? $destination : $this->_fileSrcPath;
		$destinationDir = str_replace('//','/',$destinationDir);

        if( !is_writable($destinationDir) ) {
            try {
                $io = new Varien_Io_File();
                $io->mkdir($destinationDir);
            } catch (Exception $e) {
                throw new Exception("Unable to write file into directory '{$destinationDir}'. Access forbidden.");
            }
        }

        switch( $this->_fileType ) {
            case IMAGETYPE_GIF:
				$this->_imageHandler->setImageFormat('gif');
                break;

            case IMAGETYPE_JPEG:
				$this->_imageHandler->setCompressionQuality(self::COMPRESSION_QUALITY);
				$this->_imageHandler->setImageFormat('jpeg');
                break;

            case IMAGETYPE_PNG:
				$this->_imageHandler->setImageFormat('png');
                break;

            case IMAGETYPE_XBM:
				$this->_imageHandler->setImageFormat('xbm');
                break;

            case IMAGETYPE_WBMP:
				$this->_imageHandler->setImageFormat('wbmp');
                break;

            default:
                throw new Exception("Unsupported image format.");
                break;
        }
        
        $this->_imageHandler->writeImage($fileName);

    }
 
    public function checkDependencies()
    {
        foreach( $this->_requiredExtensions as $value ) {
            if( !extension_loaded($value) ) {
                throw new Exception("Required PHP extension '{$value}' was not loaded.");
            }
        }
    }

}

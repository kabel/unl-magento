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
 * Image handler library
 *
 * @category   Zenprint
 * @package    Zenprint_Image
 */
class Zenprint_Image
{
    protected $_adapter;

    protected $_fileName;

    /**
     * Constructor
     *
     * @param Zenprint_Image_Adapter $adapter. Default value is IMAGICK
     * @param string $fileName
     * @return void
     */
    function __construct($fileName=null, $adapter=Zenprint_Image_Adapter::ADAPTER_IMK)
    {
        $this->_getAdapter($adapter);
        $this->_fileName = $fileName;
        if( isset($fileName) ) {
            $this->open();
        }
    }

    /**
     * Opens an image and creates image handle
     *
     * @access public
     * @return void
     */
    public function open()
    {
        $this->_getAdapter()->checkDependencies();

        if( !file_exists($this->_fileName) ) {
            throw new Exception("File '{$this->_fileName}' does not exists.");
        }

        $this->_getAdapter()->open($this->_fileName);
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
    	return $this->_getAdapter()->saveAsFiletype($filetype, $destination, $newName);
    }

    /**
     * Save handled image into file
     *
     * @param string $destination. Default value is NULL
     * @param string $newFileName. Default value is NULL
     * @access public
     * @return void
     */
    public function save($destination=null, $newFileName=null)
    {
        $this->_getAdapter()->save($destination, $newFileName);
    }

    protected function _getAdapter($adapter=null)
    {
        if( !isset($this->_adapter) ) {
            $this->_adapter = Zenprint_Image_Adapter::factory( $adapter );
        }
        return $this->_adapter;
    }
}

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

class Zenprint_Image_Adapter 
{
    const ADAPTER_GD    = 'GD';
    const ADAPTER_GD2   = 'GD2';
    const ADAPTER_IM    = 'IMAGEMAGIC';
    const ADAPTER_IME   = 'IMAGEMAGIC_EXTERNAL';
    const ADAPTER_IMK   = 'IMAGICK';

    public static function factory($adapter)
    {
        switch( $adapter ) {
            case self::ADAPTER_GD:
                return new Varien_Image_Adapter_Gd();
                break;

            case self::ADAPTER_GD2:
                return new Varien_Image_Adapter_Gd2();
                break;

            case self::ADAPTER_IM:
                return new Varien_Image_Adapter_Imagemagic();
                break;

            case self::ADAPTER_IMK:
                return new Zenprint_Image_Adapter_Imagick();
                break;
                
            default:
                throw new Exception('Invalid adapter selected.');
                break;
        }
    }
}

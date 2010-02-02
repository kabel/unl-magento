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

require_once 'Xajax/xajax_core/xajax.inc.php';


class Xajax_Xajax    
{
	public $call;

    /**
     * Class constructor
     *
     * @param array  $options  Options to pass to xajax constructor
     */
    public function __construct($options = array())
    {
        $this->call = new xajax($options);
    }

}

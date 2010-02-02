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

class Zenprint_Xajax_Model_Handler extends Varien_Object
{
    protected $_xajax;
    private static $ajaxFuncPrefix = 'ax';
    protected $_xresponse;
    
    public function __construct($path="/xjx")    
    {
        if(!Mage::registry('xajax')) {
            $this->_xajax = new Xajax_Xajax($path);
            Mage::register('xajax',$this->_xajax);
        }
        $this->_xajax = Mage::registry('xajax'); 
        $this->_xresponse = new xajaxResponse();
    }
    
    public function registerFunction($args)
    {
        $this->_xajax->call->registerFunction($args); 
    }
    public function printJavascript()
    {
        $this->_xajax->call->printJavascript(); 
    }
    public function getJavascript()
    {
        return $this->_xajax->call->getJavascript(); 
    }
    public function processRequest()
    {
        $this->_xajax->call->processRequest(); 
    }
    public function setRequestURI($path)
    {
        $this->_xajax->call->setRequestURI($path); 
    }
    public function registerFunctions() 
    {
		$methods = get_class_methods($this);
    		
		foreach ($methods as $m) {
    		$p = self::$ajaxFuncPrefix;
			if (preg_match("/^{$p}[A-Z]/", $m) || $m == 'fileUpload') {
				$m2 = preg_replace("/^{$p}([A-Z])/e", "strtolower('$1')", $m);
				$this->registerFunction(array($m2, $this, $m));
			}
		}
    }
}
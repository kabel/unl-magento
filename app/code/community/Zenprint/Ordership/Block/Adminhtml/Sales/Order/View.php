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
 
class Zenprint_Ordership_Block_Adminhtml_Sales_Order_View extends Mage_Adminhtml_Block_Sales_Order_View
{
	public function __construct()
    {
    	parent::__construct();
    	$this->_removeButton('order_reorder');
    	
    	//add the autoship button
    	if ($this->_isAllowedAction('ship') && $this->getOrder()->canShip()) {
            $this->_addButton('auto_ship', array(
                'label'     => Mage::helper('sales')->__('Auto Ship'),
                'onclick'   => 'setLocation(\'' . $this->getAutoShipUrl() . '\')',
            ));
        }    
    }
    
    
	public function getAutoShipUrl()
    {
        return $this->getUrl('ordership');
    }
    
}
?>
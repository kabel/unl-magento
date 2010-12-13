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
class Zenprint_Ordership_Block_Adminhtml_Sales_Order extends Mage_Adminhtml_Block_Sales_Order
{
    public function __construct()
    {
        parent::__construct();

//        if (Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/ship'))  {
//	        //add a button for shipping an order by its order_id
//			$this->_addButton('ship', array(
//				'label'     => Mage::helper('sales')->__('Ship Orders'),
//				'onclick'   => "setLocation('".$this->getUrl('ordership')."')",
//				'class'     => 'none',
//			));
//        }

    }

}

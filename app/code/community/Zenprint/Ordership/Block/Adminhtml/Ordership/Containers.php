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
 
class Zenprint_Ordership_Block_Adminhtml_Ordership_Containers extends Mage_Adminhtml_Block_Widget  {
	
	protected function _construct()
    {
        parent::_construct();

        $this->setTemplate('ordership/containers.phtml');
    }
       
    /**
     * Retrieves all the containers for the shipping carrier for this object.
     *
     * @return array An array of containers.
     */
    protected function getContainers()  {
    	$carrier = $this->getRequest()->getParam('carrier');
    	
    	//container code and description
    	$ups = Mage::getModel('usa/shipping_carrier_ups');
    	$containers = $ups->getCode('package_type');

    	return $containers;
    }
}
?>
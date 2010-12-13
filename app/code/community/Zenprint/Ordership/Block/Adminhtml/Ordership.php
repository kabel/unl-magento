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

class Zenprint_Ordership_Block_Adminhtml_Ordership extends Mage_Adminhtml_Block_Widget
{
    protected $_ajaxjs;
    protected $_loadorderjs;

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('ordership/index.phtml');

    }

    protected function _prepareLayout()
    {
    	$orderajax = Mage::getModel('xajax/ordership');
		$orderajax->setRequestURI($this->getUrl('xjx/ordership/'));
		$this->_ajaxjs = $orderajax->getJavascript();
		$this->_ajaxjs .= "<script type='text/javascript'>
//<![CDATA[
xajax.callback.global.onRequest = function() {
   	var loadingmask = \$('loading-mask');
	if(typeof loadingmask != 'undefined' && null != loadingmask ) {
          loadingmask.style.display = 'block';
	}
}
xajax.callback.global.beforeResponseProcessing = function() {\$('loading-mask').style.display='none';}
//]]>
</script>";

        //determine if there was an orderid passed
        $orderid = $this->getRequest()->getParam('order_id');
        if(!empty($orderid))  {
        	$incrementid = Mage::getModel('sales/order')->load($orderid)->getIncrementId();
    		$this->_loadorderjs = "
    		<script type='text/javascript'>
    			document.getElementById('order_id').value = '$incrementid';
    			retrieveOrder(true);
    		</script>";
        }

        return parent::_prepareLayout();
    }

    public function isQueueEmpty()
    {
        $session = Mage::getSingleton('adminhtml/session');
        $queue = $session->getOrdershipQueue();
        return empty($queue);
    }

    /**
     * Retrieve all potential dimensions for container types for each carrier and unit
     *
     * @param string $returntype If not set to 'php', will be returned as a JSON encoded string
     */
    public function getPackageDimensions($returntype='php')  {
    	$retval = array(
    		'UPS' => array(
    			'IN' => Mage::getSingleton('usa/shipping_carrier_ups')->getCode('package_dimensions_in'),
    			'CM' => Mage::getSingleton('usa/shipping_carrier_ups')->getCode('package_dimensions_cm'),
    		),
    		//TODO: Implement Fedex
//    		'FEDEX' => ,
    	);

    	if($returntype == 'php')  {
    		return $retval;
    	}

    	//return as JS array
    	return json_encode($retval);
    }

}

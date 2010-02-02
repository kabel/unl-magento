<?php
/**
 * Zenprint
 *
[[[ZENPRINT_COPYRIGHT_TEXT]]]
 *
 * @category   Zenprint
 * @package    Zenprint_Gallery
[[[ZENPRINT_COPYRIGHT]]]
 */
 
class Zenprint_Ordership_Model_Shipping_Carrier_Fedex_Source_Unitofdimension  {
	
    public function toOptionArray()  {
        $unitArr = Mage::getSingleton('usa/shipping_carrier_fedex')->getCode('unit_of_dimension');
    	$returnArr = array();
    	foreach ($unitArr as $key => $val){
    		$returnArr[] = array('value'=>$key,'label'=>$val);
    	}
    	return $returnArr;
    }
}
?>
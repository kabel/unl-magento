<?php

class Unl_Ship_Model_Resource_Shipment_Package_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
	protected $_dataFields = array(
	    'label_image',
	    'html_label_image',
	    'ins_doc',
	    'intl_doc',
    );

    protected function _construct()
	{
		$this->_init('unl_ship/shipment_package');
	}

	public function selectNoData()
	{
	    $cols = array_keys($this->getConnection()->describeTable($this->getMainTable()));

	    foreach ($cols as $col) {
	        if (!in_array($col, $this->_dataFields)) {
	            $this->addFieldToSelect($col);
	        }
	    }

	    return $this;
	}
}

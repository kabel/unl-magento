<?php

class Unl_Core_Block_Adminhtml_Report_Sales_Bursar_Grid_Abstract extends Mage_Adminhtml_Block_Report_Grid_Abstract
{
    protected $_groupedColumn = array('period');

    public function __construct()
    {
        parent::__construct();
        $this->setCountTotals(true);
        $this->setCountSubTotals(true);
    }

    public function _exportIterateCollection($callback, array $args)
    {
        // Overrides the paging because UNION queries can't be paged
        $collection = $this->getCollection();
        $collection->load();
        foreach ($collection as $item) {
            call_user_func_array(array($this, $callback), array_merge(array($item), $args));
        }
    }
}
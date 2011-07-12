<?php

class Unl_AdminLog_Block_Archive_Grid extends Unl_AdminLog_Block_Log_Grid
{
    public function __construct()
    {
        $this->_archiveFilter = true;
    }

    protected function _getCsvUrl()
    {
        return '*/*/exportArchiveCsv';
    }

    protected function _getExcelUrl()
    {
        return '*/*/exportArchiveExcel';
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/archiveGrid', array('_current'=>true));
    }
}

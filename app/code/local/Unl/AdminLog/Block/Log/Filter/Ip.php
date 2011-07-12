<?php

class Unl_AdminLog_Block_Log_Filter_Ip extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Text
{
    public function getCondition()
    {
        return array('like'=>$this->_escapeValue($this->getValue()).'%');
    }
}

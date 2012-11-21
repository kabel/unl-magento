<?php

class Unl_AdminLog_Block_Log_Filter_Ip extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Text
{
    public function getCondition()
    {
        $helper = Mage::getResourceHelper('core');
        $likeExpression = $helper->addLikeEscape($this->getValue(), array('position' => 'start'));
        return array('like' => $likeExpression);
    }
}

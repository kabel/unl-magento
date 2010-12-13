<?php

class Unl_Core_Model_System_Config_Source_Design_Noticetype
{
    public function toOptionArray()
    {
        return array(
            array('value'=>'', 'label'=>'Notice'),
            array('value'=>'affirm', 'label'=>'Affirm'),
            array('value'=>'negate', 'label'=>'Negate'),
            array('value'=>'alert', 'label'=>'Alert'),
        );
    }
}

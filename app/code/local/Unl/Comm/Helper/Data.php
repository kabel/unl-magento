<?php

class Unl_Comm_Helper_Data extends Mage_Core_Helper_Abstract
{
    public function getTemplateProcessor()
    {
        $model = 'unl_comm/template_filter';
        return Mage::getModel($model);
    }
}

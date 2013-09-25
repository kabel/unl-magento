<?php

class Unl_Core_Model_Log_Visitor extends Mage_Log_Model_Visitor
{
    public function isModuleIgnored($observer)
    {
        $ignores = Mage::getConfig()->getNode('global/ignoredModules/entities')->asArray();

        if( is_array($ignores) && $observer) {
            $curModule = $observer->getEvent()->getControllerAction()->getRequest()->getRouteName();
            if (isset($ignores[$curModule])) {
                return true;
            }
        }

        if ($observer && $action = $observer->getEvent()->getControllerAction()) {
            if ($action->getFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_START_SESSION)) {
                return true;
            }
        }

        return false;
    }
}

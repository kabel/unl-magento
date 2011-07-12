<?php

class Unl_AdminLog_Model_Source_Event
{
    public function toOptionHash()
    {
        $options = array();

        $config = Mage::getSingleton('unl_adminlog/config')->getConfig();
        foreach ($config->log_events->children() as $eventName => $event) {
            $options[$eventName] = (string) $event->label;
        }
        asort($options);

        return $options;
    }
}

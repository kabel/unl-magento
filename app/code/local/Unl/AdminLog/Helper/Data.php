<?php

class Unl_AdminLog_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Returns the string value for the requested event module
     *
     * @param Mage_Core_Controller_Request_Http $request
     */
    public function getEvent($request)
    {
        $event = $request->getRequestedRouteName() . '_' . $request->getRequestedControllerName();
        return strtolower($event);
    }

    /**
     * Returns the shortened action path for the request
     *
     * @param Mage_Core_Controller_Request_Http $request
     */
    public function getActionPath($request)
    {
        $route = $request->getRequestedRouteName();
        $actionPath = $request->getRequestedControllerName() . '_' . $request->getRequestedActionName();

        if ($route != 'adminhtml') {
            $actionPath = $route . '_' . $actionPath;
        }

        return $actionPath;
    }
}

<?php

class Unl_AdminLog_Model_Observer
{
    protected $_config;

    public function onActionPredispatch($observer)
    {
        /* @var $controller Mage_Adminhtml_Controller_Action */
        $controller = $observer->getEvent()->getControllerAction();
        $request = $controller->getRequest();
        $config = $this->_getConfig();

        if (!$config->isLogEnabled()) {
            return $this;
        }

        if (in_array($request->getActionName(), array('noroute', 'denied'))) {
            return $this;
        }

//         if ($request->getParam('forwarded')) {
//             return $this;
//         }

        /* @var $helper Unl_AdminLog_Helper_Data */
        $helper = Mage::helper('unl_adminlog');
        $event = $helper->getEvent($request);

        if (!$config->checkLogMiscEvents() && !$config->isEventRegistered($event)) {
            return $this;
        }

        $action = $config->getEventAction($event, $request);
        if ($action == Unl_AdminLog_Model_Source_Action::OTHER && !$config->checkLogOtherAction()) {
            return $this;
        }

        $actionPath = $helper->getActionPath($request);
        $params = $config->getActionParams($event, $request);

        try {
            if (!is_callable(array($controller, 'isAllowed'))) {
                throw new Exception('Missing public access to controller ACL check');
            }
            $result = $controller->isAllowed() ? Unl_AdminLog_Model_Source_Result::SUCCESS : Unl_AdminLog_Model_Source_Result::FAIL;

            $log = $this->_logFactory(array(
                'event_module' => $event,
                'action' => $action,
                'result' => $result,
                'action_path' => $actionPath,
                'action_info' => serialize($params),
            ));

            $log->save();
        } catch (Exception $e) {
            Mage::logException($e);
        }

        return $this;
    }

    /**
     * Lazily retrieves the cached config model
     *
     * @return Unl_AdminLog_Model_Config
     */
    protected function _getConfig()
    {
        if (null === $this->_config) {
            $this->_config = Mage::getSingleton('unl_adminlog/config');
        }

        return $this->_config;
    }

    protected function _logFactory($data = array())
    {
        if ($user = Mage::getSingleton('admin/session')->getUser()) {
            $userId = $user->getId();
        } else {
            $userId = 0;
        }

        $log = Mage::getModel('unl_adminlog/log');
        $log->addData($data);
        $log->setCreatedAt(now());
        $log->setRemoteAddr(Mage::helper('core/http')->getRemoteAddr(true));
        $log->setUserId($userId);

        return $log;
    }

    public function onAdminLoginFail($observer)
    {
        $config = $this->_getConfig();
        if (!$config->isLogEnabled()) {
            return $this;
        }

        $user = $observer->getEvent()->getUserName();
//         $exception = $observer->getEvent()->getException();

        try {
            $log = $this->_logFactory(array(
                'event_module' => 'adminhtml_index',
                'action' => Unl_AdminLog_Model_Source_Action::LOGIN,
                'result' => Unl_AdminLog_Model_Source_Result::FAIL,
                'action_path' => 'admin_login',
                'action_info' => serialize($user)
            ));

            $log->save();
        } catch (Exception $e) {
            Mage::logException($e);
        }

        return $this;
    }

    public function onAdminLoginSucess($observer)
    {
        $config = $this->_getConfig();
        if (!$config->isLogEnabled()) {
            return $this;
        }

        $user = $observer->getEvent()->getUser();

        try {
            $log = $this->_logFactory(array(
                'event_module' => 'adminhtml_index',
                'action' => Unl_AdminLog_Model_Source_Action::LOGIN,
                'result' => Unl_AdminLog_Model_Source_Result::SUCCESS,
                'action_path' => 'admin_login',
                'action_info' => serialize($user->getUsername())
            ));

            $log->save();
        } catch (Exception $e) {
            Mage::logException($e);
        }

        return $this;
    }

    public function onLogClean($observer)
    {
        $config = $this->_getConfig();
        $config->cleanLog();

        return $this;
    }
}

<?php

class Unl_AdminLog_Model_Config
{
    const XML_NODE_ADMIN_LOG = 'adminhtml/unl_adminlog';

    const XML_PATH_ADMIN_LOG_ENABLED = 'system/adminlog/enabled';
    const XML_PATH_ADMIN_LOG_ACTION_OTHER = 'system/adminlog/log_other';
    const XML_PATH_ADMIN_LOG_MISC_EVENTS = 'system/adminlog/log_misc_events';
    const XML_PATH_ADMIN_LOG_ARCHIVE_DAYS = 'system/adminlog/archive_days';
    const XML_PATH_ADMIN_LOG_SAVE_DAYS =  'system/adminlog/save_days';

    /**
     * The XML config for the admin log
     *
     * @var Mage_Core_Model_Config_Element
     */
    protected $_config;

    protected $_configActionsMap = array(
        'view_actions'    => Unl_AdminLog_Model_Source_Action::VIEW,
        'save_actions'    => Unl_AdminLog_Model_Source_Action::SAVE,
        'delete_actions'  => Unl_AdminLog_Model_Source_Action::DELETE,
        'utility_actions' => Unl_AdminLog_Model_Source_Action::UTILITY,
    );

    /**
    * Getter for the admin log config
    *
    * @return Mage_Core_Model_Config_Element
    */
    public function getConfig()
    {
        if (!$this->_config) {
            $this->_config = Mage::getConfig()->getNode(self::XML_NODE_ADMIN_LOG);
        }

        return $this->_config;
    }

    public function isEventRegistered($event)
    {
        $config = $this->getConfig();
        return isset($config->log_events->$event);
    }

    public function isLogEnabled()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_ADMIN_LOG_ENABLED);
    }

    public function checkLogMiscEvents()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_ADMIN_LOG_MISC_EVENTS);
    }

    public function checkLogOtherAction()
    {
        return Mage::getStoreConfigFlag(self::XML_PATH_ADMIN_LOG_ACTION_OTHER);
    }

    public function getArchiveTime()
    {
        return Mage::getStoreConfig(self::XML_PATH_ADMIN_LOG_ARCHIVE_DAYS) * 60 * 60 * 24;
    }

    public function getCleanTime()
    {
        return Mage::getStoreConfig(self::XML_PATH_ADMIN_LOG_SAVE_DAYS) * 60 * 60 * 24;
    }

    public function cleanLog()
    {
        Mage::getResourceModel('unl_adminlog/log')
            ->clean($this->getArchiveTime(), $this->getCleanTime());
        return $this;
    }

    /**
     * Returns the action map value for the given request
     *
     * @param string $event
     * @param Mage_Core_Controller_Request_Http $request
     * @return int The int value of the action type
     */
    public function getEventAction($event, $request)
    {
        $action = false;
        $actionName = strtolower($request->getActionName());
        $config = $this->getConfig();
        $defaultActionMap = $config->action_map;

        if ($this->isEventRegistered($event)) {
            $eventDef = $config->log_events->$event;
            foreach ($this->_configActionsMap as $actionElem => $actionVal) {
                if (isset($eventDef->$actionElem, $eventDef->$actionElem->$actionName)) {
                    if ($eventDef->$actionElem->{$actionName}['condition']) {
                        $condParam = (string) $eventDef->$actionElem->{$actionName}['condition'];
                        if (!$request->getParam($condParam, false)) {
                            continue;
                        }
                    }

                    $action = $actionVal;
                    break;
                }
            }
        }

        if ($action === false) {
            if (isset($defaultActionMap->$actionName)) {
                $action = (int) $defaultActionMap->$actionName;
            } else {
                $action = Unl_AdminLog_Model_Source_Action::OTHER;
            }
        }

        return $action;
    }

    /**
    * Returns the param(s) that should be saved with the action log
    *
    * @param string $event
    * @param Mage_Core_Controller_Request_Http $request
    * @return mixed
    */
    public function getActionParams($event, $request)
    {
        $config = $this->getConfig();
        $idParams = array('id');
        $altParams = array();
        $params = array();
        $returnArray = false;

        if ($this->isEventRegistered($event)) {
            $eventDef = $config->log_events->$event;
            if (isset($eventDef->id_param)) {
                array_unshift($idParams, (string) $eventDef->id_param);
            }

            if (isset($eventDef->alt_params)) {
                $altParams = array_keys($eventDef->alt_params->asArray());
            }
        }

        foreach ($idParams as $idParam) {
            $id = $request->getParam($idParam);
            if (!empty($id)) {
                if (is_array($id) || strpos($id, ',')) {
                    $returnArray = true;
                }
                break;
            }
        }

        foreach ($altParams as $param) {
            $val = $request->getParam($param);
            if (!empty($val)) {
                $params[$param] = $val;
                if (is_array($val) || strpos($val, ',')) {
                    $returnArray = true;
                }
            }
        }

        if ($returnArray) {
            if (!empty($id)) {
                $params[$idParam] = $id;
            }

            return $params;
        }

        if (empty($id)) {
            if (count($params) == 1) {
                return current($params);
            }
        } elseif (empty($params)) {
            return $id;
        }

        return $params;
    }
}

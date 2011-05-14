<?php

class Unl_CustomerTag_Block_Tag_Grid_Renderer_Action
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Action
{
    public function render(Varien_Object $row)
    {
        $actions = $this->getColumn()->getActions();
        $this->_filterActions($row);

        $out = parent::render($row);

        $this->getColumn()->setActions($actions);

        return $out;
    }

    protected function _filterActions(Varien_Object $row)
    {
        $actions = $this->getColumn()->getActions();
        if (!is_array($actions)) {
            return $this;
        }

        foreach ($actions as $i => &$action) {
            if (isset($action['filter']) && isset($action['filter']['field'])) {
                $filterValue = isset($action['filter']['value']) ? $action['filter']['value'] : null;
                if ($row->getData($action['filter']['field']) != $filterValue) {
                    unset($actions[$i]);
                } else {
                    unset($action['filter']);
                }
            }
        }

        $this->getColumn()->setActions($actions);

        return $this;
    }
}

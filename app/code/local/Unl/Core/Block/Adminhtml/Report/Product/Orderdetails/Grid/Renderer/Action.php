<?php

class Unl_Core_Block_Adminhtml_Report_Product_Orderdetails_Grid_Renderer_Action
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{

    public function render(Varien_Object $row)
    {
        $actions = array();

        $actions[] = array(
            '@'	=>  array(
                'href'  => $this->getUrl('adminhtml/sales_order/view',
                    array(
                        'order_id'        => $row->getOrderId(),
                    )
                                ),
                'onclick'=>	'window.open(this.href);return false;'
            ),
            '#'	=> $row->getOrdernum()
        );

        return $this->_actionsToHtml($actions);
    }

    public function renderExport(Varien_Object $row)
    {
        return $row->getOrdernum();
    }

    protected function _actionsToHtml(array $actions)
    {
        $html = array();
        $attributesObject = new Varien_Object();
        foreach ($actions as $action) {
            $attributesObject->setData($action['@']);
            $html[] = '<a ' . $attributesObject->serialize() . '>' . $action['#'] . '</a>';
        }
        return implode('<span class="separator">&nbsp;|&nbsp;</span>', $html);
    }
}

<?php

class Unl_AdminLog_Block_Log_Renderer_Actioninfo
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
    * Render the grid cell value
    *
    * @param Varien_Object $row
    * @return string
    */
    public function render(Varien_Object $row)
    {
        $info = unserialize($row->getData($this->getColumn()->getIndex()));
        if (empty($info)) {
            return '&nbsp;';
        } elseif (is_array($info)) {
            $html = '<div class="css-tooltip">[&hellip;]<span>';
            foreach ($info as $key => $value) {
                $html .= '<strong>' . $this->htmlEscape($key) . ':</strong> ' . $this->htmlEscape(print_r($value, true)) . '<br />';
            }
            $html .= '</span></div>';

            return $html;
        }

        return $this->htmlEscape($info);
    }
}

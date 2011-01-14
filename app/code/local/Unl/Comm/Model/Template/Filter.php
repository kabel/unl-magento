<?php

class Unl_Comm_Model_Template_Filter extends Mage_Widget_Model_Template_Filter
{
    /**
     * Generate widget HTML if template variables are assigned
     *
     * @param array $construction
     * @return string
     */
    public function widgetDirective($construction)
    {
        if (!isset($this->_templateVars['customer'])) {
            return $construction[0];
        }
        $construction[2] .= sprintf(' store_id ="%s"', $this->getStoreId());
        return parent::widgetDirective($construction);
    }
}

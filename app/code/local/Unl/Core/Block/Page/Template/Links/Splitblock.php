<?php
/**
 * Block model for outputting split label list links
 *
 * @method string getIconClass()
 * @method Unl_Core_Block_Page_Template_Links_Splitblock setIconClass(string $value)
 * @method string getBadge()
 * @method Unl_Core_Block_Page_Template_Links_Splitblock setBadge(string $value)
 * @method string getBadgeTitle()
 * @method Unl_Core_Block_Page_Template_Links_Splitblock setBadgeTitle(string $value)
 * @method string getUpperLabel()
 * @method Unl_Core_Block_Page_Template_Links_Splitblock setUpperLabel(string $value)
 * @method string getLowerLabel()
 * @method Unl_Core_Block_Page_Template_Links_Splitblock setLowerLabel(string $value)
 */
class Unl_Core_Block_Page_Template_Links_Splitblock extends Mage_Page_Block_Template_Links_Block
{
    protected function _construct()
    {
        $this->setTemplate('page/template/linkssplitblock.phtml');
    }

    public function setUrl($value)
    {
        $this->_url = $value;

        return $this;
    }
}

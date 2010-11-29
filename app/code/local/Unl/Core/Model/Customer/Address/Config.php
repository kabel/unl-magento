<?php

class Unl_Core_Model_Customer_Address_Config extends Mage_Customer_Model_Address_Config
{
    public function getFormats()
    {
        if(is_null($this->_types)) {
            $this->_types = array();
            foreach($this->getNode('formats')->children() as $typeCode=>$typeConfig) {
                $type = new Varien_Object();
                $type->setCode($typeCode)
                    ->setTitle((string)$typeConfig->title)
                    ->setDefaultFormat((string)$typeConfig->defaultFormat)
                    ->setHtmlEscape((bool)(string)$typeConfig->htmlEscape);

                $renderer = (string)$typeConfig->renderer;
                if (!$renderer) {
                    $renderer = self::DEFAULT_ADDRESS_RENDERER;
                }

                $type->setRenderer(
                    Mage::helper('customer/address')
                        ->getRenderer($renderer)->setType($type)
                );

                $this->_types[] = $type;
            }
        }

        return $this->_types;
    }
}

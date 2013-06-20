<?php

class Unl_PaymentFilter_Model_Catalog_Product_Attribute_Source_Payments
    extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
    /**
     * Retrieve All options
     *
     * @return array
     */
    public function getAllOptions()
    {
        if (is_null($this->_options)) {
            $this->_options = array();

            $methods = Mage::getSingleton('payment/config')->getActiveMethods();
            foreach ($methods as $code => $method) {
                if ($code === 'free') {
                    continue;
                }

                $this->_options[] = array(
                    'value' => $code,
                    'label' => $method->getTitle() ? $method->getTitle() : $code
                );
            }
        }
        return $this->_options;
    }
}

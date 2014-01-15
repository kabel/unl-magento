<?php

class Unl_Core_Block_Customer_Address_Renderer_Default extends Mage_Customer_Block_Address_Renderer_Default
{
    /* Overrides
     * @see Mage_Customer_Block_Address_Renderer_Default::render()
     * by using region code in place of region attribute
     */
    public function render(Mage_Customer_Model_Address_Abstract $address, $format=null)
    {
        switch ($this->getType()->getCode()) {
            case 'html':
                $dataFormat = Mage_Customer_Model_Attribute_Data::OUTPUT_FORMAT_HTML;
                break;
            case 'pdf':
                $dataFormat = Mage_Customer_Model_Attribute_Data::OUTPUT_FORMAT_PDF;
                break;
            case 'oneline':
                $dataFormat = Mage_Customer_Model_Attribute_Data::OUTPUT_FORMAT_ONELINE;
                break;
            default:
                $dataFormat = Mage_Customer_Model_Attribute_Data::OUTPUT_FORMAT_TEXT;
                break;
        }

        $formater   = new Unl_Filter_Template();
        $attributes = Mage::helper('customer/address')->getAttributes();

        $data = array();
        foreach ($attributes as $attribute) {
            /* @var $attribute Mage_Customer_Model_Attribute */
            if (!$attribute->getIsVisible()) {
                continue;
            }
            if ($attribute->getAttributeCode() == 'country_id') {
                $data['country'] = $address->getCountryModel()->getName();
            } elseif ($attribute->getAttributeCode() == 'region') {
                $data['region_code'] = $address->getRegionCode();
                $data['region'] = $address->getRegion();
            } else {
                $dataModel = Mage_Customer_Model_Attribute_Data::factory($attribute, $address);
                $value     = $dataModel->outputValue($dataFormat);
                if ($attribute->getFrontendInput() == 'multiline') {
                    $values    = $dataModel->outputValue(Mage_Customer_Model_Attribute_Data::OUTPUT_FORMAT_ARRAY);
                    // explode lines
                    foreach ($values as $k => $v) {
                        $key = sprintf('%s%d', $attribute->getAttributeCode(), $k + 1);
                        $data[$key] = $v;
                    }
                }
                $data[$attribute->getAttributeCode()] = $value;
            }
        }

        if ($this->getType()->getHtmlEscape()) {
            foreach ($data as $key => $value) {
                $data[$key] = $this->htmlEscape($value);
            }
        }

        $formater->setVariables($data);

        $format = !is_null($format) ? $format : $this->getFormat($address);

        return $formater->filter($format);
    }
}

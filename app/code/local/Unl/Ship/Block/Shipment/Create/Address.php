<?php

class Unl_Ship_Block_Shipment_Create_Address extends Mage_Adminhtml_Block_Sales_Order_Abstract
{
    protected $_form;

    public function getOrder()
    {
        return Mage::registry('current_order');
    }

    public function getHeaderText()
    {
        return Mage::helper('sales')->__('Shipping Address');
    }

    public function getHeaderCssClass()
    {
        return 'head-shipping-address';
    }

    protected function _prepareLayout()
    {
        Varien_Data_Form::setElementRenderer(
            $this->getLayout()->createBlock('adminhtml/widget_form_renderer_element')
        );
        Varien_Data_Form::setFieldsetRenderer(
            $this->getLayout()->createBlock('adminhtml/widget_form_renderer_fieldset')
        );
        Varien_Data_Form::setFieldsetElementRenderer(
            $this->getLayout()->createBlock('adminhtml/widget_form_renderer_fieldset_element')
        );
    }

    public function getForm()
    {
        $this->_prepareForm();
        return $this->_form;
    }

    protected function _prepareForm()
    {
        if (!$this->_form) {
            $this->_form = new Varien_Data_Form();

            $this->_form->setHtmlNamePrefix('shipping_address');
            $this->_form->setHtmlIdPrefix('shipping_address_');

            $fieldset = $this->_form->addFieldset('main', array('no_container'=>true));
            $addressModel = Mage::getModel('customer/address');

            foreach ($addressModel->getAttributes() as $attribute) {
                if ($attribute->hasData('is_visible') && !$attribute->getIsVisible()) {
                    continue;
                }
                if ($inputType = $attribute->getFrontend()->getInputType()) {
                    $element = $fieldset->addField($attribute->getAttributeCode(), $inputType,
                        array(
                            'name'  => $attribute->getAttributeCode(),
                            'label' => $this->__($attribute->getFrontend()->getLabel()),
                            'class' => $attribute->getFrontend()->getClass(),
                            'required' => $attribute->getIsRequired(),
                        )
                    )
                    ->setEntityAttribute($attribute);

                    if ('street' === $element->getName()) {
                        $lines = Mage::getStoreConfig('customer/address/street_lines', $this->getStoreId());
                        $element->setLineCount($lines);
                    }

                    if ($inputType == 'select' || $inputType == 'multiselect') {
                        $element->setValues($attribute->getFrontend()->getSelectOptions());
                    }
                }
            }

            if ($regionElement = $this->_form->getElement('region')) {
                $regionElement->setRenderer(
                    $this->getLayout()->createBlock('adminhtml/customer_edit_renderer_region')
                );
            }
            if ($regionElement = $this->_form->getElement('region_id')) {
                $regionElement->setNoDisplay(true);
            }
            $this->_form->setValues($this->getFormValues());
            $this->_form->addFieldNameSuffix('shipping_address');
        }
        return $this;
    }

    public function getFormValues()
    {
        return $this->getOrder()->getShippingAddress()->getData();
    }

    public function getAddressAsString($address)
    {
        return $address->format('oneline');
    }
}
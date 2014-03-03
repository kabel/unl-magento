<?php

class Unl_Core_Block_Email_Order_Instructions extends Mage_Core_Block_Template
{
    /**
     * Ordered products with special instructions
     *
     * @var  Mage_Catalog_Model_Product[]
     */
    protected $_products;

    /**
     * Template processor instance
     *
     * @var Varien_Filter_Template
     */
    protected $_templateProcessor;

    protected function _construct()
    {
        $this->setTemplate('email/order/instructions.phtml');
        parent::_construct();
    }

    protected function _getTemplateProcessor()
    {
        if (null === $this->_templateProcessor) {
            $this->_templateProcessor = Mage::helper('catalog')->getPageTemplateProcessor();
        }

        return $this->_templateProcessor;
    }

    /**
     * Returns an array of products from the order that have <code>ordered_description</code>
     * attribute data.
     *
     * @return Mage_Catalog_Model_Product[]
     */
    public function getProducts()
    {
        if ($this->_products === null) {
            /* @var $order Mage_Sales_Model_Order */
            $order = $this->getOrder();
            $products = array();

            /* @var $item Mage_Sales_Model_Order_Item */
            foreach ($order->getAllItems() as $item) {
                $product = $item->getProduct();

                if ($product->getOrderedDescription() && !array_key_exists($product->getId(), $products)) {
                    $products[$product->getId()] = $product;
                }
            }

            $this->_products = $products;
        }

        return $this->_products;

    }

    public function canShow()
    {
        if ($this->getProducts()) {
            return true;
        }

        return false;
    }

    /**
     * Returns the rendered HTML from the product's <code>ordered_description</code>
     *
     * @param Mage_Catalog_Model_Product $product
     * @return string
     */
    public function getProductInstructions($product)
    {
        return $this->_getTemplateProcessor()->filter($product->getOrderedDescription());
    }
}

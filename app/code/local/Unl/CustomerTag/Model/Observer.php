<?php

class Unl_CustomerTag_Model_Observer
{
    protected $_currentCustomerTags;

    protected $_invoiceCustomerTags = array(
        'Allow Invoicing',
    );
    protected $_invoiceTagsCollection;

    public function onBlockBeforeToHtml($observer)
    {
        $block = $observer->getEvent()->getBlock();
        //Do Actions Based on Block Type

        if ($block instanceof Mage_Adminhtml_Block_Customer_Edit_Tabs) {
            if (Mage::registry('current_customer')->getId()) {
                $block->addTab('customertag', array(
                    'label'     => Mage::helper('unl_customertag')->__('Customer Tags'),
                    'class'     => 'ajax',
                    'url'       => $block->getUrl('unl_customertag/customer/grid', array('_current'=>true)),
                    'after'     => 'account'
                ));
            }
            return;
        }

        if ($block instanceof Mage_Adminhtml_Block_Catalog_Product_Edit_Tabs
            && !($block instanceof Mage_Adminhtml_Block_Catalog_Product_Edit_Tabs_Configurable)) {
            if (!($setId = $block->getProduct()->getAttributeSetId())) {
                $setId = Mage::app()->getRequest()->getParam('set', null);
            }
            if ($setId) {
                $block->addTab('customertag', array(
                    'label'     => Mage::helper('unl_customertag')->__('Access Tags'),
                    'class'     => 'ajax',
                    'url'       => $block->getUrl('unl_customertag/product/grid', array('_current'=>true)),
                    'after'     => 'inventory'
                ));
            }
            return;
        }

        if ($block instanceof Mage_Adminhtml_Block_Catalog_Category_Edit_Form) {
            $block->setChild('category.access.serializer',
                $block->getLayout()->createBlock('adminhtml/widget_grid_serializer', 'category.access.serializer')
            );
            $block->getLayout()->getBlock('category.access.serializer')
                ->initSerializerBlock('category.access.grid', 'getSelectedTags', 'access_tags', 'category_access');
            return;
        }
    }

    public function prepareCategoryTabs($observer)
    {
        /* @var $tabs Mage_Adminhtml_Block_Catalog_Category_Tabs */
        $tabs = $observer->getEvent()->getTabs();
        $tabs->addTab('access', array(
            'label'     => Mage::helper('unl_customertag')->__('Access Tags'),
            'content'   => $tabs->getLayout()->createBlock('unl_customertag/category_tab_access', 'category.access.grid')->toHtml(),
        ));
    }

    public function onCategoryPrepareSave($observer)
    {
        /* @var $category Mage_Catalog_Model_Category */
        $category = $observer->getEvent()->getCategory();
        /* @var $request Mage_Core_Controller_Request_Http */
        $request = $observer->getEvent()->getRequest();

        if ($request->getPost('access_tags') !== null) {
            $category->setAccessTagIds(Mage::helper('adminhtml/js')->decodeGridSerializedInput($request->getPost('access_tags')));
        }

        return $this;
    }

    public function onAfterCategorySave($observer)
    {
        /* @var $category Mage_Catalog_Model_Category */
        $category = $observer->getEvent()->getCategory();

        if ($category->getAccessTagIds() !== null) {
            Mage::getResourceModel('unl_customertag/tag')->addCategoryLinks($category);
        }

        return $this;
    }

    public function onProductPrepareSave($observer)
    {
        /* @var $product Mage_Catalog_Model_Product */
        $product = $observer->getEvent()->getProduct();
        /* @var $request Mage_Core_Controller_Request_Http */
        $request = $observer->getEvent()->getRequest();

        if ($request->getPost('access_tags') !== null) {
            $product->setAccessTagIds(Mage::helper('adminhtml/js')->decodeGridSerializedInput($request->getPost('access_tags')));
        }

        return $this;
    }

    public function onAfterProductSave($observer)
    {
        /* @var $product Mage_Catalog_Model_Product */
        $product = $observer->getEvent()->getProduct();

        if ($product->getAccessTagIds() !== null) {
            Mage::getResourceModel('unl_customertag/tag')->addProductLinks($product);
        }

        return $this;
    }

    public function onQuoteCopyCustomerFieldset($observer)
    {
        $customer = $observer->getEvent()->getSource();
        $quote = $observer->getEvent()->getTarget();

        if ($customer->getId()) {
            $tagIds = Mage::helper('unl_customertag')->getTagIdsByCustomer($customer);
            $quote->setCustomerTagIds(implode(',', $tagIds));
        }

        return $this;
    }

    public function isCustomerAllowedProduct($observer)
    {
        $result = $observer->getEvent()->getResult();
        $product = $observer->getEvent()->getProduct();

        /* @var $collection Unl_CustomerTag_Model_Mysql4_Tag_Collection */
        $collection = Mage::getResourceModel('unl_customertag/tag_collection')->addProductFilter($product->getId());
        $acl = $collection->getAllIds();
        $session = Mage::getSingleton('customer/session');

        if (!empty($acl)) {
            if (!$session->isLoggedIn()) {
                $result->setFailure(Unl_Core_Helper_Data::CUSTOMER_ALLOWED_PRODUCT_FAILURE_LOGIN);
                return $this;
            }

            if (null === $this->_currentCustomerTags) {
                $collection = Mage::getResourceModel('unl_customertag/tag_collection')->addCustomerFilter($session->getCustomer()->getId());
                $this->_currentCustomerTags = $collection->getAllIds();
            }
            foreach ($this->_currentCustomerTags as $tagId) {
                if (in_array($tagId, $acl)) {
                    return $this;
                }
            }
            $result->setFailure(Unl_Core_Helper_Data::CUSTOMER_ALLOWED_PRODUCT_FAILURE_ACL);
        }

        return $this;
    }

    public function isCustomerAllowedCategory($observer)
    {
        $result = $observer->getEvent()->getResult();
        $category = $observer->getEvent()->getCategory();

        /* @var $collection Unl_CustomerTag_Model_Mysql4_Tag_Collection */
        $collection = Mage::getResourceModel('unl_customertag/tag_collection')->addCategoryFilter($category->getId());
        $acl = $collection->getAllIds();
        $session = Mage::getSingleton('customer/session');

        if (!empty($acl)) {
            if (!$session->isLoggedIn()) {
                $result->setFailure(Unl_Core_Helper_Data::CUSTOMER_ALLOWED_CATEGORY_FAILURE_LOGIN);
                return $this;
            }

            if (null === $this->_currentCustomerTags) {
                $collection = Mage::getResourceModel('unl_customertag/tag_collection')->addCustomerFilter($session->getCustomer()->getId());
                $this->_currentCustomerTags = $collection->getAllIds();
            }
            foreach ($this->_currentCustomerTags as $tagId) {
                if (in_array($tagId, $acl)) {
                    return $this;
                }
            }
            $result->setFailure(Unl_Core_Helper_Data::CUSTOMER_ALLOWED_CATEGORY_FAILURE_ACL);
        }

        return $this;
    }

    public function isPaymentMethodActive($observer)
    {
        $method = $observer->getEvent()->getMethodInstance();
        $result = $observer->getEvent()->getResult();
        $quote  = $observer->getEvent()->getQuote();

        if ($method instanceof Unl_Core_Model_Payment_Method_Invoicelater) {
            $result->isAvailable = false;
            if ($quote && $quote->getCustomer()->getId()) {
                $tagIds = Mage::helper('unl_customertag')->getTagIdsByCustomer($quote->getCustomer());
                foreach ($this->_getInvoiceCustomerTagsCollection() as $tag) {
                    if (in_array($tag->getId(), $tagIds)) {
                        $result->isAvailable = true;
                        return $this;
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Retieves a collection of the special customer tags
     *
     * @return Unl_CustomerTag_Model_Mysql4_Tag_Collection
     */
    protected function _getInvoiceCustomerTagsCollection()
    {
        if (null === $this->_invoiceTagsCollection) {
            /* @var $collection Unl_CustomerTag_Model_Mysql4_Tag_Collection */
            $collection = Mage::getModel('unl_customertag/tag')->getCollection();
            $collection->addFieldToFilter('name', array('in' => $this->_invoiceCustomerTags));
            $this->_invoiceTagsCollection = $collection;
        }

        return $this->_invoiceTagsCollection;
    }
}

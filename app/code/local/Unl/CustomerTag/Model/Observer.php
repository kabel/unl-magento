<?php

class Unl_CustomerTag_Model_Observer
{
    protected $_currentCustomerTags;

    protected $_invoiceCustomerTags = array(
        'Allow Invoicing',
    );

    protected $_invoiceTagsCollection;

    /**
     * Returns the singleton instance of this module's helper
     *
     * @return Unl_CustomerTag_Helper_Data
     */
    protected function _getHelper()
    {
        return Mage::helper('unl_customertag');
    }

    /**
     * An <i>adminhtml</i> event observer for the <code>adminhtml_catalog_category_tabs</code>
     * event.
     *
     * @param Varien_Event_Observer $observer
     */
    public function prepareCategoryTabs($observer)
    {
        /* @var $tabs Mage_Adminhtml_Block_Catalog_Category_Tabs */
        $tabs = $observer->getEvent()->getTabs();

        $parent = $tabs->getLayout()->createBlock('adminhtml/text_list');
        $accessTab = $tabs->getLayout()->createBlock('unl_customertag/category_tab_access');
        $parent->append($accessTab);
        $accessTab->appendSerializer();

        $tabs->addTab('access', array(
            'label'     => $this->_getHelper()->__('Access Tags'),
            'content'   => $parent->toHtml(),
        ));
    }

    /**
     * An <i>adminhtml</i> event observer for the <code>catalog_category_prepare_save</code>
     * event.
     *
     * @param Varien_Event_Observer $observer
     * @return Unl_CustomerTag_Model_Observer
     */
    public function onCategoryPrepareSave($observer)
    {
        /* @var $category Mage_Catalog_Model_Category */
        $category = $observer->getEvent()->getCategory();
        /* @var $request Mage_Core_Controller_Request_Http */
        $request = $observer->getEvent()->getRequest();
        $ids = $request->getPost($this->_getHelper()->getAccessStorageName());

        if ($ids !== null) {
            $category->setAccessTagIds(Mage::helper('adminhtml/js')->decodeGridSerializedInput($ids));
        }

        return $this;
    }

    /**
     * An <i>adminhtml</i> event observer for the <code>catalog_category_save_after</code>
     * event.
     *
     * @param Varien_Event_Observer $observer
     * @return Unl_CustomerTag_Model_Observer
     */
    public function onAfterCategorySave($observer)
    {
        /* @var $category Mage_Catalog_Model_Category */
        $category = $observer->getEvent()->getCategory();

        if ($category->getAccessTagIds() !== null) {
            Mage::getResourceModel('unl_customertag/tag')->addCategoryLinks($category);
        }

        return $this;
    }

    /**
     * An <i>adminhtml</i> event observer for the <code>catalog_product_prepare_save</code>
     * event.
     *
     * @param Varien_Event_Observer $observer
     * @return Unl_CustomerTag_Model_Observer
     */
    public function onProductPrepareSave($observer)
    {
        /* @var $product Mage_Catalog_Model_Product */
        $product = $observer->getEvent()->getProduct();
        /* @var $request Mage_Core_Controller_Request_Http */
        $request = $observer->getEvent()->getRequest();
        $ids = $request->getPost($this->_getHelper()->getCategoryAccessStorageName());

        if ($ids !== null) {
            $product->setAccessTagIds(Mage::helper('adminhtml/js')->decodeGridSerializedInput($ids));
        }

        return $this;
    }

    /**
     * An <i>adminhtml</i> event observer for the <code>catalog_product_save_after</code>
     * event.
     *
     * @param Varien_Event_Observer $observer
     * @return Unl_CustomerTag_Model_Observer
     */
    public function onAfterProductSave($observer)
    {
        /* @var $product Mage_Catalog_Model_Product */
        $product = $observer->getEvent()->getProduct();

        if ($product->getAccessTagIds() !== null) {
            Mage::getResourceModel('unl_customertag/tag')->addProductLinks($product);
        }

        return $this;
    }

    /**
     * An event observer for the <code>core_copy_fieldset_customer_account_to_quote</code>
     * event.
     * Sets the customer_tag_ids from a collection.
     *
     * @param Varien_Event_Observer $observer
     * @return Unl_CustomerTag_Model_Observer
     */
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

    /**
     * An event observer for the custom <code>unl_product_acl_check</code>
     * event.
     *
     * @param Varien_Event_Observer $observer
     * @return Unl_CustomerTag_Model_Observer
     */
    public function isCustomerAllowedProduct($observer)
    {
        $result = $observer->getEvent()->getResult();
        $product = $observer->getEvent()->getProduct();

        /* @var $collection Unl_CustomerTag_Model_Resource_Tag_Collection */
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

    /**
     * An event observer for the custom <code>unl_category_acl_check</code>
     * event.
     *
     * @param Varien_Event_Observer $observer
     * @return Unl_CustomerTag_Model_Observer
     */
    public function isCustomerAllowedCategory($observer)
    {
        $result = $observer->getEvent()->getResult();
        $category = $observer->getEvent()->getCategory();

        /* @var $collection Unl_CustomerTag_Model_Resource_Tag_Collection */
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

    /**
     * An event observer for the <code>payment_method_is_active</code> event.
     *
     * @param Varien_Event_Observer $observer
     * @return Unl_CustomerTag_Model_Observer
     */
    public function isPaymentMethodActive($observer)
    {
        $method = $observer->getEvent()->getMethodInstance();
        $result = $observer->getEvent()->getResult();
        $quote  = $observer->getEvent()->getQuote();

        if (!$result->isAvailable) {
            return $this;
        }

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
     * @return Unl_CustomerTag_Model_Resource_Tag_Collection
     */
    protected function _getInvoiceCustomerTagsCollection()
    {
        if (null === $this->_invoiceTagsCollection) {
            /* @var $collection Unl_CustomerTag_Model_Resource_Tag_Collection */
            $collection = Mage::getModel('unl_customertag/tag')->getCollection();
            $collection->addFieldToFilter('name', array('in' => $this->_invoiceCustomerTags));
            $this->_invoiceTagsCollection = $collection;
        }

        return $this->_invoiceTagsCollection;
    }
}

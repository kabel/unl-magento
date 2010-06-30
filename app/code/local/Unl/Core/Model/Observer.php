<?php

class Unl_Core_Model_Observer
{
    public function prepareWysiwygConfig($observer)
    {
        $config = $observer->getEvent()->getConfig();
        $design = Mage::getModel('core/design_package')->setStore(Mage::app()->getDefaultStoreView());
        $css = array(
            '/wdn/templates_3.0/css/all.css',
            $design->getSkinUrl('css/styles.css')
        );
        
        if ($config->getContentCss()) {
            array_unshift($css, $config->getContentCss());
        }
        
        $config->setContentCss(implode(',', $css));
    }
    
    public function correctAdminBlocks($observer)
    {
        $block = $observer->getEvent()->getBlock();
        
        //Do actions based on block type
        
        $type = 'Mage_Adminhtml_Block_Catalog_Category_Tree';
        if ($block instanceof $type) {
            $block->getChild('store_switcher')->setTemplate('unl/store/switcher/enhanced.phtml');
            return;
        }
        
        $type = 'Mage_Adminhtml_Block_Dashboard';
        if ($block instanceof $type) {
            $block->getChild('store_switcher')->setTemplate('unl/store/switcher.phtml');
            return;
        }
        
        $reportSwitchers = array(
            'Mage_Adminhtml_Block_Report_Product_Downloads',
            'Mage_Adminhtml_Block_Report_Product_Lowstock',
            'Mage_Adminhtml_Block_Report_Product_Ordered_Grid',
            'Mage_Adminhtml_Block_Report_Product_Sold_Grid',
            'Mage_Adminhtml_Block_Report_Product_Viewed_Grid',
            'Mage_Adminhtml_Block_Report_Customer_Accounts_Grid',
            'Mage_Adminhtml_Block_Report_Customer_Totals_Grid',
            'Mage_Adminhtml_Block_Report_Customer_Orders_Grid',
            'Unl_Core_Block_Adminhtml_Report_Product_Orderdetails_Grid',
            'Unl_Core_Block_Adminhtml_Report_Product_Customized_Grid'
        );
        foreach ($reportSwitchers as $type) {
            if ($block instanceof $type) {
                $block->getChild('store_switcher')->setTemplate('unl/report/store/switcher.phtml');
                return;
            }
        }
        
        $reportSwitchers = array(
            'Mage_Adminhtml_Block_Report_Sales_Sales',
            'Mage_Adminhtml_Block_Report_Sales_Coupons'
        );
        foreach ($reportSwitchers as $type) {
            if ($block instanceof $type) {
                $block->getChild('store.switcher')->setTemplate('unl/report/store/switcher/enhanced.phtml');
                return;
            }
        }
        
        
        $type = 'Mage_Adminhtml_Block_Report_Sales_Tax_Grid';
        if ($block instanceof $type) {
            $block->setStoreSwitcherVisibility(false);
            return;
        }
        
        $type = 'Mage_Adminhtml_Block_System_Store_Edit_Form';
        if ($block instanceof $type) {
            /* @var $form Varien_Data_Form */
            $form = $block->getForm();
            if ($fs = $form->getElement('store_fieldset')) {
                $storeModel = Mage::registry('store_data');
                $fs->addField('store_unl_rate', 'text', array(
                    'name'      => 'store[unl_rate]',
                    'label'     => Mage::helper('core')->__('Marketplace Fee'),
                    'value'     => $storeModel->getUnlRate(),
                    'required'  => false,
                    'disabled'  => $storeModel->isReadOnly(),
                    'class'     => 'validate-percents'
                ));
            }
            return;
        }
    }
        
    // These occur before the correctAdminBlocks (_beforeToHtml) calls
    public function beforeCoreBlockToHtml($observer)
    {
        $block = $observer->getEvent()->getBlock();
        
        $type = 'Mage_Adminhtml_Block_Permissions_User_Edit_Tabs';
        if ($block instanceof $type) {
            $block->addTab('scope_section', array(
                'label'     => Mage::helper('adminhtml')->__('User Scope'),
                'title'     => Mage::helper('adminhtml')->__('User Scope'),
                'content'   => $block->getLayout()->createBlock('unl_core/adminhtml_permissions_user_edit_tab_scope')->toHtml(),
                'after'     => 'roles_section',
            ));
            return;
        }
        
        $type = 'Mage_Adminhtml_Block_Catalog_Product_Grid';
        if ($block instanceof $type) {
            $request = Mage::app()->getRequest();
            $request->setParam('_unlcore_std_product_grid', true);
            return;
        }
        
        $type = 'Mage_Page_Block_Switch';
        if ($block instanceof $type) {
            /* @var $block Mage_Page_Block_Switch */
            $groups = $block->getGroups();
            if (count($groups) > 1) {
                usort($groups, array($this, '_compareStores'));
                $block->setData('groups', $groups);
            }
            return;
        }
    }
    
    /**
     * 
     * @param Mage_Core_Model_Store_Group $a
     * @param Mage_Core_Model_Store_Group $b
     * @return int
     */
    protected function _compareStores($a, $b)
    {
        $sortA = $a->getDefaultStore()->getSortOrder();
        $sortB = $b->getDefaultStore()->getSortOrder();
        
        if ($sortA == $sortB) {
            return 0;
        }
        return ($sortA > $sortB) ? 1 : -1;
    }
    
    public function beforeEavCollectionLoad($observer)
    {
        $request = Mage::app()->getRequest();
        $collection = $observer->getEvent()->getCollection();
        
        $type = 'Mage_Catalog_Model_Resource_Eav_Mysql4_Product_Collection';
        if ($request->getParam('_unlcore_std_product_grid') && $collection instanceof $type) {
            $user = Mage::getSingleton('admin/session')->getUser();
            if ($scope = $user->getScope()) {
                $scope = explode(',', $scope);
                $collection->addAttributeToFilter('source_store_view', array('in' => $scope));
            }
        }
    }
    
    /**
     * Save order tax information
     *
     * @param Varien_Event_Observer $observer
     */
    public function salesEventOrderAfterSave(Varien_Event_Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        if (!$order->getConvertingFromQuote() || $order->getAppliedTaxIsSaved()) {
            return;
        }

        $taxes = $order->getAppliedTaxes();
        foreach ($taxes as $row) {
            foreach ($row['rates'] as $tax) {
                if (is_null($row['percent'])) {
                    $baseRealAmount = $row['base_amount'];
                } else {
                    if ($row['percent'] == 0 || $tax['percent'] == 0) {
                        $baseRealAmount = 0;
                    } else {
                        $baseRealAmount = $row['base_amount']/$row['percent']*$tax['percent'];
                    }
                }
                $hidden = (isset($row['hidden']) ? $row['hidden'] : 0);
                $data = array(
                            'order_id'=>$order->getId(),
                            'code'=>$tax['code'],
                            'title'=>$tax['title'],
                            'hidden'=>$hidden,
                            'percent'=>$tax['percent'],
                            'priority'=>$tax['priority'],
                            'position'=>$tax['position'],
                            'amount'=>$row['amount'],
                            'base_amount'=>$row['base_amount'],
                            'process'=>$row['process'],
                            'base_real_amount'=>$baseRealAmount,
                            'sale_amount'=>$row['sale_amount'],
                            'base_sale_amount'=>$row['base_sale_amount']
                            );

                Mage::getModel('sales/order_tax')->setData($data)->save();
            }
        }
        $order->setAppliedTaxIsSaved(true);
    }
    
    /**
     * Daily DB backup (called from cron)
     *
     * @param   Varien_Event_Observer $observer
     * @return  Unl_Core_Model_Observer
     */
    public function generateNightlyBackup($observer)
    {
        try {
            $backupDb = Mage::getModel('backup/db');
            $backup   = Mage::getModel('backup/backup')
                ->setTime(time())
                ->setType('db')
                ->setPath(Mage::getBaseDir("var") . DS . "backups");

            $backupDb->createBackup($backup);
        }
        catch (Exception  $e) { }
        
        return $this;
    }
    
    public function isCustomerAllowedCategory($observer)
    {
        $_cat = $observer->getEvent()->getCategory();
        $helper = Mage::helper('unl_core');
        if (!$helper->isCustomerAllowedCategory($_cat, false)) {
            if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                $message = $helper->__('You are not authorized to access this part of the catalog');
            } else {
                $message = $helper->__('You must be logged in and authorized to access this part of the catalog');
                Mage::app()->getResponse()->setRedirect(Mage::getUrl('customer/account/login'));
            }
            
            Mage::getSingleton('core/session')->addNotice($message);
            Mage::throwException($message);
        }
    }
    
    public function isCustomerAllowedProduct($observer)
    {
        $_prod = $observer->getEvent()->getProduct();
        $helper = Mage::helper('unl_core');
        if (!$helper->isCustomerAllowedProduct($_prod)) {
            if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                $message = $helper->__('You are not authorized to purchase this product');
            } else {
                $message = $helper->__('You must be logged in and authorized to purchase this product');
                Mage::app()->getResponse()->setRedirect(Mage::getUrl('customer/account/login'));
            }
            
            Mage::getSingleton('core/session')->addNotice($message);
            Mage::throwException($message);
        }
    }
    
    public function onAddProductToQuote($observer)
    {
        $_prod = $observer->getEvent()->getProduct();
        $helper = Mage::helper('unl_core');
        if (!$helper->isCustomerAllowedProduct($_prod)) {
            Mage::throwException($helper->__('You are not authorized to purchase the selected product'));
        }
    }
    
    public function isPaymentMethodActive($observer)
    {
        $method = $observer->getEvent()->getMethodInstance();
        $result = $observer->getEvent()->getResult();
        
        if ($method instanceof Mage_Payment_Model_Method_Purchaseorder) {
            /* @var $customerSession Mage_Customer_Model_Session */
            $customerSession = Mage::getSingleton('customer/session');
            if (!$customerSession->isLoggedIn()) {
                $result->isAvailable = false;
                return;
            } else {
                $customer = $customerSession->getCustomer();
                $facStaffModel = Mage::getModel('customer/group')->load('UNL Faculty/Staff', 'customer_group_code');
                if ($customer->getGroupId() !== $facStaffModel->getId()) {
                    $result->isAvailable = false;
                    return;
                }
            }
        }
    }
}

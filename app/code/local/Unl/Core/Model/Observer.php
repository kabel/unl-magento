<?php

class Unl_Core_Model_Observer
{
    protected $_skipWysiwygConfig = false;

    public function prepareWysiwygConfig($observer)
    {
        $config = $observer->getEvent()->getConfig();

        if (!$this->_skipWysiwygConfig) {
            // Add CSS from the Design
            if (!$config->getDisableDesignCss()) {
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

            $config->setBodyId('maincontent');
            $config->setBodyClass('fixed');
            $config->setExtendedValidElements('iframe[align|frameborder|height|longdesc|marginheight|marginwidth|name|scrolling|src|width|class|id|style|title]');

            // Fix bad default values
            $this->_skipWysiwygConfig = true; // prevent infinite observer loop
            $defaultConfig = Mage::getSingleton('cms/wysiwyg_config')->getConfig();
            $this->_skipWysiwygConfig = false;

            if ($config->getData('files_browser_window_url') == $defaultConfig->getData('files_browser_window_url')) {
                $config->setData('files_browser_window_url', Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg_images/index'));
            }

            if ($config->getData('directives_url') == $defaultConfig->getData('directives_url')) {
                $config->setData('directives_url', Mage::getSingleton('adminhtml/url')->getUrl('adminhtml/cms_wysiwyg/directive'));
                $config->setData('directives_url_quoted', preg_quote($config->getData('directives_url')));
            }
        }
    }

    public function correctAdminBlocks($observer)
    {
        $block = $observer->getEvent()->getBlock();

        //Do actions based on block type

        $type = 'Mage_Adminhtml_Block_Catalog_Category_Tree';
        if ($block instanceof $type) {
            if ($child = $block->getChild('store_switcher')) {
                $child->setTemplate('unl/store/switcher/enhanced.phtml');
            }
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
            'Mage_Adminhtml_Block_Report_Product_Sold_Grid',
            'Mage_Adminhtml_Block_Report_Product_Viewed_Grid',
            'Mage_Adminhtml_Block_Report_Customer_Accounts_Grid',
            'Mage_Adminhtml_Block_Report_Customer_Totals_Grid',
            'Mage_Adminhtml_Block_Report_Customer_Orders_Grid',
        );
        foreach ($reportSwitchers as $type) {
            if ($block instanceof $type) {
                $block->getChild('store_switcher')->setTemplate('unl/report/store/switcher.phtml');
                return;
            }
        }

        $reportSwitchers = array(
            'Mage_Adminhtml_Block_Report_Sales_Bestsellers',
            'Mage_Adminhtml_Block_Report_Sales_Sales',
            'Mage_Adminhtml_Block_Report_Sales_Coupons',
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
            if ($fs = $form->getElement('group_fieldset')) {
                $storeModel = Mage::registry('store_data');
                $fs->addField('group_is_hidden', 'select', array(
                        'name'      => 'group[is_hidden]',
                        'label'     => Mage::helper('core')->__('Hidden'),
                        'value'     => $storeModel->getIsHidden(),
                        'options'   => array(
                            0 => Mage::helper('adminhtml')->__('Disabled'),
                            1 => Mage::helper('adminhtml')->__('Enabled')
                        ),
                        'required'  => true,
                        'disabled'  => $storeModel->isReadOnly(),
                ));
            }
            return;
        }

        $type = 'Mage_Adminhtml_Block_Permissions_User_Edit_Tab_Main';
        if ($block instanceof $type) {
             /* @var $form Varien_Data_Form */
            $form = $block->getForm();
            if ($fs = $form->getElement('base_fieldset')) {
                $model = Mage::registry('permissions_user');
                $fs->addField('is_cas', 'select', array(
                    'name' => 'is_cas',
                    'label' => Mage::helper('adminhtml')->__('Is UNL CAS'),
                	'title' => Mage::helper('adminhtml')->__('Is UNL CAS'),
                    'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
                    'value' => $model->getIsCas(),
                    'required' => true
                ), 'username');
            }

            // define field dependencies
            $block->setChild('form_after', $block->getLayout()->createBlock('adminhtml/widget_form_element_dependence')
                ->addFieldMap("user_is_cas", 'cas_enabled')
                ->addFieldMap("user_password", 'password')
                ->addFieldMap("user_confirmation", 'confirmation')
                ->addFieldDependence('password', 'cas_enabled', '0')
                ->addFieldDependence('confirmation', 'cas_enabled', '0')
            );
            return;
        }

        $type = 'Mage_Adminhtml_Block_System_Account_Edit_Form';
        if ($block instanceof $type) {
            $form = $block->getForm();
            $model = Mage::getModel('admin/user')->load(Mage::getSingleton('admin/session')->getUser()->getId());
            if ($model->getIsCas()) {
                $fs = $form->getElement('base_fieldset');
                $fs->removeField('username');
                $fs->removeField('password');
                $fs->removeField('confirmation');
            }
        }

        $advFilterParents = array(
        	'Mage_Adminhtml_Block_Customer',
            'Mage_Adminhtml_Block_Sales_Order'
        );
        foreach ($advFilterParents as $type) {
            if ($block instanceof $type) {
                $block->setTemplate('widget/grid/advanced/container.phtml');
                $block->append('adv.filter');
                return;
            }
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
            if ($scope = Mage::helper('unl_core')->getAdminUserScope()) {
                $collection->addAttributeToFilter('source_store_view', array('in' => $scope));
            }
        }
    }

    /**
     * Event driven salable status setter
     *
     * @param $observer
     */
    public function checkNoSale($observer)
    {
        $product = $observer->getEvent()->getProduct();
        $result  = $observer->getEvent()->getSalable();

        if ($product->getNoSale() !== null) {
            $result->setIsSalable($result->getIsSalable() && !$product->getNoSale());
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
            // clear previous backups
            $collection = Mage::getSingleton('backup/fs_collection');
            foreach ($collection as $backupFile) {
                $backup = Mage::getModel('backup/backup', $backupFile->getData());
                $backup->setType($backupFile->getType());
                $backup->deleteFile();
            }

            $backupDb = Mage::getModel('backup/db');
            $backup   = Mage::getModel('backup/backup')
                ->setTime(time())
                ->setType('db')
                ->setPath(Mage::getBaseDir("var") . DS . "backups");

            $backupDb->createBackup($backup);
        }
        catch (Exception  $e) {
            Mage::logException($e);
        }

        return $this;
    }

    public function autoCleanBlockHtmlCache($schedule)
    {
        $type = 'block_html';
        if (Mage::app()->useCache($type)
            && array_key_exists($type, Mage::app()->getCacheInstance()->getInvalidatedTypes())) {
            Mage::app()->getCacheInstance()->cleanType($type);
        }

        return $this;
    }

    public function isCustomerAllowedCategory($observer)
    {
        $_cat = $observer->getEvent()->getCategory();
        $action = $observer->getEvent()->getControllerAction();
        $result = $observer->getEvent()->getResult();
        $helper = Mage::helper('unl_core');
        if (!$helper->isCustomerAllowedCategory($_cat, true, false, $action)) {
            $result->setPreventDefault(true);
        }
    }

    public function isCustomerAllowedProduct($observer)
    {
        $_prod = $observer->getEvent()->getProduct();
        $action = $observer->getEvent()->getControllerAction();
        $result = $observer->getEvent()->getResult();
        $helper = Mage::helper('unl_core');
        if (!$helper->isCustomerAllowedProduct($_prod, $action)) {
            $result->setPreventDefault(true);
        }
    }

    public function consumeCheckoutMessages($observer)
    {
        $block = $observer->getEvent()->getBlock();
        $type = 'Mage_Core_Block_Messages';
        if ($block instanceof $type) {
            $checkout = Mage::getSingleton('checkout/session');
            if ($checkout->getConsume(true)) {
                $checkout->getMessages(true);
            }
        }
    }

    public function onAfterSetSalesQuoteItemQty($observer)
    {
        $_item = $observer->getEvent()->getItem();
        $helper = Mage::helper('unl_core');
        $helper->checkCustomerAllowedProduct($_item);
        $helper->checkCustomerAllowedProductQty($_item);
    }

    public function onBeforeAdminLoginCheckSSL($observer)
    {
        // even POST requests to the login page should be checked
        $controller = $observer->getEvent()->getControllerAction();
        $request = $controller->getRequest();
        $front = Mage::app()->getFrontController();

        if ($this->_shouldBeSecureAdmin() && !Mage::app()->getStore()->isCurrentlySecure()) {
            $url = Mage::app()->getStore(Mage_Core_Model_App::ADMIN_STORE_ID)->getBaseUrl('link', true).ltrim($request->getPathInfo(), '/');

            $front->getResponse()
                ->setRedirect($url)
                ->sendResponse();
            exit;
        }
    }

    public function onBeforeManageCustomers($observer)
    {
        $controller = $observer->getEvent()->getControllerAction();
        $request = $controller->getRequest();
        if ($request->has('filter') && $request->getParam('filter') == '') {
            Mage::helper('unl_core')->getAdvancedGridFilters('customer', true);
        }
    }

    public function onBeforeSalesOrderGrid($observer)
    {
        $controller = $observer->getEvent()->getControllerAction();
        $request = $controller->getRequest();
        if ($request->has('filter') && $request->getParam('filter') == '') {
            Mage::helper('unl_core')->getAdvancedGridFilters('order', true);
        }
    }

    /**
     * This logic has been copied from the Admin router for security checks
     * @see Mage_Core_Controller_Varien_Router_Admin
     *
     */
    protected function _shouldBeSecureAdmin()
    {
        return substr((string)Mage::getConfig()->getNode('default/web/unsecure/base_url'),0,5)==='https'
            || Mage::getStoreConfigFlag('web/secure/use_in_adminhtml', Mage_Core_Model_App::ADMIN_STORE_ID)
            && substr((string)Mage::getConfig()->getNode('default/web/secure/base_url'),0,5)==='https';
    }
}

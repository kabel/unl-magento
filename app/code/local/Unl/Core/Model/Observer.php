<?php

class Unl_Core_Model_Observer
{
    protected $_skipWysiwygConfig = false;

    /**
     * An <i>adminhtml</i> event observer for the <code>core_block_abstract_prepare_layout_after</code>
     * Provides a hook for blocks that cannot be modified from layout files.
     *
     * @param Varien_Event_Observer $observer
     */
    public function onAfterAdminPrepareLayout($observer)
    {
        $block = $observer->getEvent()->getBlock();

        // Do actions based on block type

        $type = 'Mage_Adminhtml_Block_Catalog_Category_Tree';
        if ($block instanceof $type) {
            if ($child = $block->getChild('store_switcher')) {
                $child->setTemplate('unl/store/switcher/enhanced.phtml');
            }

            return;
        }

        $type = 'Mage_Adminhtml_Block_Customer';
        if ($block instanceof $type) {
            $block->setTemplate('widget/grid/advanced/container.phtml');
            $block->append('adv.filter');

            return;
        }

        $types = array(
            'Mage_Adminhtml_Block_Report_Product_Downloads',
            'Mage_Adminhtml_Block_Report_Product_Lowstock',
            'Mage_Adminhtml_Block_Report_Product_Sold_Grid',
            'Mage_Adminhtml_Block_Report_Product_Viewed_Grid',
            'Mage_Adminhtml_Block_Report_Customer_Accounts_Grid',
            'Mage_Adminhtml_Block_Report_Customer_Totals_Grid',
            'Mage_Adminhtml_Block_Report_Customer_Orders_Grid',
        );

        foreach ($types as $type) {
            if ($block instanceof $type) {
                $block->getChild('store_switcher')->setTemplate('unl/report/store/switcher.phtml');
                return;
            }
        }
    }

    /**
     * A <i>frontend</i> event observer for the <code>core_block_abstract_prepare_layout_after</code> event.
     *
     * @param Varien_Event_Observer $observer
     */
    public function onAfterFrontPrepareLayout($observer)
    {
        $block = $observer->getEvent()->getBlock();

        // Do actions based on block type

        $type = 'Mage_Cms_Block_Page';
        if ($block instanceof $type) {
            /* @var $block Mage_Cms_Block_Page */
            $homeId = Mage::getStoreConfig(Mage_Cms_Helper_Page::XML_PATH_HOME_PAGE);
            $pageId = is_numeric($homeId) ? $block->getPage()->getId() : $block->getPage()->getIdentifier();
            $head = $block->getLayout()->getBlock('head');
            if ($head && $homeId === $pageId) {
                $head->setTitle('');
            }
        }

        $type = 'Mage_CatalogSearch_Block_Layer';
        if ($block instanceof $type) {
            /* @var $block Mage_CatalogSearch_Block_Layer */
            $layer = $block->getLayer();
            $storeId = Mage::app()->getStore()->getId();
            if ($block->getRequest()->getParam('deep')) {
                $layer->getProductCollection()->addAttributeToSelect('source_store_view', 'inner');
            } else {
                $layer->getProductCollection()->addAttributeToFilter('source_store_view', $storeId);
            }
        }
    }

    /**
     * An <i>adminhtml</i> event listener for the <code>core_block_abstract_to_html_before</code>
     * event. It occurs before the call to _beforeToHtml()
     *
     * @param Varien_Event_Observer $observer
     */
    public function beforeCoreBlockToHtml($observer)
    {
        $block = $observer->getEvent()->getBlock();

        $type = 'Mage_Adminhtml_Block_Catalog_Product_Grid';
        if ($block instanceof $type) {
            Mage::register('UNL_PRODUCT_GRID', true, true);
            return;
        }
    }

    /**
     * An <i>adminhtml</i> event listener for the <code>adminhtml_block_html_before</code>
     * event.
     *
     * @param Varien_Event_Observer $observer
     */
    public function onBeforeToHtml($observer)
    {
        $block = $observer->getEvent()->getBlock();

        $type = 'Mage_Adminhtml_Block_System_Store_Edit_Form';
        if ($block instanceof $type) {
            /* @var $form Varien_Data_Form */
            $form = $block->getForm();
            if ($fs = $form->getElement('group_fieldset')) {
                $model = Mage::registry('store_data');
                $fs->addField('group_is_hidden', 'select', array(
                    'name'      => 'group[is_hidden]',
                    'label'     => Mage::helper('core')->__('Hidden'),
                    'value'     => $model->getIsHidden(),
                    'options'   => array(
                        0 => Mage::helper('adminhtml')->__('Disabled'),
                        1 => Mage::helper('adminhtml')->__('Enabled')
                    ),
                    'required'  => true,
                    'disabled'  => $model->isReadOnly(),
                ));
                $fs->addField('group_description', 'textarea', array(
                    'name'      => 'group[description]',
                    'label'     => Mage::helper('core')->__('Description'),
                    'value'     => $model->getDescription(),
                    'required'  => false,
                    'disabled'  => $model->isReadOnly(),
                ));
            }

            return;
        }
    }

    /**
     * A <i>frontend</i> event observer for the custom <code>core_design_check_useragent_exps_before</code>
     * event.
     *
     * @param Varien_Event_Observer $observer
     * @return Unl_Core_Model_Observer
     */
    public function checkNoMobile($observer)
    {
        $result = $observer->getEvent()->getResult();
        $request = Mage::app()->getRequest();
        $action = Mage::app()->getFrontController()->getAction();
        /* @var $action Mage_Core_Controller_Varien_Action */
        if ($action->getFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_START_SESSION)) {
            $session = new Varien_Object();
        } else {
            $session = Mage::getSingleton('core/session');
        }

        if ($request->getParam('mobile') == 'no' && !$session->getDesignNoExps()) {
            $session->setDesignNoExps(true);
        } elseif ($request->getParam('mobile', 'no') != 'no' && $session->getDesignNoExps()) {
            $session->unsDesignNoExps();
        }

        if ($session->getDesignNoExps()) {
            $result->setPreventDefault(true);
        }

        return $this;
    }

    /**
     * An event observer for the <code>core_block_abstract_prepare_layout_before</code>
     * event. It removes the checkout session message, if the session is marked to be
     * consumed.
     *
     * @param Varien_Event_Observer $observer
     */
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

    /**
     * An <i>adminhtml</i> event observer for the <code>cms_wysiwyg_config_prepare</code>
     * event. It extends the default CMS config.
     *
     * @param Varien_Event_Observer $observer
     */
    public function prepareWysiwygConfig($observer)
    {
        $config = $observer->getEvent()->getConfig();

        if (!$this->_skipWysiwygConfig) {
            // Add CSS from the Design
            if (!$config->getDisableDesignCss()) {
                $design = Mage::getModel('core/design_package')->setStore(Mage::app()->getDefaultStoreView());
                $css = array(
                    '/wdn/templates_3.1/css/compressed/base.css',
                    '/wdn/templates_3.1/css/variations/media_queries.css',
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

    /**
     * An <i>adminhtml</i> event observer for the
     * <code>controller_action_predispatch_adminhtml_index_login</code>
     * event.
     * Ensures that even login POST requests are secure if admin is secure.
     *
     * @param Varien_Event_Observer $observer
     */
    public function onBeforeAdminLoginCheckSSL($observer)
    {
        /* @var $controller Mage_Adminhtml_Controller_Action */
        $controller = $observer->getEvent()->getControllerAction();
        $response = $controller->getResponse();

        if ($this->_shouldBeSecureAdmin() && !Mage::app()->getStore()->isCurrentlySecure()) {
            $response->setRedirect($controller->getUrl('*/*/*'));
            $controller->setFlag('', Mage_Adminhtml_Controller_Action::FLAG_NO_DISPATCH, true);
        }
    }

    /**
     * @param Varien_Event_Observer $observer
     * @param string $grid
     */
    protected function _clearAdvfilter($observer, $grid)
    {
        $controller = $observer->getEvent()->getControllerAction();
        $request = $controller->getRequest();
        if ($request->has('filter')) {
            $filters = Mage::helper('unl_core')->getAdvancedGridFilters($grid);
            $frozen = false;

            if ($filters) {
                $frozen = $filters->getData('freeze');
                $filters->unsetData('freeze');
            }

            if ($request->getParam('filter') == '' && !$frozen) {
                Mage::helper('unl_core')->getAdvancedGridFilters($grid, true);
            }
        }
    }

    /**
     * An <i>adminhtml</i> event observer for the
     * <code>controller_action_predispatch_adminhtml_customer_grid</code>
     * event.
     *
     * @param Varien_Event_Observer $observer
     */
    public function onBeforeManageCustomers($observer)
    {
        $this->_clearAdvfilter($observer, 'customer');
    }

    /**
     * An <i>adminhtml</i> event observer for the
     * <code>controller_action_predispatch_adminhtml_sales_order_grid</code>
     * event.
     *
     * @param Varien_Event_Observer $observer
     */
    public function onBeforeSalesOrderGrid($observer)
    {
        $this->_clearAdvfilter($observer, 'order');
    }

    /**
     * A cron method for cleaning the block cache automatically
     *
     * @param string $schedule
     * @return Unl_Core_Model_Observer
     */
    public function autoCleanBlockHtmlCache($schedule)
    {
        $type = 'block_html';
        if (Mage::app()->useCache($type) &&
            array_key_exists($type, Mage::app()->getCacheInstance()->getInvalidatedTypes())
        ) {
            Mage::app()->getCacheInstance()->cleanType($type);
        }

        return $this;
    }

    /**
     * A <i>frontend</i> event observer for the
     * <code>catalog_helper_output_construct</code> event.
     * Adds a handler for the productAttribute output helper.
     *
     * @param Varien_Event_Observer $observer
     */
    public function attachProductHandler($observer)
    {
        /* @var $helper Mage_Catalog_Helper_Output */
        $helper = $observer->getEvent()->getHelper();
        $helper->addHandler('productAttribute', Mage::helper('unl_core/catalog_output'));
    }
}

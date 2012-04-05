<?php

class Unl_Cas_Model_Observer
{
    /**
     * A <i>frontend</i> event handler for the <code>customer_login</code> event
     *
     * @param Varien_Event_Observer $observer
     */
    public function customerLogin($observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        /* @var $customer Mage_Customer_Model_Customer */

        if ($uid = $customer->getData('unl_cas_uid')) {
            Mage::helper('unl_cas')->assignCustomerTags($customer, $uid);
        } else {
            Mage::helper('unl_cas')->revokeSpecialCustomerTags($customer);
        }
    }

    /**
     * A <i>frontend</i> event handler for the <code>customer_logout</code> event
     *
     * @param Varien_Event_Observer $observer
     */
    public function customerLogout($observer)
    {
        $customer = $observer->getEvent()->getCustomer();

        if ($uid = $customer->getData('unl_cas_uid')) {
            $auth = Mage::helper('unl_cas')->getAuth();
            if ($auth->isLoggedIn()) {
                $auth->logout(Mage::getUrl());
            }
        }
    }

    /**
     * An <i>adminhtml</i> event listener for the
     * <code>controller_action_postdispatch_adminhtml_index_logout</code>
     * event.
     *
     * @param Varien_Event_Observer $observer
     */
    public function onAfterAdminLogout()
    {
        /* @var $session Mage_Core_Model_Session_Abstract */
        foreach (array('core/session', 'adminhtml/session', 'unl_cas/session') as $sessionModel) {
            $session = Mage::getSingleton($sessionModel);
            $session->clear();
        }
    }

    /**
     * A <i>frontend</i> event handler for the
     * <code>sales_quote_payment_import_data_before</code>
     * event
     *
     * @param Varien_Event_Observer $observer
     */
    public function onPaymentMethodImport($observer)
    {
        $data     = $observer->getEvent()->getInput();
        $quote    = $observer->getEvent()->getPayment()->getQuote();
        $customer = $quote->getCustomer();

        if ($customer->getId()) {
            if ($data->getMethod() == 'purchaseorder') {
                Mage::helper('unl_cas')->authorizeCostObject($customer);
            } else {
                Mage::helper('unl_cas')->revokeCostObjectAuth($customer);
            }

            $quote->setCustomer($customer);
        }
    }

    /**
     * Event handler for the <code>payment_method_is_active</code> event
     *
     * @param Varien_Event_Observer $observer
     */
    public function isPaymentMethodActive($observer)
    {
        $method = $observer->getEvent()->getMethodInstance();
        $result = $observer->getEvent()->getResult();
        $quote  = $observer->getEvent()->getQuote();

        if ($method instanceof Mage_Payment_Model_Method_Purchaseorder) {
            $customer = $quote->getCustomer();
            if (!$customer->getId()) {
                $result->isAvailable = false;
                return;
            } else {
                $result->isAvailable = Mage::helper('unl_cas')->isCustomerCostObjectAuthorized($customer);
                return;
            }
        }
    }

    /**
     * A <i>frontend</i> event handler for the <code>checkout_submit_all_after</code> event
     *
     * @param Varien_Event_Observer $observer
     */
    public function onCheckoutSubmitAfterAll($observer)
    {
        $quote    = $observer->getEvent()->getQuote();
        $payment  = $quote->getPayment();
        $customer = $quote->getCustomer();

        if ($payment->getMethodInstance() instanceof Mage_Payment_Model_Method_Purchaseorder) {
            Mage::helper('unl_cas')->revokeCostObjectAuth($customer);
        }
    }

    /**
     * An <i>adminhtml</i> event handler for the <code>admin_session_user_login_success</code>
     * event.
     *
     * @param Varient_Event_Observer $observer
     */
    public function tryAdminLdapUpdate($observer)
    {
        $user = $observer->getEvent()->getUser();
        $user->reload();
        if ($user->getIsCas()) {
            try {
                $pfData = new Varien_Object();
                Mage::helper('unl_cas/ldap')->populateLdapData($pfData);
                $changed = false;
                foreach (array('email', 'firstname', 'lastname') as $data) {
                    if ($pfData->hasData($data) && $pfData->getData($data) != $user->getData($data)) {
                        $user->setDataUsingMethod($data, $pfData->getData($data));
                        $changed = true;
                    }
                }
                if ($changed && !$user->userExists()) {
                    $user->save();
                }
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
    }

    /**
     * An <i>adminhtml</i> event handler for the <code>adminhtml_block_html_before</code>
     * event.
     *
     * @param Varient_Event_Observer $observer
     */
    public function onBeforeToHtml($observer)
    {
        $block = $observer->getEvent()->getBlock();

        $type = 'Mage_Adminhtml_Block_Permissions_User_Edit_Tab_Main';
        if ($block instanceof $type) {
            $form = $block->getForm();
            $fs = $form->getElement('base_fieldset');
            $model = Mage::registry('permissions_user');

            $fs->addField('is_cas', 'select', array(
                'name' => 'is_cas',
                'label' => Mage::helper('unl_cas')->__('Is UNL CAS'),
                'title' => Mage::helper('unl_cas')->__('Is UNL CAS'),
                'values' => Mage::getModel('adminhtml/system_config_source_yesno')->toOptionArray(),
                'value' => $model->getIsCas(),
                'required' => true
            ), 'username');

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

            return;
        }
    }
}

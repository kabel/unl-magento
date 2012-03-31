<?php

require_once 'Mage/Catalog/controllers/CategoryController.php';

class Unl_Core_Catalog_CategoryController extends Mage_Catalog_CategoryController
{
    /* Overrides
     * @see Mage_Catalog_CategoryController::_initCatagory()
     * by allowing the init_after event to stop the init without
     * an exception
     */
    protected function _initCatagory()
    {
        Mage::dispatchEvent('catalog_controller_category_init_before', array('controller_action' => $this));
        $categoryId = (int) $this->getRequest()->getParam('id', false);
        if (!$categoryId) {
            return false;
        }

        $category = Mage::getModel('catalog/category')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load($categoryId);

        if (!Mage::helper('catalog/category')->canShow($category)) {
            return false;
        }
        Mage::getSingleton('catalog/session')->setLastVisitedCategoryId($category->getId());
        Mage::register('current_category', $category);
        try {
            $result = new Varien_Object(array('prevent_default' => false));
            Mage::dispatchEvent(
                'catalog_controller_category_init_after',
                array(
                    'category' => $category,
                    'controller_action' => $this,
                    'result' => $result
                )
            );
        } catch (Mage_Core_Exception $e) {
            Mage::logException($e);
            return false;
        }

        if ($result->getPreventDefault()) {
            return false;
        }

        return $category;
    }
}

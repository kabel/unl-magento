<?php
/**
 * Custom Order Downloadable Pdf Items renderer
 *
 * @category   Unl
 * @package    Unl_DownloadablePlus
 * @author     Kevin Abel <kabel2@unl.edu>
 * @see Mage_Downloadable_Model_Sales_Order_Pdf_Items_Abstract
 */
abstract class Unl_DownloadablePlus_Model_Sales_Order_Pdf_Items_Abstract extends Unl_Core_Model_Sales_Order_Pdf_Items_Abstract
{
    /**
     * Downloadable links purchased model
     *
     * @var Mage_Downloadable_Model_Link_Purchased
     */
    protected $_purchasedLinks = null;

    /**
     * Return Purchased link for order item
     *
     * @return Mage_Downloadable_Model_Link_Purchased
     */
    public function getLinks()
    {
        $this->_purchasedLinks = Mage::getModel('downloadable/link_purchased')
            ->load($this->getOrder()->getId(), 'order_id');
        $purchasedItems = Mage::getModel('downloadable/link_purchased_item')->getCollection()
            ->addFieldToFilter('order_item_id', $this->getItem()->getOrderItem()->getId());
        $this->_purchasedLinks->setPurchasedItems($purchasedItems);

        return $this->_purchasedLinks;
    }

    /**
     * Return Links Section Title for order item
     *
     * @return string
     */
    public function getLinksTitle()
    {
        if ($this->_purchasedLinks->getLinkSectionTitle()) {
            return $this->_purchasedLinks->getLinkSectionTitle();
        }
        return Mage::getStoreConfig(Mage_Downloadable_Model_Link::XML_PATH_LINKS_TITLE);
    }

    protected function _appendLinkLines(&$lines)
    {
        $_purchasedItems = $this->getLinks()->getPurchasedItems();

        // draw Links title
        $lines[][] = array(
            'text' => Mage::helper('core/string')->str_split($this->getLinksTitle(), self::DEFAULT_TRIM_OPTION, true, true),
            'font' => 'italic',
            'feed' => self::DEFAULT_OFFSET_OPTION
        );

        // draw Links
        foreach ($_purchasedItems as $_link) {
            $lines[][] = array(
                'text' => Mage::helper('core/string')->str_split($_link->getLinkTitle(), self::DEFAULT_TRIM_VALUE, true, true),
                'feed' => self::DEFAULT_OFFSET_OPTION + self::DEFAULT_OFFSET_PAD
            );
        }
    }
}

<?php

class Unl_Inventory_Model_Audit extends Mage_Core_Model_Abstract
{
    const TYPE_SALE        = 1;
    const TYPE_CREDIT      = 2;
    const TYPE_PURCHASE    = 3;
    const TYPE_ADJUSTMENT  = 4;
    const TYPE_NOTE_ONLY   = 5;

    const TYPE_ADJUSTMENT_SET     = 1;
    const TYPE_ADJUSTMENT_OFFSET  = 2;

    protected function _construct()
	{
		$this->_init('unl_inventory/audit');
	}

	protected function _afterSave()
	{
	    if ($this->hasRegisterFlag()) {
	        $this->_register();
	    }

	    return parent::_afterSave();
	}

	protected function _register()
	{
	    $accounting = Mage::getSingleton('unl_inventory/config')->getAccounting();
	    /* @var $collection Unl_Inventory_Model_Mysql4_Index_Collection */
        $collection = Mage::getResourceModel('unl_inventory/index_collection')
            ->addProductFilter($this->getProductId())
            ->addAccountingOrder($accounting);

        $indexCount = $collection->getSize();
	    switch ($this->getType()) {
	        case self::TYPE_PURCHASE:
	            $this->_adjustStockItem($this->getQty());

	            if ($accounting == Unl_Inventory_Model_Config::ACCOUNTING_AVG && $indexCount) {
	                $index = $collection->getFirstItem();
	                $index->setQtyOnHand($this->getQty() + $index->getQtyOnHand())
	                    ->setAmount($this->getAmount() + $index->getAmount())
	                    ->setProduct($this->getProduct());
	            } else {
    	            $index = Mage::getModel('unl_inventory/index');
    	            $index->setData(array(
    	                'product' => $this->getProduct(),
    	                'product_id' => $this->getProductId(),
    	                'qty_on_hand' => $this->getQty(),
    	                'amount' => $this->getAmount(),
    	                'created_at' => $this->getCreatedAt(),
    	            ));
	            }
	            $index->save();

	            if ($accounting == Unl_Inventory_Model_Config::ACCOUNTING_LIFO || $indexCount == 0) {
	                $index->publish();
	            } elseif ($accounting == Unl_Inventory_Model_Config::ACCOUNTING_AVG) {
	                $this->_setProductCost($index->getCostPerItem());
	            }

	            break;
	        case self::TYPE_ADJUSTMENT:
	            $this->_adjustStockItem($this->getQty());

	            // validate protects against negative on hand
	            if ($indexCount == 0 && $this->getQty() > 0) {
	                $index = Mage::getModel('unl_inventory/index');
    	            $index->setData(array(
    	                'product' => $this->getProduct(),
    	                'product_id' => $this->getProductId(),
    	                'qty_on_hand' => $this->getQty(),
    	                'amount' => 0,
    	                'created_at' => $this->getCreatedAt(),
    	            ));
    	            $index->save()
    	                ->publish();
	            } elseif ($indexCount) {
	                $this->_updateIndexes($collection, $this->getQty());
	            }

	            break;

	        case Unl_Inventory_Model_Audit::TYPE_SALE:
	            if ($indexCount == 0) {
	                break;
	            }
	            $this->_updateIndexes($collection, $this->getQty());
	            break;
	        case Unl_Inventory_Model_Audit::TYPE_CREDIT:
	            if (!$indexCount) {
	                $index = Mage::getModel('unl_inventory/index');
    	            $index->setData(array(
    	                'product_id' => $this->getProductId(),
    	                'qty_on_hand' => $this->getQty(),
    	                'amount' => $this->getAmount(),
    	                'created_at' => $this->getCreatedAt(),
    	            ));
    	            $index->save()
    	                ->publish();
	            } else {
                    $index = $collection->getFirstItem();
	                if ($accounting == Unl_Inventory_Model_Config::ACCOUNTING_AVG
	                    || $index->getCostPerItem() == $this->getCostPerItem()) {
	                    // return to next out
	                    $index->setQtyOnHand($index->getQtyOnHand() + $this->getQty())
	                        ->setAmount($index->getAmount() + $this->getAmount())
	                        ->save();
	                    if ($accounting == Unl_Inventory_Model_Config::ACCOUNTING_AVG) {
	                        $this->_setProductCost($index->getAmount() / $index->getQtyOnHand());
	                    }
	                } else {
	                    // create a next out index and publish
	                    $nextOutDate = new Zend_Date(strtotime($index->getCreatedAt()));
	                    if ($accounting == Unl_Inventory_Model_Config::ACCOUNTING_LIFO) {
	                        $nextOutDate->addSecond(1);
	                    } else {
	                        $nextOutDate->subSecond(1);
	                    }
	                    $newIndex = Mage::getModel('unl_inventory/index');
	                    $index->setData(array(
        	                'product_id' => $this->getProductId(),
        	                'qty_on_hand' => $this->getQty(),
        	                'amount' => $this->getAmount(),
        	                'created_at' => $nextOutDate->toString('YYYY-MM-dd HH:mm:ss'),
        	            ));
        	            $index->save()
        	                ->publish();
    	            }
	            }
	            break;
	        default:
	            break;
	    }

	    return $this;
	}


	/**
	 *
	 * @param float $qty
	 */
	protected function _adjustStockItem($qty)
	{
	    $item = $this->getProduct()->getStockItem();
	    $item->addQty($qty);

	    $verify = $item->verifyStock();
	    if ($item->getIsInStock() != $verify) {
	        $item->setIsInStock($verify)
	            ->setStockStatusChangeAutomaticallyFlag(true);
	    }

	    $item->save();

	    return $this;
	}

	protected function _setProductCost($cost)
	{
	    $product = $this->getProduct();

	    $product->setCostFlag(true)
	        ->setCost($cost)
	        ->save();

        return $this;
	}

	/**
	 *
	 * @param Unl_Inventory_Model_Mysql4_Index_Collection $collection
	 * @param float $qty
	 */
	protected function _updateIndexes($collection, $qty)
	{
	    $republish = false;
        foreach ($collection as $index) {
            $step = $qty;
            $qty += $index->getQtyOnHand();
            if ($qty <= 0) {
                $republish = true;
                $index->isDeleted(true);
                $index->save();
            } else {
                $index->setQtyOnHand($qty);
                $index->setAmount(($index->getAmount() / ($qty - $step)) * $qty);
                $index->save();
                break;
            }
        }

        if ($republish) {
            foreach ($collection as $index) {
                if ($index->isDeleted()) {
                    continue;
                }

                $index->publish();
                break;
            }
        }

        return $this;
	}

	public function validate()
	{
	    $helper = Mage::helper('unl_inventory');

	    switch ($this->getType()) {
	        case self::TYPE_PURCHASE:
	            if ($this->getQty() == 0) {
        	        return $helper->__('A valid Qty is required.');
	            } elseif ($this->getQty() < 0) {
	                $this->setQty(abs($this->getQty()));
	            }

	            if ($this->getAmount() == 0) {
	                return $helper->__('A purchases amount must be greater than 0.');
	            } elseif ($this->getAmount() < 0) {
	                $this->setAmount(abs($this->getAmount()));
	            }
	            break;

	        case self::TYPE_ADJUSTMENT:
	            $this->unsAmount();
                $qty = $helper->getQtyOnHand($this->getProductId());

	            if ($this->getAdjType() == self::TYPE_ADJUSTMENT_SET) {
	                if ($this->getQty() < 0) {
	                    $this->setQty(0);
	                }
	                if ($qty == $this->getQty()) {
	                    return $helper->__('The Qty on Hand is already set to the provided value.');
	                }
	                $this->setQty($this->getQty() - $qty);
	            } else {
    	            if ($this->getQty() == 0) {
            	        return $helper->__('A valid Qty is required.');
    	            }
    	            if (($this->getQty() + $qty) < 0) {
    	                $this->setQty($qty * -1);
    	            }
	            }
	            break;

	        default:
	            return $helper->__('Invalid update type.');
	    }

	    return true;
	}

	/**
	 *
	 * @return Mage_Catalog_Model_Product
	 */
	public function getProduct()
	{
	    if (!$this->hasProduct() && $this->hasProductId()) {
	        $this->setProduct(Mage::getModel('catalog/product')->load($this->getProductId()));
	    }

	    return $this->getData('product');
	}

	public function getCostPerItem()
	{
	    return $this->getAmount() / $this->getQty();
	}
}

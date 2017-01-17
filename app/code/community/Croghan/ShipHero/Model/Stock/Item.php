<?php

class Croghan_ShipHero_Model_Stock_Item extends Mage_CatalogInventory_Model_Stock_Item
{
    /**
     * Initialize resource model
     *
     */
    protected function _construct()
    {
    	parent::_construct();
        //$this->_init('cataloginventory/stock_item');
        //will eventually change this entirely
    }

    /**
     * Retrieve stock identifier
     *
     * @todo multi stock
     * @return int
     */
    public function getStockId()
    {
    	if ( ! $this->_helper()->isModuleEnabled()) {
    		parent::getStockId();
    	}
    	else {
    		return 1; // shiphero warehouse stuff
    	}
    }

    /**
     * Retrieve Product Id data wrapper
     *
     * @return int
     */
    public function getProductId()
    {
        return $this->_getData('product_id');
    }

    /**
     * Load item data by product
     *
     * @param   mixed $product
     * @return  Mage_CatalogInventory_Model_Stock_Item
     */
    public function loadByProduct($product)
    {
        if ($product instanceof Mage_Catalog_Model_Product) {
            $product = $product->getId();
        }
        $this->_getResource()->loadByProductId($this, $product);
        $this->setOrigData();
        return $this;
    }

    /**
     * Subtract quote item quantity
     *
     * @param   decimal $qty
     * @return  Mage_CatalogInventory_Model_Stock_Item
     */
    public function subtractQty($qty)
    {
    	if ( ! $this->_helper()->isModuleEnabled()) {
    		parent::subtractQty($qty);
    	}

        return $this;
    }

    /**
     * Add quantity process
     *
     * @param float $qty
     * @return Mage_CatalogInventory_Model_Stock_Item
     */
    public function addQty($qty)
    {
    	if ( ! $this->_helper()->isModuleEnabled()) {
    		parent::addQty($qty);
    	}

        return $this;
    }

    /**
     * Check quantity
     *
     * @param   decimal $qty
     * @exception Mage_Core_Exception
     * @return  bool
     */
    public function checkQty($qty)
    {
        if (!$this->getManageStock() || Mage::app()->getStore()->isAdmin()) {
            return true;
        }

        if ($this->getQty() - $this->getMinQty() - $qty < 0) {
            switch ($this->getBackorders()) {
                case Mage_CatalogInventory_Model_Stock::BACKORDERS_YES_NONOTIFY:
                case Mage_CatalogInventory_Model_Stock::BACKORDERS_YES_NOTIFY:
                    break;
                default:
                    return false;
                    break;
            }
        }
        return true;
    }

    /**
     * Checking quote item quantity
     *
     * Second parameter of this method specifies quantity of this product in whole shopping cart
     * which should be checked for stock availability
     *
     * @param mixed $qty quantity of this item (item qty x parent item qty)
     * @param mixed $summaryQty quantity of this product
     * @param mixed $origQty original qty of item (not multiplied on parent item qty)
     * @return Varien_Object
     */
    public function checkQuoteItemQty($qty, $summaryQty, $origQty = 0)
    {
        $result = new Varien_Object();
        $result->setHasError(false);

        if (!is_numeric($qty)) {
            $qty = Mage::app()->getLocale()->getNumber($qty);
        }

        /**
         * Check if child product assigned to parent
         */
        $parentItem = $this->getParentItem();
        if ($this->getIsChildItem() && !empty($parentItem)) {
            $typeInstance = $parentItem->getProduct()->getTypeInstance(true);
            $requiredChildrenIds = $typeInstance->getChildrenIds($parentItem->getProductId(), true);
            $childrenIds = array();
            foreach ($requiredChildrenIds as $groupedChildrenIds) {
                $childrenIds = array_merge($childrenIds, $groupedChildrenIds);
            }
            if (!in_array($this->getProductId(), $childrenIds)) {
                $result->setHasError(true)
                    ->setMessage(Mage::helper('cataloginventory')
                        ->__('This product with current option is not available'))
                    ->setQuoteMessage(Mage::helper('cataloginventory')->__('Some of the products are not available'))
                    ->setQuoteMessageIndex('stock');
                return $result;
            }
        }

        /**
         * Check quantity type
         */
        $result->setItemIsQtyDecimal($this->getIsQtyDecimal());

        if (!$this->getIsQtyDecimal()) {
            $result->setHasQtyOptionUpdate(true);
            $qty = intval($qty);

            /**
              * Adding stock data to quote item
              */
            $result->setItemQty($qty);

            if (!is_numeric($qty)) {
                $qty = Mage::app()->getLocale()->getNumber($qty);
            }
            $origQty = intval($origQty);
            $result->setOrigQty($origQty);
        }

        if ($this->getMinSaleQty() && $qty < $this->getMinSaleQty()) {
            $result->setHasError(true)
                ->setMessage(
                    Mage::helper('cataloginventory')->__('The minimum quantity allowed for purchase is %s.', $this->getMinSaleQty() * 1)
                )
                ->setErrorCode('qty_min')
                ->setQuoteMessage(Mage::helper('cataloginventory')->__('Some of the products cannot be ordered in requested quantity.'))
                ->setQuoteMessageIndex('qty');
            return $result;
        }

        if ($this->getMaxSaleQty() && $qty > $this->getMaxSaleQty()) {
            $result->setHasError(true)
                ->setMessage(
                    Mage::helper('cataloginventory')->__('The maximum quantity allowed for purchase is %s.', $this->getMaxSaleQty() * 1)
                )
                ->setErrorCode('qty_max')
                ->setQuoteMessage(Mage::helper('cataloginventory')->__('Some of the products cannot be ordered in requested quantity.'))
                ->setQuoteMessageIndex('qty');
            return $result;
        }

        $result->addData($this->checkQtyIncrements($qty)->getData());
        if ($result->getHasError()) {
            return $result;
        }

        if (!$this->getManageStock()) {
            return $result;
        }

        if (!$this->getIsInStock()) {
            $result->setHasError(true)
                ->setMessage(Mage::helper('cataloginventory')->__('This product is currently out of stock.'))
                ->setQuoteMessage(Mage::helper('cataloginventory')->__('Some of the products are currently out of stock.'))
                ->setQuoteMessageIndex('stock');
            $result->setItemUseOldQty(true);
            return $result;
        }

        if (!$this->checkQty($summaryQty) || !$this->checkQty($qty)) {
            $message = Mage::helper('cataloginventory')->__('The requested quantity for "%s" is not available.', $this->getProductName());
            $result->setHasError(true)
                ->setMessage($message)
                ->setQuoteMessage($message)
                ->setQuoteMessageIndex('qty');
            return $result;
        } else {
            if (($this->getQty() - $summaryQty) < 0) {
                if ($this->getProductName()) {
                    if ($this->getIsChildItem()) {
                        $backorderQty = ($this->getQty() > 0) ? ($summaryQty - $this->getQty()) * 1 : $qty * 1;
                        if ($backorderQty > $qty) {
                            $backorderQty = $qty;
                        }

                        $result->setItemBackorders($backorderQty);
                    } else {
                        $orderedItems = $this->getOrderedItems();
                        $itemsLeft = ($this->getQty() > $orderedItems) ? ($this->getQty() - $orderedItems) * 1 : 0;
                        $backorderQty = ($itemsLeft > 0) ? ($qty - $itemsLeft) * 1 : $qty * 1;

                        if ($backorderQty > 0) {
                            $result->setItemBackorders($backorderQty);
                        }
                        $this->setOrderedItems($orderedItems + $qty);
                    }

                    if ($this->getBackorders() == Mage_CatalogInventory_Model_Stock::BACKORDERS_YES_NOTIFY) {
                        if (!$this->getIsChildItem()) {
                            $result->setMessage(
                                Mage::helper('cataloginventory')->__('This product is not available in the requested quantity. %s of the items will be backordered.', ($backorderQty * 1))
                            );
                        } else {
                            $result->setMessage(
                                Mage::helper('cataloginventory')->__('"%s" is not available in the requested quantity. %s of the items will be backordered.', $this->getProductName(), ($backorderQty * 1))
                            );
                        }
                    } elseif (Mage::app()->getStore()->isAdmin()) {
                        $result->setMessage(
                            Mage::helper('cataloginventory')->__('The requested quantity for "%s" is not available.', $this->getProductName())
                        );
                    }
                }
            } else {
                if (!$this->getIsChildItem()) {
                    $this->setOrderedItems($qty + (int)$this->getOrderedItems());
                }
            }
        }

        return $result;
    }

    /**
     * Add join for catalog in stock field to product collection
     *
     * @param Mage_Catalog_Model_Entity_Product_Collection $productCollection
     * @return Mage_CatalogInventory_Model_Stock_Item
     */
    public function addCatalogInventoryToProductCollection($productCollection)
    {
		if ( ! $this->_helper()->isModuleEnabled()) {
			parent::addCatalogInventoryToProductCollection($productCollection);
		}
		else {
			//shiphero iterate through productCollection
		}

        return $this;
    }

    /**
     * Before save prepare process
     *
     * @return Mage_CatalogInventory_Model_Stock_Item
     */
    protected function _beforeSave()
    {
        if ( ! $this->_helper()->isModuleEnabled()) {
        	parent::_beforeSave();
        }

        return $this;
    }

    /**
     * Retrieve Stock Availability
     *
     * @return bool|int
     */
    public function getIsInStock()
    {
    	if ( ! $this->_helper()->isModuleEnabled()) {
    		return parent::getIsInStock();
    	}
    	else {
    		//shiphero
    		return true;
    	}
    }

    /**
     * Retrieve stock qty whether product is composite or no
     *
     * @return float
     */
    public function getStockQty()
    {
    	if ( ! $this->_helper()->isModuleEnabled()) {
    		return parent::getStockQty();
    	}
    	else {
	        if (!$this->hasStockQty()) {
	            $this->setStockQty(0);  // prevent possible recursive loop

	            // shiphero product response //
	            $response = $this->_helper()->getProduct($this->getProduct());
	            // product, shiphero response, warehouse which will come from store map //
	            $availableStock = $this->_helperItem()->getAvailable ($this->getProduct(), $response, array());
	            

	            $this->setStockQty($availableStock);
	        }
    	}

        return $this->getData('stock_qty');
    }

    /**
     * Reset model data
     * @return Mage_CatalogInventory_Model_Stock_Item
     */
    public function reset()
    {
    	if ( ! $this->_helper()->isModuleEnabled()) {
     		parent::reset();
     	}

        return $this;
    }

    /**
     * Set whether index events should be processed immediately
     *
     * @param bool $process
     * @return Mage_CatalogInventory_Model_Stock_Item
     */
    public function setProcessIndexEvents($process = true)
    {
		if ( ! $this->_helper()->isModuleEnabled()) {
        	parent::setProcessIndexEvents($process);
        }

        return $this;
    }

    /**
     * Callback function which called after transaction commit in resource model
     *
     * @return Mage_CatalogInventory_Model_Stock_Item
     */
    public function afterCommitCallback()
    {
    	if ( ! $this->_helper()->isModuleEnabled()) {
        	parent::afterCommitCallback();
    	}

    	return $this;
    }



    protected function _helper()
    {
    	return Mage::helper('croghan_shiphero');
    }

    protected function _helperItem()
    {
    	return Mage::helper('croghan_shiphero/item');
    }
}

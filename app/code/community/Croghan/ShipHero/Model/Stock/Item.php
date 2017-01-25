<?php

class Croghan_ShipHero_Model_Stock_Item extends Mage_CatalogInventory_Model_Stock_Item
{
    /**
     * _beforeSave
     */
    protected function _beforeSave()
    {
        // always set to minimum quantity available for item status in stock //
        if ($this->_helper()->isModuleEnabled()
        || Mage_Catalog_Model_Product_Type::TYPE_SIMPLE != $this->getTypeID()) {
            $this->setData('qty', $this->getMinQty());
       }
        
       parent::_beforeSave();
    }

    /**
     * _afterLoad
     */
    protected function _afterLoad()
    {
        if ($this->_helper()->isModuleEnabled()) {
            $this->getQty();
        }
        
        parent::_afterLoad();
    }

    /**
     * set stock qty
     */
    public function setQty($_qty)
    {
        if ( ! $this->_helper()->isModuleEnabled()) {
            parent::setQty($_qty);
        }
        else{
            $this->getQty(); // in case qty isn't set
        }
    }

    /**
     * Retrieve Stock Availability
     *
     * @return bool|int
     */
    public function getIsInStock()
    {
        if ( ! $this->_helper()->isModuleEnabled()
        || Mage_Catalog_Model_Product_Type::TYPE_SIMPLE != $this->getTypeID()) {
            return parent::getIsInStock();
        }
        // only execute if module is enabled and type Id is simple //
        else {
            if ( ! $this->getManageStock()) {
                return true;
            }
            return $this->verifyStock();
        }
    }

    /**
     * Retrieve stock qty
     *
     * @note all product types are dependent on inventory of it's simple products.
     * @return float
     */
    public function getQty()
    {
        if ( ! $this->_helper()->isModuleEnabled()
        || Mage_Catalog_Model_Product_Type::TYPE_SIMPLE != $this->getTypeID()) {
            return parent::getQty();
        }
        // only execute if module is enabled and type Id is simple //
        else {
            if ( ! $this->getData('shiphero_qty')) {
                // sku is inconsistent; in either place, sometimes not added to stock_item model //
                if ( ! $this->getSku() && $this->getProduct()){
                    Mage::log("No SKU, product ({$this->getProduct()->getId()}) exists", null, "shiphero.log");
                    $this->setData('sku', $this->getProduct()->getSku());
                }
                elseif ($this->getProductId()) {
                    Mage::log("No SKU, No product, product Id ({$this->getProductId()}) exists", null, "shiphero.log");
                    $this->loadByProduct($this->getProductId());
                }
                else{
                    Mage::log("No SKU, No product nor product Id", null, "shiphero.log");
                }

                // shiphero product response //
                $response = $this->_helper()->getProduct(array('sku' => $this->getSku()));
                //error_log(sprintf("response\n\n%s\n\n", print_r($response,true)));

                // product, shiphero response, warehouse which will come from store map //
                $availableQty = $this->_helperItem()->getAvailable ($this->getSku(), $response, $this->_helperWarehouse()->getWarehouses());
                //error_log(sprintf("availableQty: %s", $availableQty));

                $this->setData('shiphero_qty', (float)$availableQty);
                $this->setData('qty', (float)$availableQty);
            }

            return $this->getData('qty');
        }
    }

    /*
     * _helper
     *
     * return default helper
     */
    protected function _helper()
    {
        return Mage::helper('croghan_shiphero');
    }

    /*
     * _helperItem
     *
     * return item helper
     */
    protected function _helperItem()
    {
        return Mage::helper('croghan_shiphero/item');
    }

    /*
     * _helperWarehouse
     *
     * return warehouse helper
     */
    protected function _helperWarehouse()
    {
        return Mage::helper('croghan_shiphero/warehouse');
    }
}

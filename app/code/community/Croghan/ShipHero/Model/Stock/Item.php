<?php

class Croghan_ShipHero_Model_Stock_Item extends Mage_CatalogInventory_Model_Stock_Item
{
    const EXTRA_CACHE_TAG = 'shiphero_stock'; // don't define CACHE_TAG

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
            if ( ! $this->_loadQtyCache()) { // attempt to load from cache
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

                // set qty //
                $this->setData('qty', (float)$availableQty);

                // save cache //
                $this->_saveQtyCache((float)$availableQty); // save cache //
            }

            // always keep in stock if qty is above min qty //
            if ($this->getData('qty') > $this->getMinQty()) {
                $this->setData('is_in_stock', Mage_CatalogInventory_Model_Stock_Status::STATUS_IN_STOCK);
            }

            return $this->getData('qty');
        }
    }

    /**
     * _saveQtyCache method
     *
     * saves shiphero qty to cache
     */
    protected function _saveQtyCache($_qty)
    {
        $tags = parent::getCacheTags();
        $tags[] = $this->_getStoreCacheTag();
        $tags[] = self::EXTRA_CACHE_TAG;
        $jsonArr = array('qty' => (float)$_qty);

        Mage::app()->saveCache(json_encode($jsonArr), $this->_getStoreCacheTag(), $tags);
    }

    /**
     * _loadQtyCache method
     *
     * tries to load shiphero qty cache
     */
    protected function _loadQtyCache()
    {
        $jsonStr = Mage::app()->loadCache($this->_getStoreCacheTag());

        if ($jsonStr && ($jsonArr = json_decode($jsonStr,true))) {
            $qty = $jsonArr['qty'];
            $this->setData('shiphero_qty', $qty);
            $this->setData('qty', $qty);
        }

        return (FALSE !== $jsonStr ? true : false);
    }

    /**
     * Remove model onject related cache
     *
     * @return Mage_Core_Model_Abstract
     */
    public function cleanModelCache()
    {
        $tags = parent::getCacheTags();
        $tags[] = $this->_getStoreCacheTag();
        $tags[] = self::EXTRA_CACHE_TAG;

        if ($tags !== false) {
            Mage::app()->cleanCache($tags);
            Mage::app()->removeCache($this->_getStoreCacheTag());
        }

        return $this;
    }

    /*
     * _getStoreCacheTag method
     *
     * returns store related cache tag; will double
     */
    protected function _getStoreCacheTag()
    {
        return sprintf("%s_%s_%s", self::EXTRA_CACHE_TAG, Mage::app()->getStore()->getCode(), $this->getProductId());
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

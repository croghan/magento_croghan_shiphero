<?php

class Croghan_ShipHero_Model_Stock_Item extends Mage_CatalogInventory_Model_Stock_Item
{
    /**
     * set stock qty
     *
     */
    public function setQty($_qty)
    {
        $this->getQty(); // in case qty isn't set
    }

    /**
     * Retrieve stock qty
     *
     * @return float
     */
    public function getQty()
    {
        if ( ! $this->getData('shiphero_qty')) {
            // sku is inconsistent; in either place, sometimes not added to stock_item model //
            if ( ! $this->getSku() && $this->getProduct()){
                $this->setData('sku', $this->getProduct()->getSku());
            }
            elseif ($this->getProductId()) {
                $this->loadByProduct($this->getProductId());
            }

            // shiphero product response //
            $response = $this->_helper()->getProduct(array('sku' => $this->getSku()));
            //error_log(sprintf("response\n\n%s\n\n", print_r($response,true)));

            // product, shiphero response, warehouse which will come from store map //
            $availableQty = $this->_helperItem()->getAvailable ($this->getSku(), $response, array());
            //error_log(sprintf("availableQty: %s", $availableQty));

            $this->setData('shiphero_qty', (float)$availableQty);
            $this->setData('qty', (float)$availableQty);
        }

        return $this->getData('qty');
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
}

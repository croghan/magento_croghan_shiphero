<?php

class Croghan_ShipHero_Model_Observer
{
    //catalog_product_is_salable_after
    /*
     * isSalableAfterStockCheck method
     *
     */
    public function isSalableAfterStockCheck ($_observer)
    {
        //@see Mage_Catalog_Model_Product
        /*
        $object = new Varien_Object(array(
            'product'    => $this,
            'is_salable' => $salable
        ));
        Mage::dispatchEvent('catalog_product_is_salable_after', array(
            'product'   => $this,
            'salable'   => $object
        ));
        */
        $event = $_observer->getEvent();
        $product = $event->getProduct();
        $salable = $event->getSalable();
        $isSalable = $salable->getData('is_salable');

        // salable should indicate product type is simple, but just in case check //
        if ($isSalable && Mage_Catalog_Model_Product_Type::TYPE_SIMPLE == $product->getTypeId()) {
            // make sure product has stock object //
            if ( ! $product->getStockItem()) {
                Mage::getModel('cataloginventory/stock_item')->assignProduct($product);
            }

            // if there's stock, it's salable, else not //
            if ($product->getStockItem()->getQty() > $product->getStockItem()->getMinQty()) {
                $product->setData('is_salable', 1);
            }
            else {
                $product->setData('is_salable', 0);   
            }
        }

        return $this;
    }

    /*
     * cleanCacheAfterCheckout method
     *
     * clears cache after a successful sale
     */
    public function cleanCacheAfterCheckout ($_observer)
    {
        //@see Mage_Sales_Model_Service_Quote
        //Mage::dispatchEvent('sales_model_service_quote_submit_after', array('order'=>$order, 'quote'=>$quote));
        $event = $_observer->getEvent();
        $quote = $event->getQuote();

        // traverse items and clear shiphero cache on all //
        foreach ($quote->getAllVisibleItems() as $item) {
            $product = $item->getProduct();

            // shouldn't have to do this, but just in case //
            if ( ! $product->getStockItem()) {
                Mage::getModel('cataloginventory/stock_item')->assignProduct($product);
            }

            // dev log //            
            Mage::log(sprintf("%s::%s: clearing shiphero cache for product sku %s", __CLASS__, __METHOD__, $product->getSku()), null, "shiphero.log");

            $product->getStockItem()->cleanModelCache(); // clean cache
        }

        return $this;
    }

    /*
     * cleanCacheAfterProductSave method
     *
     * clears cache after a product is saved
     */
    public function cleanCacheAfterProductSave ($_observer)
    {
        //@see Mage_Core_Model_Abstract
        //MMage::dispatchEvent($this->_eventPrefix.'_load_after', $this->_getEventData());
        $product = $_observer->getProduct();

        // in most cases will have this //
        if ( ! $product->getStockItem()) {
            Mage::getModel('cataloginventory/stock_item')->assignProduct($product);
        }

        // dev log //
        Mage::log(sprintf("%s::%s: Attemped to clear shiphero cache for product sku %s", __CLASS__, __METHOD__, $product->getSku()), null, "shiphero.log");

        //Not clear cache due to some other extension would save product causes unnecessary cache flushed.
        //$product->getStockItem()->cleanModelCache(); // clean cache

        return $this;
    }
}

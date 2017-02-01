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
            if ($product->getStockItem()->getQty() > 0) {
                $product->setData('is_salable', 1);
            }
            else {
                $product->setData('is_salable', 0);   
            }
        }

        return $this;
    }
}
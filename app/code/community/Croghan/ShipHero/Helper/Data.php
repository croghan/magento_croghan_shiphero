<?php

class Croghan_ShipHero_Helper_Data extends Mage_Core_Helper_Abstract
{
    protected $_api;

    /**
     * Constructor
     * Determine our endpoint url
     */
    public function __construct()
    {}

    /**
     * api method
     *
     * returns a shiphero api model
     */
    public function api()
    {
        if ( ! $this->_api) {
            $this->_api = Mage::getModel('croghan_shiphero/api');
        }

        return $this->_api;
    }

    //DEPRECATED; STILL USED BY STOCK_ITEM
    /*
     * getProduct
     *
     * retrieves product data by sku; no sku returns all products
     * @see http://docs.shipheropublic.apiary.io/#reference/products/remove-kit-component/get-product
     * @example https://api-gateway.shiphero.com/v1/general-api/get-product/?token=ABC123&sku=&page=&count=
     */
    public function getProduct($_fields = array())
    {
        return $this->api()->getProduct($_fields);
    }


}
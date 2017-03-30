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
    }

    /*
     * getProduct
     *
     * retrieves product data by sku; no sku returns all products
     * @see http://docs.shipheropublic.apiary.io/#reference/products/remove-kit-component/get-product
     * @example https://api-gateway.shiphero.com/v1/general-api/get-product/?token=ABC123&sku=&page=&count=
     */
    public function getProduct($_fields = array())
    {
        // generate fields; add defaults to missing //
        $fields = array();
        $fields['sku'] = isset($_fields['sku']) ? $_fields['sku'] : '';
        $fields['page'] = isset($_fields['page']) ? $_fields['page'] : 1;
        $fields['count'] = isset($_fields['count']) ? $_fields['count'] : 10;

        return $this->api()->getProduct($fields);
    }


}
<?php

//@see http://docs.shipheropublic.apiary.io/#reference/products/get-product/get-product
/*
sku: Sku to get (leave out to get all products)
page: Which page to return (used only when sku is not specified). Default 1.
count: How many products to return per page (used only when sku is not specified) *Note that if a product is present in multiple warehouses, each warehouses while grouped under one sku in the response, is considered a separeat product for the purposes of the count. Therefore the results array may be less than the count. Default 50.
*/
class Croghan_ShipHero_Model_Api_Getproduct extends Croghan_ShipHero_Model_Api_Abstract
{
    /*
     * constructor
     */
    public function __construct ()
    {
        $this->_endpoint = 'get-product';
        $this->_endpointType = Croghan_ShipHero_Model_Api::ENDPOINT_TYPE_GET;
    }

    /*
     * generateFields method
     *
     * generates fields from passed data.
     */
    public function generateFields($_data = array())
    {
        $fields['sku'] = isset($_data['sku']) ? $_data['sku'] : '';
        $fields['page'] = isset($_data['page']) ? $_data['page'] : 1;
        $fields['count'] = isset($_data['count']) ? $_data['count'] : 10;

        return $fields;
    }

    /*
     * validateFields method
     *
     * validates passed fields; throws exception on invalid fields
     */
    public function validateFields($_fields)
    {
        // validate
    }
}
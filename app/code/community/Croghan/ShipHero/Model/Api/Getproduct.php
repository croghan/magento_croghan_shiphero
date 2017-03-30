<?php

//@see http://docs.shipheropublic.apiary.io/#reference/products/get-product/get-product
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
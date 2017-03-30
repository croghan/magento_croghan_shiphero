<?php

//@see http://docs.shipheropublic.apiary.io/#reference/webhooks/get-webhooks/get-webhooks
/*
(none)
*/
class Croghan_ShipHero_Model_Api_Getwebhooks extends Croghan_ShipHero_Model_Api_Abstract
{
    /*
     * constructor
     */
    public function __construct ()
    {
        $this->_endpoint = 'get-webhooks';
        $this->_endpointType = Croghan_ShipHero_Model_Api::ENDPOINT_TYPE_GET;
    }

    /*
     * generateFields method
     *
     * generates fields from passed data.
     */
    public function generateFields($_data = array())
    {
        $fields = $_data; // no fields

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
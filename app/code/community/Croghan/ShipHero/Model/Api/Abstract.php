<?php

abstract class Croghan_ShipHero_Model_Api_Abstract implements Croghan_ShipHero_Model_Api_Interface
{
    protected $_api;

    // endpoint //
    protected $_endpoint;
    // endpoint type //
    protected $_endpointType = 0; // see Croghan_ShipHero_Model_Api

    /*
     * getEndpoint method
     *
     * returns endpoint; throws exception if invalid
     */
    public function getEndpoint ()
    {
        $validater = new Zend_Validate_NotEmpty(Zend_Validate_NotEmpty::STRING);

        if ( ! $validater->isValid($this->_endpoint)) {
            throw new Mage_Core_Exception ("%s invalid endpoint '%s'", __METHOD__, $this->_endpoint);
        }

        return $this->_endpoint;
    }

    /*
     * getEndpoint method
     *
     * returns endpoint type; throws exception if invalid
     */
    public function getEndpointType ()
    {
        switch ($this->_endpointType) {
            case Croghan_ShipHero_Model_Api::ENDPOINT_TYPE_GET :
            case Croghan_ShipHero_Model_Api::ENDPOINT_TYPE_POST :
                return $this->_endpointType;
            break; // for uniformity
            default :
                throw new Mage_Core_Exception ("%s invalid endpoint type '%s'", __METHOD__, $this->_endpointType);
            break;
        }
    }
}
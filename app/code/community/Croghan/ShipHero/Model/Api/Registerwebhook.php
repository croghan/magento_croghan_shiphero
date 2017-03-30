<?php

//@see http://docs.shipheropublic.apiary.io/#reference/webhooks/register-webhook
/*
name: Inventory Update , Shipment Update , Order Canceled , Capture Payment , PO Update , Return Update .
url: A valid URL for your webhook.
source:  shopify , magento , bigcommerce , amazon , ebay , api (for custom integrations) .
*/
class Croghan_ShipHero_Model_Api_Registerwebhook extends Croghan_ShipHero_Model_Api_Abstract
{
    const SOURCE_MAGENTO = 'magento';
    const SOURCE_API = 'api';

    const NAME_INVENTORY_UPDATE = 'Inventory Update';
    const NAME_SHIPMENT_UPDATE = 'Shipment Update';
    const NAME_ORDER_CANCELED = 'Order Canceled';
    const NAME_CAPTURE_PAYMENT = 'Capture Payment';
    const NAME_PO_UPDATE = 'PO Update';
    const NAME_RETURN_UPDATE = 'Return Update';

    /*
     * constructor
     */
    public function __construct ()
    {
        $this->_endpoint = 'register-webhook';
        $this->_endpointType = Croghan_ShipHero_Model_Api::ENDPOINT_TYPE_POST;
    }

    /*
     * generateFields method
     *
     * generates fields from passed data.
     */
    public function generateFields($_data = array())
    {
        $fields['name'] = isset($_data['name']) ? $_data['name'] : '';
        $fields['url'] = isset($_data['url']) ? $_data['url'] : Mage::getUrl();
        $fields['source'] = isset($_data['source']) ? $_data['source'] : self::SOURCE_MAGENTO;

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
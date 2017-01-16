<?php

class Croghan_ShipHero_Helper_Data
{
    protected $_url = 'https://api-gateway.shiphero.com/v1/general-api/';
    protected $_token;

    const XML_API_KEY = 'croghan_shiphero/api_key';
    const XML_API_SECRET = 'croghan_shiphero/api_secret';

    // endpoints //
    const GET_PRODUCT = 'get-product'; //http://docs.shipheropublic.apiary.io/#reference/products/get-product/get-product
    const GET_ORDERS = 'get-orders'; //http://docs.shipheropublic.apiary.io/#reference/orders/get-orders/get-orders
    const GET_ORDER = 'get-order'; //http://docs.shipheropublic.apiary.io/#reference/orders/get-orders/get-order

    /**
     * Constructor
     * Determine our endpoint url
     */
    public function __construct()
    {}

    /*
     * _getDefaultFields
     *
     * generate and return default fields used for api calls
     */
    protected function _addDefaultFields ($_fields)
    {
        // get token "token"; for now it appears the "api_key" is the "token" //
        if ( ! $this->_token) {
            if ( ! ($this->_token = Mage::getStoreConfig(self::XML_API_KEY))) {
                Mage::log(sprintf("%s is empty; please add a key to local.xml", self::XML_API_KEY), null, "shiphero.log");
            }
        }

        // only one field for now //
        $_fields['token'] = $this->_token;
        return $_fields;
    }

    /*
     * _getData
     *
     * GET call
     */
    protected function _getData($_url, $_fields)
    {
        // add query string to url //
        $content = $this->_addDefaultFields($_fields);
        $url = sprintf("$_url?%s", http_build_query($content));

        // curl request //
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        // execute and log error if any other http status code than 200 //
        $jsonResponse = curl_exec($curl);
        $httpStatusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if (200 != $httpStatusCode) {
            $curlErrorMsg = curl_error($curl);
            $curlErrorNum = curl_errno($curl);

            Mage::log(
                sprintf("_getData cURL error to url: '%s'\n error: (%s) '%s'\nresponse:'%s'", $_url, $curlErrorNum, $curlErrorMsg, $jsonResponse),
                null,
                'shiphero.log'
            );
        }

        curl_close($curl);

        // decode & return response //
        $response = json_decode($jsonResponse, true);
        $httpStatusCode = isset($response['code']) ? $response['code'] : 500;

        // ShipHero always returns 200; check "code" in response //
        //{"errorCode": "", "Message": "No Token Provided.", "code": "400"}
        if (200 != $httpStatusCode) {
            $shipHeroErrorCode = $response['errorCode'];
            $shipHeroErrorMsg = $response['Message'];
            $shipHeroHttpCode = $response['code'];

            Mage::log(
                sprintf("_getData ShipHero error to url: '%s'\n error: (%s - %s) '%s'\nresponse:'%s'", $_url, $shipHeroHttpCode, $shipHeroErrorCode, $shipHeroErrorMsg, $jsonResponse),
                null,
                'shiphero.log'
            );
        }

        return $response;
    }

    /*
     * _postData
     *
     * POST call
     */
    protected function _postData($_url, $_fields)
    {
        // add query string to url //
        $content = $this->_addDefaultFields($_fields);
        $url = $_url;

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER,
            array("Content-type: application/json")
        );
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $content);

        // execute and log error if any other http status code than 200 //
        $jsonResponse = curl_exec($curl);
        $httpStatusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if (200 != $httpStatusCode) {
            $curlErrorMsg = curl_error($curl);
            $curlErrorNum = curl_errno($curl);

            Mage::log(
                sprintf("_getData cURL error to url: '%s'\n error: (%s) '%s'\nresponse:'%s'", $_url, $curlErrorNum, $curlErrorMsg, $jsonResponse),
                null,
                'shiphero.log'
            );
        }

        curl_close($curl);

        // decode & return response //
        $response = json_decode($jsonResponse, true);
        $httpStatusCode = isset($response['code']) ? $response['code'] : 500;

        // ShipHero always returns 200; check "code" in response //
        //{"errorCode": "", "Message": "No Token Provided.", "code": "400"}
        if (200 != $httpStatusCode) {
            $shipHeroErrorCode = $response['errorCode'];
            $shipHeroErrorMsg = $response['Message'];
            $shipHeroHttpCode = $response['code'];

            Mage::log(
                sprintf("_getData ShipHero error to url: '%s'\n error: (%s - %s) '%s'\nresponse:'%s'", $_url, $shipHeroHttpCode, $shipHeroErrorCode, $shipHeroErrorMsg, $jsonResponse),
                null,
                'shiphero.log'
            );
        }

        return $response;
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
        // build url //
        $url = sprintf("%s%s", $this->_url, self::GET_PRODUCT);

        // generate fields; add defaults to missing //
        $fields = array();
        $fields['sku'] = isset($_fields['sku']) ? $_fields['sku'] : '';
        $fields['page'] = isset($_fields['page']) ? $_fields['page'] : 1;
        $fields['count'] = isset($_fields['count']) ? $_fields['count'] : 10;

        return $this->_getData($url, $fields);
    }
}
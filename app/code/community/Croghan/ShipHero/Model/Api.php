<?php
//@see http://docs.shipheropublic.apiary.io/
class Croghan_ShipHero_Model_Api
{
    protected $_token; // "api token"
    protected $_storeConfig; // store config model

    const GENERAL_API_URL = 'https://api-gateway.shiphero.com/v1/general-api/';

    const ENDPOINT_TYPE_GET = 1;
    const ENDPOINT_TYPE_POST = 2;

    /**
     * Constructor
     * Determine our endpoint url
     */
    public function __construct($_storeConfig = '')
    {
        $this->_storeConfig = Mage::getModel('croghan_shiphero/config', $_storeConfig);
    }

    /*
     * _getDefaultFields
     *
     * generate and return default fields used for api calls
     */
    protected function _addDefaultFields ($_fields)
    {
        // get token "token"; for now it appears the "api_key" is the "token" //
        // only one field for now //
        $_fields['token'] = $this->_storeConfig->getApiKey();

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
        
        //echo sprintf("%s url '%s', content:\n%s", __METHOD__, $url, print_r($content,true));

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
                sprintf("%s cURL error to url: '%s'\n error: (%s) '%s'\nresponse:'%s'", __METHOD__, $_url, $curlErrorNum, $curlErrorMsg, $jsonResponse),
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
            Mage::log(
                sprintf("%s error to url '%s', response: \n %s", __METHOD__, $_url, $jsonResponse),
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

        //echo sprintf("%s url '%s', content:\n%s", __METHOD__, $url, print_r($content,true));

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_HTTPHEADER,
            array("Content-type: application/json")
        );
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($content));

        // execute and log error if any other http status code than 200 //
        $jsonResponse = curl_exec($curl);
        $httpStatusCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        if (200 != $httpStatusCode) {
            $curlErrorMsg = curl_error($curl);
            $curlErrorNum = curl_errno($curl);

            Mage::log(
                sprintf("%s cURL error to url: '%s'\n error: (%s) '%s'\nresponse:'%s'", __METHOD__, $_url, $curlErrorNum, $curlErrorMsg, $jsonResponse),
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
            Mage::log(
                sprintf("%s error to url '%s', response: \n %s", __METHOD__, $_url, $jsonResponse),
                null,
                'shiphero.log'
            );
        }

        return $response;
    }

    /**
     * __call magic caller for endpoint methods
     */
    public function __call($_endpointName, $_data /*arguments; only want [0]*/)
    {
        $endpointModelName = sprintf("croghan_shiphero/api_%s", strtolower($_endpointName));
        $endpointModel = Mage::getModel($endpointModelName);
        $data = (isset($_data[0]) ? $_data[0] : array());

        if ( ! $endpointModel instanceof Croghan_ShipHero_Model_Api_Abstract) {
            throw new Mage_Core_Exception(sprintf("%s invalid endpoint '%s => %s'", __METHOD__, $_endpointName, $_endpointModelName));
        }

        //echo sprintf("%s API call endpoint name '%s' => endpoint model name '%s' with arguments:\n%s", __METHOD__, $_endpointName, $endpointModelName, print_r($data, true));

        Mage::log(
            sprintf("%s API call endpoint name '%s' => endpoint model name '%s' with arguments:\n%s", __METHOD__, $_endpointName, $endpointModelName, print_r($data, true)),
            null,
            'shiphero.log'
        );

        // build url //
        $url = sprintf("%s%s", self::GENERAL_API_URL, $endpointModel->getEndpoint());
        // generate fields //
        $fields = $endpointModel->generateFields ($data);
        // validate fields //
        $endpointModel->validateFields ($fields);
        // response //

        switch ($endpointModel->getEndpointType()) {
            case self::ENDPOINT_TYPE_GET :
                return $this->_getData($url, $fields);
            break;
            case self::ENDPOINT_TYPE_POST :
                return $this->_postData($url, $fields);
            break;
            default :
                // already throws an exception on invalid endpoint type //
            break;
        }


    }
}
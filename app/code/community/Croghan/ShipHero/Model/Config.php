<?php

class Croghan_ShipHero_Model_Config extends Varien_Object // from system config
{
    const XML_CONFIG_PATH = 'cataloginventory/croghan_shiphero';
    const XML_API_KEY = 'api_key';
    const XML_API_SECRET = 'api_secret';
    const XML_WAREHOUSES = 'warehouses';

    /**
     * Constructor
     *
     * sets store config data; uses config helper
     */
    public function __construct($_storeCode = '')
    {
        $storeCode = (is_string($_storeCode) ? $_storeCode : ''); // needs to be a string
        $storeConfigData = $this->_getStoreConfig($storeCode);

        $this->_setStoreConfig ($storeConfigData);
    }

    /**
     * _getStoreConfig
     *
     * retrieves shiphero store config; used by config model constructor
     */
    protected function _getStoreConfig($_storeCode = '')
    {
        // check for valid store //
        $store = Mage::app()->getStore($_storeCode);

        if ( ! $store) {
            throw new Mage_Core_Exception(sprintf("%s::%s invalid store code '%s'", __CLASS__, __METHOD__, $_storeCode));
        }
        $storeCode = $store->getCode();

        return Mage::getStoreConfig(self::XML_CONFIG_PATH, $storeCode);
    }

    /**
     * _setStoreConfig method
     *
     * sets & validates shiphero config data; if store saved with empty data will throw errors.
     */
    protected function _setStoreConfig ($_storeConfigData)
    {
        $validater = new Zend_Validate_NotEmpty(Zend_Validate_NotEmpty::STRING);

        // validate data //
        if ( ! $validater->isValid($_storeConfigData[self::XML_API_KEY])) {
            throw new Mage_Core_Exception (sprintf("%s::%s no api key", __CLASS__, __METHOD__));
        }

        if ( ! $validater->isValid($_storeConfigData[self::XML_API_SECRET])) {
            throw new Mage_Core_Exception (sprintf("%s::%s no api secret", __CLASS__, __METHOD__));
        }

        if ( ! $validater->isValid($_storeConfigData[self::XML_WAREHOUSES])) {
            throw new Mage_Core_Exception (sprintf("%s::%s no warehouses", __CLASS__, __METHOD__));
        }

        // set data //
        $this->setApiKey($_storeConfigData[self::XML_API_KEY]);
        $this->setApiSecret($_storeConfigData[self::XML_API_SECRET]);
        $this->setWarehouses($_storeConfigData[self::XML_WAREHOUSES]);
    }

    /*
     * specific getters
     */
    public function getApiKey()
    {
        return Mage::helper('core')->decrypt($this->getData('api_key'));
    }

    public function getApiSecret()
    {
        return Mage::helper('core')->decrypt($this->getData('api_secret'));
    }

    public function getWarehouses()
    {
        return explode(',', $this->getData('warehouses'));
    }
}
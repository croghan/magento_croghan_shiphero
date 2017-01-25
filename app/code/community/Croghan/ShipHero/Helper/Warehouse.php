<?php

class Croghan_ShipHero_Helper_Warehouse extends Mage_Core_Helper_Abstract
{
    const XML_WAREHOUSES = 'croghan_shiphero/warehouses';

    /**
     * Constructor
     * Determine our endpoint url
     */
    public function __construct()
    {}

    /*
     * getWarehouses
     *
     * returns warehouses for store code; default is current store
     */
    public function getWarehouses ($_storeCode = null)
    {
        $storeCode = $_storeCode ? $_storeCode : Mage::app()->getStore()->getCode(); // null will get current store, want code for logging
        $storeWarehouseMap = array();
        $storeWarehouseStr = null;

        // get warehouses //
        $storeWarehouseStr = Mage::getStoreConfig(self::XML_WAREHOUSES, $_storeCode);
        $storeWarehouseMap = $storeWarehouseStr ? explode(',', $storeWarehouseStr) : array();

        // log quantity hits //
        Mage::log(
            sprintf("%s::%s: store '%s' warehouses are '%s'", __CLASS__, __METHOD__, $storeCode, implode(',', $storeWarehouseMap)),
            null,
            'shiphero.log'
        );

        return $storeWarehouseMap;
    }
}
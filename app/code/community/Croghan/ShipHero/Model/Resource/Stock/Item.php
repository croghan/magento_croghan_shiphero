<?php

class Croghan_ShipHero_Model_Resource_Stock_Item extends Mage_CatalogInventory_Model_Resource_Stock_Item
{
    /**
     * Retrieve select object and join it to product entity table to get type ids
     *
     * @param string $field
     * @param mixed $value
     * @param Mage_CatalogInventory_Model_Stock_Item $object
     * @return Varien_Db_Select
     */
    protected function _getLoadSelect($field, $value, $object)
    {
        $select = parent::_getLoadSelect($field, $value, $object)
            //add entity table for sku//
            ->join(array('e' => Mage::getSingleton('core/resource')->getTableName("catalog/product")),
                'product_id=e.entity_id',
                array('sku')
            );

        return $select;
    }
}

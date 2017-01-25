<?php

class Croghan_ShipHero_Helper_Item
{
    /* response
    [
     "Message" => "success",
     "code" => "200",
     "products" => [
       "results" => [
         [
           "sku" => "shiphero123",
           "kit_components" => [],
           "warehouses" => [
             [
               "warehouse" => "Primary",
               "available" => "100",
               "on_hand" => "100",
               "allocated" => "0",
               "backorder" => "0",
             ],
           ],
           "build_kit" => 0,
           "value" => "1.00",
           "kit" => 0,
         ],
       ],
     ],
   ]
   */

   public function getAvailable ($_product, $_response, $_warehouse = array())
   {
        $sku = $_product;

        if (is_object($sku)) {
            $sku = $sku->getSku();
        }

        $availableQty = 0;
        $skuFound = true; // log products out of sync from shiphero

        if (isset ($_response['products']['results'])){
            foreach ($_response['products']['results'] as $productResult) {
                if ($sku == $productResult['sku'] 
                &&  isset ($productResult['warehouses'])) {

                    $skuFound = true;

                    foreach ($productResult['warehouses'] as $warehouseResult) {
                        // add warehouse logic //
                        $availableQty += $warehouseResult['available'];
                    }
                }
            }
        }

        // log quantity hits //
        Mage::log(
            sprintf("%s::%s: sku '%s', availabilityQty '%s'", __CLASS__, __METHOD__, $sku, $availableQty),
            null,
            'shiphero.log'
        );

        // force log database out of sync //
        if ( ! $skuFound) {
            Mage::log(
                sprintf("%s::%s: sku '%s', not found in ShipHero database", __CLASS__, __METHOD__, $sku),
                null,
                'shiphero.log',
                true
            );
        }

        return $availableQty;
   }
}
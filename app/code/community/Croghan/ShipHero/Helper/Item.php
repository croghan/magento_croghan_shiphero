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

        if (isset ($_response['products']['results'])){
            foreach ($_response['products']['results'] as $productResult) {
                if ($sku == $productResult['sku'] 
                &&  isset ($productResult['warehouses'])) {
                    foreach ($productResult['warehouses'] as $warehouseResult) {
                        // add warehouse logic //
                        $availableQty += $warehouseResult['available'];
                    }
                }
            }
        }

        Mage::log(
            sprintf("getAvailable: sku '%s', availabilityQty '%s'", $sku, $availableQty),
            null,
            'shiphero.log'
        );

        return $availableQty;
   }
}
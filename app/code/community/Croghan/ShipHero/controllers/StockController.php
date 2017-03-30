<?php
//don't use as example; temp
//http://docs.shipheropublic.apiary.io/#reference/webhooks/inventory-update-webhook-url/inventory-update-webhook-url
/*
REQUEST (theirs)
{
  "test": "0",
  "account_id": "100",
  "inventory": [
    {
      "sku": "bz-123",
      "inventory": 10,
      "backorder_quantity": 0
    }
  ]
}
RESPONSE (ours)
{
  "code": "200",
  "Message": "Success"
}
*/
class Croghan_ShipHero_StockController extends Mage_Core_Controller_Front_Action
{
    protected $_responseBody;

    /*
     * preDispatch method
     *
     * decode JSON posted and set as params;
     */
    public function preDispatch()
    {
        parent::preDispatch();

        //Zend_Controller_Request_Http does not decode JSON
        if ( ! $this->getRequest()->getParams()) {
            $this->getRequest()->setParams(
                Mage::helper('core')->jsonDecode($this->getRequest()->getRawBody())
            );
        }
    }

    /*
     * postDispatch method
     *
     * add response content
     */
    public function postDispatch()
    {
        $this->getResponse()->setHeader('Content-Type', 'application/json', true);
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($this->_responseBody));

        parent::postDispatch();
    }

    /*
     * indexAction method
     *
     * generate and return default fields used for api calls
     */
    public function indexAction ()
    {
        $inventoryInfoArr = (array) $this->getRequest()->getParam('inventory');

        $this->_responseBody = array(
                'code' => "404",
                'Message' => "Missing"
            );

        //assuming only 1, but is an array; "_response" handling is strange, but not worrying about it at the moment
        foreach ($inventoryInfoArr as $inventoryInfo) {

            $sku = $inventoryInfo['sku'];
            $productId = Mage::getModel('catalog/product')->getIdBySku($sku);

            if ($productId) {
                $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId);
                $stockItem->cleanModelCache();

                $this->_responseBody = array(
                        'code' => "200",
                        'Message' => "Success"
                    );

                Mage::log(
                    sprintf("%s product SKU inventory cleaned: '%s'", __METHOD__, $sku),
                    null,
                    'shiphero.log'
                );
            }
            else {
                Mage::log(
                    sprintf("%s product SKU could not be found: '%s'\n response:\n%s", __METHOD__, $sku, $this->getRequest()->getRawBody()),
                    null,
                    'shiphero.log'
                );
            }
        }
    }
}

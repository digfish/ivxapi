<?php

use PHPUnit\Framework\TestCase;

class InvoiceXpressApiItemTest extends TestCase {

    var $per_page;
    var $invoice_id;

    public function setUp() {
//        echo "> " . __METHOD__ . "\n";
        MyInvoiceXpressApi::init(IVX_DOMAIN,IVX_TOKEN);
        ini_set('error_reporting', E_ALL);
        ini_set('display_errors', 'stdout');
        $this->per_page = 20;
        ob_start();
    }

    public function tearDown() {
//        echo  __METHOD__ . " >\n";
        echo ob_get_clean();
    }




    public function testCreateItem() {
        $rand = rand(1,100);
        $method = 'items.create';
        $api = new MyInvoiceXpressApi($method);

        $new_item = array(
            'item' => array(
                'name' => "PHP_DEVEL_$rand",
                'description' => 'PHP Programming',
                'unit_price' => "100.0"
            )
        );

        $response = $api->invoke($new_item);

        $this->assertTrue($api->success());

        if (isset($response['item'])) {
            $new_created_item = $response['item'];
            return $new_created_item;
        }
    }


    /**
     @depends testCreateItem
    */
    public function testGetItem($item) {

        $api = new MyInvoiceXpressApi('items.get');
        $response = $api->invoke(array('id' => $item['id'] ));
        $this->assertTrue($api->success());
    }


    /**
     @depends testCreateItem
    */
    public function testUpdateItem($item) {
       
        $rand =  rand( 2, 1000);
        $api = new MyInvoiceXpressApi('items.update');
        $item['unit_price'] = "$rand.0";
        $response = $api->invoke(array(
            'item' => $item
            )
        );

        $this->assertTrue($api->success());
    }

    /**
     @depends testCreateItem
    */
    public function testDeleteItem($item) {
       // $this->markTestSkipped();
        $api = new MyInvoiceXpressApi('items.delete');
        $response = $api->invoke(array('id' => $item['id'] ));
        $this->assertTrue($api->success());
    }

    public function testGetItems() {
        $method = 'items.list';
        $api = new MyInvoiceXpressApi($method);

        $response = $api->invoke();
        $count = count($response['items']);
        echo "Number of elements in response: $count\n";
        $this->assertTrue($api->success());
    }



}

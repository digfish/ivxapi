<?php

use PHPUnit\Framework\TestCase;

class InvoiceXpressApiClientTest extends TestCase {

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



    /****************************************************************************************
     * Client Methods tests
    *****************************************************************************************/

    public function testCreateClient() {
    	$rand =  rand( 2, 1000);
    	$new_client = array('client' => array(
    		'name' => "Joaquim dos Testes $rand" ,
    		'code' => "JTESTES_$rand",
    		'email' => 'jtestes@testes.org',
    		'language' => 'pt'
    		));
    	$api = new MyInvoiceXpressApi('clients.create');
    	
    	$response = $api->invoke($new_client);
    	$this->assertTrue($api->success());
    	if (isset($response['client'])) {
    		$created_client = $response['client'];
    		return $created_client;
    	}
    }

    public function testGetClients() {

        $api = new MyInvoiceXpressApi('clients.list');
       

        $response = $api->invoke(array('per_page' => $this->per_page));
        $count = count($response);

        echo "Number of clients in response: $count\n";

        //print_r($response);

        $last_client = end($response['clients']);

        echo "Last client id: " .  $last_client['id'];

        $this->assertTrue($api->success());
    }

    /**
     @depends testCreateClient
    */
    public function testGetClient($client) {

        $api = new MyInvoiceXpressApi('clients.get');
        $response = $api->invoke(array('id' => $client['id'] ));
        //print_r($response);
        $this->assertTrue($api->success());
    }

    /**
     @depends testCreateClient
    */
    public function testUpdateClient($client) {
        $this->markTestSkipped();
        $rand =  rand( 2, 1000);
        $api = new MyInvoiceXpressApi('clients.update');
       // $api->withDebug();
        $response = $api->invoke(array(
            'client' => array(
                'nome' => "Joao dos Testes $rand",
                'id' => $client['id'],
                'code' => $client['code'] ,
                'email' => $client['email'],
                'language' => $client['language']
                )
            )
        );
        $this->assertTrue($api->success());
    }

    /**
     @depends testCreateClient
    */
    public function testClientFindByName($client) {
        $api = new MyInvoiceXpressApi('clients.find-by-name');
        $response = $api->invoke(array('client_name'=>$client['name']));
        $this->assertTrue($api->success());
    }

    /**
     @depends testCreateClient
    */
    public function testClientFindByCode($client) {
        $api = new MyInvoiceXpressApi('clients.find-by-code');
        $response = $api->invoke(array('client_code'=>$client['code']));
        $this->assertTrue($api->success());
    }


    /**
     @depends testCreateClient
    */
    public function testClientGetInvoices($client) {
        $api = new MyInvoiceXpressApi('clients.invoices');
        $response = $api->invoke(array('id'=>$client['id']));
        $this->assertTrue($api->success());
    }


}

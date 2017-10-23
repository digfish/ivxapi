<?php

use PHPUnit\Framework\TestCase;

class InvoiceXpressApiTest extends TestCase {

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


    public function testCreateInvoice() {
        $api = new MyInvoiceXpressApi('invoices.create');
        $today = date('d/m/Y');
        $tomorrow = date('d/m/Y',strtotime('tomorrow'));

        $new_invoice = array('invoice' => array(
            'date' => $today,
            'due_date' => $tomorrow,
            'client' => array(
                'name' => 'Maria Testes',
                'email' => 'mariatestes@testes.org'
            ),
            'items' => array(
                array(

                    'name' => 'REG_DOMINIO',
                    'quantity' => 3,
                    'unit_price' => 40.0
                )
            )
        ));

        $response = $api->invoke($new_invoice);

        $new_invoice_id = $response['invoice']['id'];

        $this->assertTrue($api->success());

        return $new_invoice_id;
    }


    public function testListInvoices() {

        $method = "invoices.list";
        $api = new MyInvoiceXpressApi($method);
        $response = $api->invoke(array('per_page' => $this->per_page));
        $count = count($response);

        echo "Number elements in response: $count\n";

//        var_dump(end($response['invoices'])); die();

        if (count($response) > 0 ) {
            $last_invoice = end($response['invoices']);
            $this->invoice_id = $last_invoice['id'];
        }

        print("Last invoice id:" . $this->invoice_id);

        $this->assertTrue($api->success());

        return $this->invoice_id;
    }


    /**
     * @depends testListInvoices
     * 
     */
     public function testSendEmail($invoice_id) {
        $this->markTestSkipped();
        
        $api = new MyInvoiceXpressApi('invoices.email-document');

        $args = array(
                'message' => 
                    array('client' => array(
                        'email' => 'pescadordigital@gmail.com',
                        'save' => '0'
                    ),
                    "subject" => 'Invoice from Digfish programações',
                    "body" => 'Many thanks for contracting us!'
                ),
                'id' => $invoice_id
            );
        $response = $api->invoke($args);

        $this->assertTrue($api->success());
    }

    /**
     * @depends testCreateInvoice
     */
    public function testGetInvoice($invoice_id) {
        $api = new MyInvoiceXpressApi('invoices.get');
        $invoice = $api->invoke(array('id' => $invoice_id));
        $this->assertTrue($api->success());
    }

    public function testGetInvoiceThatShouldGiveErrorAsHtml () {
        $this->markTestSkipped();
        $api = new MyInvoiceXpressApi('invoices.get');
        $invoice = $api->invoke();
        $this->assertFalse($api->success());
    }

    public function testListInvoiceReceipts() {
        $method = "invoice_receipts.list";
        $api = new MyInvoiceXpressApi($method);
        $response = $api->invoke(array('per_page' => $this->per_page));
        $this->assertTrue($api->success());

    }

    /**
     * @depends testListInvoices
     */
    public function testInvoiceChangeState($invoice_id) {
        $this->markTestSkipped();
        $api = new MyInvoiceXpressApi('invoices.change-state');
        $response = $api->invoke(array(
            'id' => $invoice_id,
            'invoice' => array('state' => 'settled') 
            )
        );

        $this->assertTrue($api->success());
    }

    /**
     * @depends testCreateInvoice
     */
    public function testGetRelatedDocuments($invoice_id) {
        $api = new MyInvoiceXpressApi('document.related_documents');
        
        $related_documents = $api->invoke(array('id' => $invoice_id));
        $this->assertTrue($api->success());
    }


    /**
     * @depends testCreateInvoice
     */
    public function testUpdateInvoice($invoice_id) {
    	$today = date('d/m/Y');
        $tomorrow = date('d/m/Y',strtotime('tomorrow'));

        $api = new MyInvoiceXpressApi('invoices.update');
        $invoice_to_update = array(
//        	'id' => $invoice_id,
        	'invoice' => array(
	        	'id' => $invoice_id,
        		'tax_exemption' => 'M01',
	            'date' => $today,
	            'due_date' => $tomorrow,
	            'client' => array(
	                'name' => 'João dos Testes',
	                'email' => 'joaotestes@testes.org'
	            ),
            'items' => array(
                array(
                    'name' => 'REG_DOMINIO',
                    'quantity' => 1,
                    'unit_price' => 20.0
                )
            )
        ));

        $invoice_updated = $api->invoke($invoice_to_update);
        $this->assertTrue($api->success());
    }



}

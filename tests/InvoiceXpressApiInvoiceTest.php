<?php

use PHPUnit\Framework\TestCase;

class InvoiceXpressInvoiceApiTest extends TestCase {

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


    /**
     * @depends testCreateInvoice
     */
    public function testGetInvoice($invoice_id) {
        $api = new MyInvoiceXpressApi('invoices.get');
        $response = $api->invoke(array('id' => $invoice_id));
        $this->assertTrue($api->success());

        if (isset($response['invoice'])) {
            return $response['invoice'];
        }
    }



    /**
     * @depends testGetInvoice
     */
    public function testInvoiceMarkAsFinal($invoice) {
        echo "=> Mark the invoice as final!\n";
        $api = new MyInvoiceXpressApi('invoices.change-state');
        $changed_invoice = array(
            'id' => $invoice['id'],
            'state' => 'finalized'
        );
        $response = $api->invoke(array('invoice' => $changed_invoice));
        //var_dump($response); die();

        $this->assertTrue($api->success());
        return $invoice['id'];
    }

    /**
     * @depends testInvoiceMarkAsFinal
     */
    public function testInvoiceMarkAsSettled($invoice_id) {
        $this->markTestSkipped();
        echo "=> Now mark as settled...\n";
        $api = new MyInvoiceXpressApi('invoices.change-state');
        $changed_invoice = array(
            'id' => $invoice_id,
            'state' => 'settled'
        );
        $response = $api->invoke(array('invoice' =>$changed_invoice));
        $this->assertTrue($api->success());
        return $invoice_id;
    }

    /**
     * @depends testInvoiceMarkAsFinal
     */
    public function testInvoiceMarkAsCancelled($invoice_id) {
        echo "=> Now mark as canceled\n";
        $api = new MyInvoiceXpressApi('invoices.change-state');
        //$api->withDebug();
        $changed_invoice = array(
            'id' => $invoice_id,
            'state' => 'canceled' 
        );
        $response = $api->invoke(array('invoice' =>$changed_invoice));
        $this->assertTrue($api->success());
        return $invoice_id;
    }



    /**
     * @depends testGetInvoice
     */
    public function testInvoiceMarkAsDeleted($invoice) {
        $this->markTestSkipped();
        echo "=> Now mark as deleted\n";
        $invoice_id = $invoice['id'];
        echo " => Now mark as deleted!\n";
        $api = new MyInvoiceXpressApi('invoices.change-state');
//        $api->withDebug();
        $changed_invoice = array(
            'id' => $invoice_id,
            'state' => 'deleted'            
        );
        $response = $api->invoke(array('invoice' => $changed_invoice));
        $this->assertTrue($api->success());
        return $invoice_id;
    }


    /**
     * @depends testCreateInvoice
     */
    public function testUpdateInvoice($invoice_id) {
        $this->markTestSkipped();
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

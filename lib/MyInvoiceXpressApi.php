<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once __DIR__ . "/../config/ivx_config.php";

/**
 * Description of MyInvoiceXpressApi
 * Child class of InvoiceXpressRequest
 * wraps the InvoiceXpressRequest class
 *
 * @author digfish
 * @version 2.0
 * @
 */

use digfish\ivx_api2_php\InvoiceXpressRequest;

class MyInvoiceXpressApi extends InvoiceXpressRequest {

	 static $isInitialized;

   static public function init($domain,$token) {
		parent::init($domain,$token);
		self::$isInitialized = TRUE;
   }

    public function __construct($method='') {
      parent::__construct($method);
    }

    private function parseHtmlResponse($html,$lang='pt') {
      $matches = array();
      preg_match_all("/<h3 class=\"$lang\">(.+)<\/h3>\s+<h4>(.+)<\/h4>/m",$html,$matches);
      return $matches;
      //return (isset($matches[1]) && isset($matches[2]))? array($matches[1],$matches[2]) : NULL;
    }
    
    public function invoke($args=array(),$debug = FALSE) {
      
        $id = -1;

        if (array_key_exists('id',$args) ) {
            $id = $args['id'];
            unset($args['id']);
        }

        $head = current($args);
        $key = key($args);

//        var_dump($key); die();
//        echo "Head of args is  $key";

        if (isset($head['id']) ) {
          $id = $head['id'];
          unset($args[$key]['id']);
        }

        $this->set_args($args);

        echo "** Invoking {$this->_method} **\n ";
     
        $json = json_decode($this->getGeneratedJson());
        
        if ($this->_debug && !empty($json)) {
          echo "\n";
          echo "JSON input:--------------------\n";
          if (is_object($json)) { 
             print($this->getGeneratedJson());
          };
          echo "\n-----------------------------\n";
        }

        
        $response = NULL;

        if (isset($id) && $id != -1) {
          $this->request($id);
        } else  {
          $this->request();
        }


       if ($this->success() === TRUE) {
         if ($this->_debug) {
           echo "SUCESS!\n";
          } 
        } else {
          echo "Something got wrong!\n";
          echo "ERRORS:\n";
          print_r( $this->getError());
          if ($this->_was_html_response)  {
            $regex_matches = $this->parseHtmlResponse($this->getRawResponse(),'en');        
            echo "\nParsed HTML:";
            print_r ($regex_matches);
          }
       }

       $raw_response = $this->getRawResponse();
          

      if ($this->_debug) {
        echo "\nRESPONSE:\n";

        // print, in case of debugging only the 255 first characters in order to avoid terminal flooding
        if (strlen(trim($raw_response)) == 0) {
          echo "!EMPTY!\n";
        } else {
         echo ("'"+substr($raw_response,0,255) + "'" );
       }
      }

    return $this->getJsonResponse();
  }



}

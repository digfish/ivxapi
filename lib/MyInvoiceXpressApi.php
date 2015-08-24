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
 * Implements a cache (from Laravel) that avoids always asking
 * wraps the InvoiceXpressRequest class
 * the InvoiceExpress servers for the same information
 *
 * @author digfish
 */
class MyInvoiceXpressApi extends InvoiceXpressRequest {




    var $withCache;
    var $cacheTimeout = 10; // 10 minute cache duration
	static $isInitialized;

   static public function init($domain,$token) {
		parent::init($domain,$token);
		self::$isInitialized = TRUE;
   }



    public function __construct($method) {

        //self::init(IVX_DOMAIN,IVX_TOKEN);

        parent::__construct($method);
        $this->withCache = FALSE;
    }

    public function withCache($_cache) {
      $this->withCache = $_cache;
    }

    public function putInCache($key,$value) {
      if ($this->withCache) {
        Cache::put($key,$value,$this->cacheTimeout);
      }
    }

    public function invoke($args=array(),$debug = FALSE) {
  $id = -1;

        if (array_key_exists('id',$args)) {
            $id = $args['id'];
            unset($args['id']);
        }


        $this->post($args);


         echo "** Invoking {$this->_method} **\n ";
      if ($this->_debug) {
        var_dump($this);
         echo "\n";



        echo "XML input:--------------------\n";

        echo ($this->getGeneratedXML());

        echo "\n-----------------------------\n";
      }


        $return = NULL;
        $response = NULL;


          if ($this->withCache == FALSE || !Cache::has($this->_method)) {
            if ($this->_debug) {
              echo "There is no {$this->_method} in the cache, retrieving it...\n";
            }

            if (isset($id) && $id != -1) {
              $return = $this->request($id);
            } else {
              $return = $this->request();
            }

            $response = $this->getResponse();

             if ($this->success()) {
               if ($this->_debug) {
                echo "SUCESS!\n";
              }
              if ($this->withCache == FALSE) {
                 $this->putInCache($this->_method,$response);
                }

             } else {
                 echo "Something got wrong!\n";
                echo "ERRORS:\n";
                var_dump( $this->getError());
            }

          } else {
            if ($this->_debug) {
              echo "There is something on the cache, retreiving it...";
             echo "Retrieving {$this->_method} from the cache!";
          }
            $response = Cache::get($this->_method);
          }

          if ($this->_debug) {
            echo "\nRETURN:\n";

            var_dump($return);

          echo "\nRESPONSE:\n";

          // print, in case of debugging only the 10000 first characters in order to avoid terminal flooding

         echo (substr(print_r($response,TRUE),0,10000) );
       }

        return $response;
}



}

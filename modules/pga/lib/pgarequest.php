<?php

 /** This class is instantiated everytime any notification or
  * order processing commands are received.
  *
  * It has a SendReq function to post different requests to the Server
  * Send functions are provided for most of the commands that are supported
  * by the server for this code
  */
  define('ENTER', "\r\n");
  define('DOUBLE_ENTER', ENTER.ENTER);
  // Max size of the Messsage string
  define('MESSAGE_LENGTH', 254);
  define('REASON_LENGTH', 140);

  /**
   * Send requests to the server to perform different actions
   */
  class PGARequest {
    var $merchant_id;
    var $name;
    var $merchant_key;
    var $currency;
    var $trans_type;
    var $server_url;
    /*var $schema_url;
    var $base_url;
    var $checkout_url;
    var $checkout_diagnose_url;
    var $request_url;
    var $request_diagnose_url;
    var $merchant_checkout;
    var $proxy = array();

    var $certPath='';*/
    var $log;

    /**
     * @param string $id the merchant id
	 * @param string $name the merchant name
     * @param string $key the merchant key
     * @param string $server_type the server type of the server to be used, one
     *                            of 'sandbox' or 'production'.
     *                            defaults to 'sandbox'
     * @param string $currency the currency of the items to be added to the cart
     *                         , as of now values can be 'USD' or 'GBP'.
     *                         defaults to 'USD'
	 * @param string $trans_type the transaction type of the payment gateway.
	 *                         as of now values can be 'Auth', 'PreAuth',
	 *                         'PostAuth', 'Void', 'Credit'
	 *                         defaults to 'Auth'
     */
    function PGARequest($id, $name, $key, $server_type="sandbox", $currency="USD", $trans_type="Auth") {
      $this->merchant_id = $id;
      $this->merchant_name = $name;
      $this->merchant_key = $key;
      $this->currency = $currency;
      $this->trans_type = $trans_type;

      if(strtolower($server_type) == "sandbox") {
        $this->server_url = "https://testsanalpos.est.com.tr/servlet/cc5ApiServer";
      } else {
        $this->server_url = "https://www.sanalakpos.com/servlet/cc5ApiServer";
      }

      //$this->schema_url = "http://checkout.google.com/schema/2";
      //$this->base_url = $this->server_url . "api/checkout/v2/";
      //$this->request_url = $this->base_url . "request/Merchant/" . $this->merchant_id;
      //$this->merchant_checkout = $this->base_url . "merchantCheckout/Merchant/" . $this->merchant_id;

      ini_set('include_path', ini_get('include_path').PATH_SEPARATOR.'.');
      require_once('pgalog.php');
      $this->log = new PGALog();
    }

    function SetLogFiles($errorLogFile, $messageLogFile, $logLevel=L_ERR_RQST) {
      $this->log = new PGALog($errorLogFile, $messageLogFile, $logLevel);
    }

	/**
     * Submit a SetCertificatePath request.
     *
     * @param string $certPath The name of a file holding one or more certificates
     *  to verify the peer with
     */
    function SetCertificatePath($certPath) {
      //$this->certPath = $certPath;
    }

    /**
     * Submit a server-to-server request.
     *
     * @param string $xml_cart the cart's xml
     * @param int/bool $timeout timeout for the Server2Server execution
     * @param bool $die whether to die() or not after performing the request,
     *                  defaults to true
     *
     * @return array with the returned http status code (200 if OK) in index 0
     *               and the redirect url returned by the server in index 1
     */
    function SendServer2ServerCart($xml_cart, $timeout=false, $die=true) {
      list($status, $body) = $this->SendReq($this->server_url,
                   $this->GetAuthenticationHeaders(), $xml_cart, $timeout);

      ini_set('include_path', ini_get('include_path').PATH_SEPARATOR.'.');
      require_once('xml/pga_xmlparser.php');

      $xml_parser = new pga_XmlParser($body);
      $root = $xml_parser->GetRoot();
      $data = $xml_parser->GetData();
      return array($root, $data);

      /*if($status != 200 ) {
          return array($status, $body);
      } else {
        ini_set('include_path', ini_get('include_path').PATH_SEPARATOR.'.');
        require_once('xml/pga_xmlparser.php');

        $xml_parser = new pga_XmlParser($body);
        $root = $xml_parser->GetRoot();
        $data = $xml_parser->GetData();

        //$this->log->logRequest("Redirecting to: ".
        //                $data[$root]['redirect-url']['VALUE']);

        //header('Location: ' . $data[$root]['redirect-url']['VALUE']);
        if($die) {
          die($data[$root]['redirect-url']['VALUE']);
        } else {
          return array(200, $data);
        }
      }*/
    }

    /**
     * Send a <charge-order> command to the server
     *
     * @param string $google_order the google id of the order
     * @param double $amount the amount to be charged, if empty the whole
     *                       amount of the order will be charged
     *
     * @return array the status code and body of the response
     */
    function SendChargeOrder($google_order, $amount='') {
      $postargs = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                  <charge-order xmlns=\"".$this->schema_url.
                  "\" google-order-number=\"". $google_order. "\">";
      if ($amount != '') {
        $postargs .= "<amount currency=\"" . $this->currency . "\">" .
                      $amount . "</amount>";
      }
      $postargs .= "</charge-order>";
      return $this->SendReq($this->request_url,
                   $this->GetAuthenticationHeaders(), $postargs);
    }

    /**
     * Send a <refund-order> command to the server
     *
     * @param string $google_order the google id of the order
     * @param double $amount the amount to be refunded, if empty the whole
     *                       amount of the order will be refunded
     * @param string $reason the reason why the refund is taking place
     * @param string $comment a comment about the refund
     *
     * @return array the status code and body of the response
     */
    function SendRefundOrder($google_order, $amount, $reason,
                              $comment='') {
      $postargs = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                  <refund-order xmlns=\"".$this->schema_url.
                  "\" google-order-number=\"". $google_order. "\">" .
                  "<reason>". $reason . "</reason>";
      if($amount!=0) {
        $postargs .= "<amount currency=\"" . $this->currency . "\">".
                      htmlentities($amount)."</amount>";
      }
      $postargs .= "<comment>". htmlentities($comment) . "</comment>
                  </refund-order>";
      return $this->SendReq($this->request_url,
                   $this->GetAuthenticationHeaders(), $postargs);
    }

    /**
     * Send a <cancel-order> command to the server
     *
     * @param string $google_order the google id of the order
     * @param string $reason the reason why the order is being cancelled
     * @param string $comment a comment about the cancellation
     *
     * @return array the status code and body of the response
     */
    function SendCancelOrder($google_order, $reason, $comment) {
      $postargs = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                  <cancel-order xmlns=\"".$this->schema_url.
                  "\" google-order-number=\"". $google_order. "\">
                  <reason>".
                    (substr(htmlentities(strip_tags($reason)),0,GOOGLE_REASON_LENGTH)) .
                  "</reason>
                  <comment>".
                    (substr(htmlentities(strip_tags($comment)),0,GOOGLE_REASON_LENGTH)) .
                  "</comment>
                  </cancel-order>";
      return $this->SendReq($this->request_url,
                   $this->GetAuthenticationHeaders(), $postargs);
    }

    /**
     * Send an <add-tracking-data> command to the server, which
     * will associate a shipper's tracking number with an order.
     *
     * @param string $google_order the google id of the order
     * @param string $carrier the carrier, valid values are "DHL", "FedEx",
     *                        "UPS", "USPS" and "Other"
     * @param string $tracking_no the shipper's tracking number
     *
     * @return array the status code and body of the response
     */
    function SendTrackingData($google_order, $carrier,
                                              $tracking_no) {
      $postargs = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                  <add-tracking-data xmlns=\"". $this->schema_url .
                  "\" google-order-number=\"". $google_order . "\">
                  <tracking-data>
                  <carrier>". htmlentities($carrier) . "</carrier>
                  <tracking-number>". $tracking_no . "</tracking-number>
                  </tracking-data>
                  </add-tracking-data>";
      return $this->SendReq($this->request_url,
                   $this->GetAuthenticationHeaders(), $postargs);
    }

    /**
     * Send an <add-merchant-order-number> command to the
     * server, which will associate a merchant order number with an order
     *
     * @param string $google_order the google id of the order
     * @param string $merchant_order the merchant id of the order
     *
     * @return array the status code and body of the response
     */
    function SendMerchantOrderNumber($google_order,
                                              $merchant_order, $timeout=false) {
      $postargs = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                  <add-merchant-order-number xmlns=\"". $this->schema_url .
                  "\" google-order-number=\"". $google_order . "\">
                  <merchant-order-number>" . $merchant_order .
                  "</merchant-order-number>
                  </add-merchant-order-number>";
      return $this->SendReq($this->request_url,
                   $this->GetAuthenticationHeaders(), $postargs, $timeout);
    }

    /**
     * Send a <send-buyer-message> command to the
     * server, which will place a message in the customer's Google Checkout
     * account
     *
     * @param string $google_order the google id of the order
     * @param string $message the message to be sent to the customer
     * @param string $send_email whether Google should send an email to
     *                           the buyer, use "true" or"false",
     *                           defaults to "true"
     *
     * @return array the status code and body of the response
     */
    function SendBuyerMessage($google_order, $message,
                                      $send_mail="true", $timeout=false) {
      $postargs = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                  <send-buyer-message xmlns=\"". $this->schema_url .
                  "\" google-order-number=\"". $google_order . "\">
                  <message>" .
            (substr(htmlentities(strip_tags($message)),0,GOOGLE_MESSAGE_LENGTH))
               . "</message>
                  <send-email>" . strtolower($send_mail) . "</send-email>
                  </send-buyer-message>";
      return $this->SendReq($this->request_url,
                   $this->GetAuthenticationHeaders(), $postargs, $timeout);
    }

    /**
     * Send a <process-order> command to the
     * server, which will update an order's fulfillment state from NEW to
     * PROCESSING
     *
     * @param string $google_order the google id of the order
     *
     * @return array the status code and body of the response
     */
    function SendProcessOrder($google_order) {
      $postargs = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                  <process-order xmlns=\"".$this->schema_url    .
                  "\" google-order-number=\"". $google_order. "\"/> ";
      return $this->SendReq($this->request_url,
                   $this->GetAuthenticationHeaders(), $postargs);
    }

    /**
     * Send a <deliver-order> command to the server, which
     * will update an order's fulfillment state from either NEW or PROCESSING
     * to DELIVERED
     *
     * @param string $google_order the google id of the order
     * @param string $carrier the carrier, valid values are "DHL", "FedEx",
     *                        "UPS", "USPS" and "Other"
     * @param string $tracking_no the shipper's tracking number
     * @param string $send_email whether Google should send an email to
     *                           the buyer, use "true" or"false",
     *                           defaults to "true"
     *
     * @return array the status code and body of the response
     */
    function SendDeliverOrder($google_order, $carrier="",
                              $tracking_no="", $send_mail = "true") {
      $postargs = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                  <deliver-order xmlns=\"". $this->schema_url .
                  "\" google-order-number=\"". $google_order . "\">";
      if($carrier != "" && $tracking_no != "") {
         $postargs .= "<tracking-data>
                  <carrier>". htmlentities($carrier) . "</carrier>
            <tracking-number>". htmlentities($tracking_no) . "</tracking-number>
                  </tracking-data>";
      }
      $postargs .= "<send-email>". strtolower($send_mail) . "</send-email>
                  </deliver-order>";
      return $this->SendReq($this->request_url,
                   $this->GetAuthenticationHeaders(), $postargs);
    }

    /**
     * Send a <archive-order> command to the
     * server, which removes an order from the merchant's Merchant Center Inbox
     *
     * @param string $google_order the google id of the order
     *
     * @return array the status code and body of the response
     */
    function SendArchiveOrder($google_order) {
      $postargs = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                  <archive-order xmlns=\"".$this->schema_url.
                  "\" google-order-number=\"". $google_order. "\"/>";
      return $this->SendReq($this->request_url,
                   $this->GetAuthenticationHeaders(), $postargs);
    }

    /**
     * Send a <archive-order> command to the
     * server, which returns a previously archived order to the merchant's
     * Merchant Center Inbox
     *
     * @param string $google_order the google id of the order
     *
     * @return array the status code and body of the response
     */
    function SendUnarchiveOrder($google_order) {
      $postargs = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                  <unarchive-order xmlns=\"".
                  $this->schema_url."\" google-order-number=\"".
                  $google_order. "\"/>";
      return $this->SendReq($this->request_url,
                   $this->GetAuthenticationHeaders(), $postargs);
    }

/**
 * Ship items API Commands
 *
 */

    /**
     * Send a <ship-items> command to the server,
     *
     * @param string $google_order the google id of the order
     * @param array $items_list a list of GoogleShipItem classes.
     * @param string $send_email whether Google should send an email to
     *                           the buyer, use "true" or"false",
     *                           defaults to "true"
     *
     * @return array the status code and body of the response
     */

    function SendShipItems($google_order, $items_list=array(), $send_mail="true") {
      $postargs = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                  <ship-items xmlns=\"". $this->schema_url .
                  "\" google-order-number=\"". $google_order . "\">" .
                  "<item-shipping-information-list>\n";
      foreach($items_list as $item) {
        $postargs .= "<item-shipping-information>
                      <item-id>
                        <merchant-item-id>" . $item->merchant_item_id . "</merchant-item-id>
                     </item-id>\n";

        if(count($item->tracking_data_list)) {
          $postargs .= "<tracking-data-list>\n";
          foreach($item->tracking_data_list as $tracking_data) {
            $postargs .= "<tracking-data>
                            <carrier>". htmlentities($tracking_data['carrier']) . "</carrier>
                            <tracking-number>". $tracking_data['tracking-number'] . "</tracking-number>
                          </tracking-data>\n";
          }
          $postargs .= "</tracking-data-list>\n";
        }
        $postargs .= "</item-shipping-information>\n";
      }
      $postargs .= "</item-shipping-information-list>\n" .
                  "<send-email>". strtolower($send_mail) . "</send-email>
                  </ship-items>";
      return $this->SendReq($this->request_url,
                   $this->GetAuthenticationHeaders(), $postargs);

    }

    /**
     * Send a <backorder-items> command to the server
     *
     * @param string $google_order the google id of the order
     * @param array $items_list a list of GoogleShipItem classes.
     * @param string $send_email whether Google should send an email to
     *                           the buyer, use "true" or"false",
     *                           defaults to "true"
     *
     * @return array the status code and body of the response
     */
    function SendBackorderItems($google_order, $items_list=array(), $send_mail="true") {
      $postargs = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                  <backorder-items xmlns=\"".
                  $this->schema_url."\" google-order-number=\"".
                  $google_order. "\">";
      $postargs .= "<item-ids>";
      foreach($items_list as $item) {
        $postargs .= "<item-id>
                        <merchant-item-id>" . $item->merchant_item_id . "</merchant-item-id>
                      </item-id>";
      }
      $postargs .= "</item-ids>";
      $postargs .= "<send-email>". strtolower($send_mail) . "</send-email>
                    </backorder-items>";
      return $this->SendReq($this->request_url,
                   $this->GetAuthenticationHeaders(), $postargs);
    }

    /**
     * Send a <cancel-items> command to the server
     *
     * @param string $google_order the google id of the order
     * @param array $items_list a list of GoogleShipItem classes.
     * @param string $reason the reason why the refund is taking place
     * @param string $comment a comment about the refund
     * @param string $send_email whether Google should send an email to
     *                           the buyer, use "true" or"false",
     *                           defaults to "true"
     *
     * @return array the status code and body of the response
     */
    function SendCancelItems($google_order, $items_list=array(), $reason,
                                               $comment='', $send_mail="true") {
      $postargs = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                  <cancel-items xmlns=\"".
                  $this->schema_url."\" google-order-number=\"".
                  $google_order. "\">";
      $postargs .= "<item-ids>";
      foreach($items_list as $item) {
        $postargs .= "<item-id>
                        <merchant-item-id>" . $item->merchant_item_id . "</merchant-item-id>
                      </item-id>";
      }
      $postargs .= "</item-ids>";
      $postargs .= "<send-email>". strtolower($send_mail) . "</send-email>
                  <reason>".
                    (substr(htmlentities(strip_tags($reason)),0,GOOGLE_REASON_LENGTH)) .
                  "</reason>
                  <comment>".
                    (substr(htmlentities(strip_tags($comment)),0,GOOGLE_REASON_LENGTH)) .
                  "</comment>
                  </cancel-items>";
      return $this->SendReq($this->request_url,
                   $this->GetAuthenticationHeaders(), $postargs);
    }

    /**
     * Send a <return-items> command to the server
     *
     * @param string $google_order the google id of the order
     * @param array $items_list a list of GoogleShipItem classes.
     * @param string $send_email whether Google should send an email to
     *                           the buyer, use "true" or"false",
     *                           defaults to "true"
     *
     * @return array the status code and body of the response
     */
    function SendReturnItems($google_order, $items_list=array(), $send_mail="true") {
      $postargs = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                  <return-items xmlns=\"".
                  $this->schema_url."\" google-order-number=\"".
                  $google_order. "\">";
      $postargs .= "<item-ids>";
      foreach($items_list as $item) {
        $postargs .= "<item-id>
                        <merchant-item-id>" . $item->merchant_item_id . "</merchant-item-id>
                      </item-id>";
      }
      $postargs .= "</item-ids>";
      $postargs .= "<send-email>". strtolower($send_mail) . "</send-email>
                    </return-items>";
      return $this->SendReq($this->request_url,
                   $this->GetAuthenticationHeaders(), $postargs);
    }

    /**
     * Send a <reset-items-shipping-information> command to the server
     *
     * @param string $google_order the google id of the order
     * @param array $items_list a list of GoogleShipItem classes.
     * @param string $send_email whether Google should send an email to
     *                           the buyer, use "true" or"false",
     *                           defaults to "true"
     *
     * @return array the status code and body of the response
     */
    function SendResetItemsShippingInformation($google_order, $items_list=array(), $send_mail="true") {
      $postargs = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
                  <reset-items-shipping-information xmlns=\"".
                  $this->schema_url."\" google-order-number=\"".
                  $google_order. "\">";
      $postargs .= "<item-ids>";
      foreach($items_list as $item) {
        $postargs .= "<item-id>
                        <merchant-item-id>" . $item->merchant_item_id . "</merchant-item-id>
                      </item-id>";
      }
      $postargs .= "</item-ids>";
      $postargs .= "<send-email>". strtolower($send_mail) . "</send-email>
                    </reset-items-shipping-information>";
      return $this->SendReq($this->request_url,
                   $this->GetAuthenticationHeaders(), $postargs);
    }

    /**
     * @access private
     */
    function GetAuthenticationHeaders() {
      $headers = array();
      //$headers[] = "Authorization: Basic ".base64_encode(
      //    $this->merchant_id.':'.$this->merchant_key);
      $headers[] = "Content-Type: application/xml; charset=UTF-8";
      $headers[] = "Accept: application/xml; charset=UTF-8";
      return $headers;
    }
    /**
     * Set the proxy to be used by the connections to the outside
     *
     * @param array $proxy Array('host' => 'proxy-host', 'port' => 'proxy-port')
     *
     */
    function SetProxy($proxy=array()) {
      if(is_array($proxy) && count($proxy)) {
        $this->proxy['host'] = $proxy['host'];
        $this->proxy['port'] = $proxy['port'];
      }
    }

    /**
     * @access private
     */
    function SendReq($url, $header_arr, $postargs, $timeout=false) {
      // Get the curl session object
	  $session = curl_init();
      $this->log->LogRequest(Tools::maskPaymentDetails($postargs));
      // Set the POST options.
      curl_setopt($session, CURLOPT_URL, $url);
      curl_setopt($session, CURLOPT_SSL_VERIFYHOST, true);
      //curl_setopt($session, CURLOPT_POST, true);
      //curl_setopt($session, CURLOPT_HTTPHEADER, $header_arr);
      curl_setopt($session, CURLOPT_POSTFIELDS, $postargs);
      //curl_setopt($session, CURLOPT_HEADER, true);
      curl_setopt($session, CURLOPT_RETURNTRANSFER, true);

      /*if(!empty($this->certPath) && file_exists($this->certPath)) {
        curl_setopt($session, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($session, CURLOPT_CAINFO, $this->certPath);
      }
      else {*/
        curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);
      //}

      /*if(is_array($this->proxy) && count($this->proxy)) {
         curl_setopt($session, CURLOPT_PROXY, $this->proxy['host'] . ":" . $this->proxy['port']);
      }*/
      if($timeout != false) {
        curl_setopt($session, CURLOPT_TIMEOUT, $timeout);
      }
      // Do the POST and then close the session
      $response = curl_exec($session);
      if (curl_errno($session)) {
        $this->log->LogError(curl_error($session));
        $this->log->LogResponse($response);
        return array("CURL_ERR", curl_error($session));
      } else {
        curl_close($session);
      }

      //$heads = $this->parse_headers($response);
      //$body = $this->get_body_x($response);

      // Get HTTP Status code from the response
      //$status_code = array();
      //preg_match('/\d\d\d/', $heads[0], $status_code);

      // Check for errors
      /*switch( $status_code[0] ) {
        case 200:
          // Success*/
            $this->log->LogResponse($response);
            return array(200, $response);
          /*break;
        case 503:
            $this->log->LogError($response);
            return array(503, htmlentities($body));
          break;
        case 403:
            $this->log->LogError($response);
            return array(403, htmlentities($body));
          break;
        case 400:
            $this->log->LogError($response);
            return array(400, htmlentities($body));
          break;
        default:
            $this->log->LogError($response);
            return array("ERR", htmlentities($body));
          break;
      }*/
    }

// Private functions
// Function to get HTTP headers,
// will also work with HTTP 200 status added by some proxy servers
    /**
     * @access private
     */
    function parse_headers($message) {
      $head_end = strpos($message, DOUBLE_ENTER);
      $headers = $this->get_headers_x(substr($message,0,
                                             $head_end + strlen(DOUBLE_ENTER)));
      if(!is_array($headers) || empty($headers)){
        return null;
      }
      if(!preg_match('%[HTTP/\d\.\d] (\d\d\d)%', $headers[0], $status_code)) {
        return null;
      }
      switch( $status_code[1] ) {
        case '200':
          $parsed = $this->parse_headers(substr($message,
                                             $head_end + strlen(DOUBLE_ENTER)));
          return is_null($parsed)?$headers:$parsed;
        break;
        default:
          return $headers;
        break;
      }
    }

    /**
     * @access private
     */
    function get_headers_x($heads, $format=0) {
      $fp = explode(ENTER, $heads);
      foreach($fp as $header){
        if($header == "") {
          $eoheader = true;
          break;
        } else {
          $header = trim($header);
        }

        if($format == 1) {
          $key = array_shift(explode(':',$header));
          if($key == $header) {
            $headers[] = $header;
          } else {
            $headers[$key]=substr($header,strlen($key)+2);
          }
          unset($key);
        } else {
         $headers[] = $header;
        }
      }
      return $headers;
    }

    /**
     * @access private
     */
    function get_body_x($heads){
      $fp = explode(DOUBLE_ENTER, $heads, 2);
      return $fp[1];
    }
  }

class GoogleShipItem {
  var $merchant_item_id;
  var $tracking_data_list;
  var $tracking_no;

  function GoogleShipItem($merchant_item_id, $tracking_data_list=array()) {
    $this->merchant_item_id = $merchant_item_id;
    $this->tracking_data_list = $tracking_data_list;
  }

  function AddTrackingData($carrier, $tracking_no) {
    if($carrier != "" && $tracking_no != "") {
      $this->tracking_data_list[] = array('carrier' => $carrier,
                                          'tracking-number' => $tracking_no);
    }
  }
}
?>

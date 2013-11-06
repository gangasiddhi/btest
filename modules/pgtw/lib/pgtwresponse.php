<?php

/* This class is instantiated everytime any notification or
  * order processing commands are received.
  *
  */


  /**
   * Handles the response to notifications sent by the server.
   */
  class PGTWResponse {
    var $merchant_id;
    var $merchant_name;
    var $merchant_key;
    var $schema_url;

    var $log;
    var $response;
    var $root='';
    var $data=array();
    var $xml_parser;

    /**
     * @param string $id the merchant id
     * @param string $name the merchant name
     * @param string $key the merchant key
     */
    function PGTWResponse($id=null, $name=null, $key=null) {
      $this->merchant_id = $id;
      $this->merchant_name = $name;
      $this->merchant_key = $key;
      //$this->schema_url = "http://checkout.google.com/schema/2";
      ini_set('include_path', ini_get('include_path').PATH_SEPARATOR.'.');
      require_once('pgtwlog.php');
      $this->log = new PGTWLog('', '', L_OFF);
    }

    /**
     * @param string $id the merchant id
     * @param string $name the merchant name
     * @param string $key the merchant key
     */
    function SetMerchantAuthentication($id, $name, $key){
      $this->merchant_id = $id;
      $this->merchant_name = $name;
      $this->merchant_key = $key;
    }

    function SetLogFiles($errorLogFile, $messageLogFile, $logLevel=L_ERR_RQST) {
      $this->log = new PGTWLog($errorLogFile, $messageLogFile, $logLevel);
    }

    /**
     * Verifies that the authentication sent by server matches the
     * merchant id and key
     *
     * @param string $headers the headers from the request
     */
    function HttpAuthentication($headers=null, $die=true) {
      if(!is_null($headers)) {
        $_SERVER = $headers;
      }
      if(isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) {
        $compare_mer_id = $_SERVER['PHP_AUTH_USER'];
        $compare_mer_key = $_SERVER['PHP_AUTH_PW'];
      }
  //  IIS Note::  For HTTP Authentication to work with IIS,
  // the PHP directive cgi.rfc2616_headers must be set to 0 (the default value).
      else if(isset($_SERVER['HTTP_AUTHORIZATION'])){
        list($compare_mer_id, $compare_mer_key) = explode(':',
            base64_decode(substr($_SERVER['HTTP_AUTHORIZATION'],
            strpos($_SERVER['HTTP_AUTHORIZATION'], " ") + 1)));
      } else if(isset($_SERVER['Authorization'])) {
        list($compare_mer_id, $compare_mer_key) = explode(':',
            base64_decode(substr($_SERVER['Authorization'],
            strpos($_SERVER['Authorization'], " ") + 1)));
      } else {
        $this->SendFailAuthenticationStatus(
              "Failed to Get Basic Authentication Headers",$die);
        return false;
      }
      if($compare_mer_id != $this->merchant_id
         || $compare_mer_key != $this->merchant_key) {
        $this->SendFailAuthenticationStatus("Invalid Merchant Id/Key Pair",$die);
        return false;
      }
      return true;
    }

    function ProcessMerchantCalculations($merchant_calc) {
      $this->SendOKStatus();
      $result = $merchant_calc->GetXML();
      echo $result;
    }

// Notification API
    function ProcessNewOrderNotification() {
      $this->SendAck();
    }
    function ProcessRiskInformationNotification() {
      $this->SendAck();
    }
    function ProcessOrderStateChangeNotification() {
      $this->SendAck();
    }
//   Amount Notifications
    function ProcessChargeAmountNotification() {
      $this->SendAck();
    }
    function ProcessRefundAmountNotification() {
      $this->SendAck();
    }
    function ProcessChargebackAmountNotification() {
      $this->SendAck();
    }
    function ProcessAuthorizationAmountNotification() {
      $this->SendAck();
    }

    function SendOKStatus() {
      header('HTTP/1.0 200 OK');
    }

    /**
     * Set the response header indicating an erroneous authentication from
     * server
     *
     * @param string $msg the message to log
     */
    function SendFailAuthenticationStatus($msg="401 Unauthorized Access",
                                                                   $die=true) {
      $this->log->logError($msg);
      header('WWW-Authenticate: Basic realm="GoogleCheckout PHPSample Code"');
      header('HTTP/1.0 401 Unauthorized');
      if($die) {
       die($msg);
      } else {
      echo $msg;
      }
    }

    /**
     * Set the response header indicating a malformed request from the server
     *
     * @param string $msg the message to log
     */
    function SendBadRequestStatus($msg="400 Bad Request", $die=true) {
      $this->log->logError($msg);
      header('HTTP/1.0 400 Bad Request');
      if($die) {
       die($msg);
      } else {
      echo $msg;
      }
    }

    /**
     * Set the response header indicating that an internal error ocurred and
     * the notification sent by server can't be processed right now
     *
     * @param string $msg the message to log
     */
    function SendServerErrorStatus($msg="500 Internal Server Error",
                                                                   $die=true) {
      $this->log->logError($msg);
      header('HTTP/1.0 500 Internal Server Error');
      if($die) {
       die($msg);
      } else {
        echo $msg;
      }
    }

    /**
     * Send an acknowledgement in response to server's request
     */
    function SendAck($die=true) {
      $this->SendOKStatus();
      $acknowledgment = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>" .
                        "<notification-acknowledgment xmlns=\"" .
                        $this->schema_url . "\"/>";
      $this->log->LogResponse($acknowledgment);
      if($die) {
        die($acknowledgment);
      } else {
        echo $acknowledgment;
      }
    }

    /**
     * @access private
     */
    function GetParsedXML($request=null){
      if(!is_null($request)) {
        $this->log->LogRequest($request);
        $this->response = $request;
        ini_set('include_path', ini_get('include_path').PATH_SEPARATOR.'.');
        require_once('xml/pgtw_xmlparser.php');

        $this->xml_parser = new pgtw_XmlParser($request);
        $this->root = $this->xml_parser->GetRoot();
        $this->data = $this->xml_parser->GetData();
      }
      return array($this->root, $this->data);
    }
  }
?>

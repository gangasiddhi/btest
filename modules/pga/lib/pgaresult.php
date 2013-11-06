<?php

 /**
  * Used to create a server result as a response to a
  * merchant-calculations feedback structure, i.e shipping, tax, coupons and
  * gift certificates.
  */
  class PGAResult {
    var $shipping_name;
    var $address_id;
    var $shippable;
    var $ship_price;

    var $tax_amount;

    //var $coupon_arr = array();
    //var $giftcert_arr = array();

    /**
     * @param integer $address_id the id of the anonymous address sent by
     *                           server.
     */
    function PGAResult($address_id) {
      $this->address_id = $address_id;
    }

    function SetShippingDetails($name, $price, $shippable = "true") {
      $this->shipping_name = $name;
      $this->ship_price = $price;
      $this->shippable = $shippable;
    }

    function SetTaxDetails($amount) {
      $this->tax_amount = $amount;
    }

    /*function AddCoupons($coupon) {
      $this->coupon_arr[] = $coupon;
    }

    function AddGiftCertificates($gift) {
      $this->giftcert_arr[] = $gift;
    }*/
  }

 /**
  * This is a class used to return the results of coupons the buyer supplied in
  * the order page.
  */
  /*class GoogleCoupons {
    var $coupon_valid;
    var $coupon_code;
    var $coupon_amount;
    var $coupon_message;

    function googlecoupons($valid, $code, $amount, $message) {
      $this->coupon_valid = $valid;
      $this->coupon_code = $code;
      $this->coupon_amount = $amount;
      $this->coupon_message = $message;
    }
  }*/

 /**
  * This is a class used to return the results of gift certificates
  * supplied by the buyer on the place order page
  */

  /*class GoogleGiftcerts {
    var $gift_valid;
    var $gift_code;
    var $gift_amount;
    var $gift_message;

    function googlegiftcerts($valid, $code, $amount, $message) {
      $this->gift_valid = $valid;
      $this->gift_code = $code;
      $this->gift_amount = $amount;
      $this->gift_message = $message;
    }
  }*/
?>

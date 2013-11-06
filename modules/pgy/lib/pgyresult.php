<?php

/**
 * Used to create a server result as a response to a
 * merchant-calculations feedback structure, i.e shipping, tax, coupons and
 * gift certificates.
 */
class PGYResult {
    var $shipping_name;
    var $address_id;
    var $shippable;
    var $ship_price;
    var $tax_amount;

    /**
     * @param integer $address_id the id of the anonymous address sent by server.
     */
    function PGYResult($address_id) {
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
}

?>

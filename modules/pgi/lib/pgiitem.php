<?php

/**
 * Creates an item to be added to the shopping cart.
 * A new instance of the class must be created for each item to be added.
 *
 * Required fields are the item name, description, quantity and price
 * The private-data and tax-selector for each item can be set in the
 * constructor call or using individual Set functions
 */
class PGIItem {
    var $item_code;
    var $item_name;
    var $unit_price;
    var $quantity;
    var $merchant_item_id;
    var $merchant_item_num;

    /**
     * @param string $code the item code -- required
     * @param string $name the name of the item -- required
     * @param string $desc the description of the item -- required
     * @param integer $qty the number of units of this item the customer has
     *        in its shopping cart -- required
     * @param double $price the unit price of the item -- required
     * @param string $item_weight the weight unit used to specify the item's
     *        weight,
     *        one of 'LB' (pounds) or 'KG' (kilograms)
     * @param double $numeric_weight the weight of the item
     */
    function PGIItem($code, $name, $qty, $price) {
        $this->item_code = $code;
        $this->item_name = $name;
        $this->unit_price = $price;
        $this->quantity = $qty;
    }

    /**
     * Set the merchant item id that the merchant uses to uniquely identify an
     * item.
     *
     * @param mixed $item_id the value that identifies this item on the merchant's side
     *
     * @return void
     */
    function SetMerchantItemId($item_id) {
        $this->merchant_item_id = $item_id;
    }

    /**
     * Set the merchant item number that the merchant uses to identify an item.
     *
     * @param mixed $item_num the value that identifies this item on the merchant's side
     *
     * @return void
     */
    function SetMerchantItemNumber($item_num) {
        $this->merchant_item_num = $item_num;
    }
}

?>

<?php

if (! defined('MAX_DIGITAL_DESC')) {
    define('MAX_DIGITAL_DESC', 1024);
}

require_once(_PS_INTERFACE_DIR_ . 'IPGCart.php');

/**
 * Classes used to build a shopping cart and submit it to Payment Gateway
 */
class PGICart implements IPGCart {
    var $merchant_id;
    var $merchant_name;
    var $merchant_key;
    var $trans_type;
    var $currency;
    var $server_url;
    var $request_url;
    var $request_diagnose_url;
    var $customer_arr;
    var $item_arr;
    var $xml_data;

    // For HTML API Conversion
    // This tags are those that can be used more than once as a sub tag
    // so a "-#" must be added always
    /**
     * used when using the html api
     * tags that can be used more than once, so they need to be numbered
     * ("-#" suffix)
     */
    var $multiple_tags = array(
        'parameterized-url' => array(),
        'url-parameter' => array(),
        'item' => array(),
        'method' => array(),
        'anonymous-address' => array(),
        'result' => array(),
        'string' => array(),
    );

    var $ignore_tags = array(
        'xmlns' => true,
        'checkout-shopping-cart' => true,
        // Dont know how to translate these tag yet
        'merchant-private-data' => true,
        'merchant-private-item-data' => true,
    );

    /**
     * Has all the logic to build the cart's xml (or html) request to be posted to PGI servers.
     *
     * @param string $id the merchant id
     * @param string $name the merchant name
     * @param string $key the merchant key
     * @param string $server_type the server type of the server to be used, one
     *        of 'sandbox' or 'production'.
     *        defaults to 'sandbox'
     * @param string $currency the currency of the items to be added to the cart
     *        , as of now values can be 'USD' or 'GBP'.
     *        defaults to 'USD'
     * @param string $trans_type the transaction type of the payment gateway.
     *        as of now values can be 'Auth', 'PreAuth',
     *        'PostAuth', 'Void', 'Credit'
     *        defaults to 'Auth'
     */
    function PGICart($id, $name, $key, $server_type = "sandbox", $currency = "USD", $trans_type = "Auth") {
        $this->merchant_id = $id;
        $this->merchant_name = $name;
        $this->merchant_key = $key;
        $this->currency = $currency;
        $this->trans_type = $trans_type;

        if(strtolower($server_type) == "sandbox") {
            $this->server_url = "https://testsanalpos.est.com.tr/servlet/cc5ApiServer";
        } else {
            $this->server_url = "https://spos.isbank.com.tr/servlet/cc5ApiServer";
        }

        //The customer, item, shipping and tax table arrays are initialized
        $this->customer_arr = array();
        $this->item_arr = array();
    }

    /**
     * Set Customer Data for the XML.
     *
     * @param array $customer_data an array that contains customer data
     *
     * @return void
     */
    function SetCustomerData($customer_data) {
        foreach ($customer_data as $key => $value) {
            $this->customer_arr[$key] = $value;
        }
    }

    /**
     * Add an item to the cart.
     *
     * @param PGIItem $item an object that represents an item
     *
     * @return void
     */
    function AddItem($item) {
        $this->item_arr[] = $item;
    }

    /**
     * Builds the cart's xml to be sent.
     *
     * @return string the cart's xml
     */
    function GetXML() {
        require_once('xml/pgi_xmlbuilder.php');

        $xml_data = new pgi_XmlBuilder();
        $xml_data->Push('CC5Request');
        $xml_data->Element('Name', $this->merchant_name);
        $xml_data->Element('Password', $this->merchant_key);
        $xml_data->Element('ClientId', $this->merchant_id);
        $xml_data->Element('Type', $this->trans_type);
        $xml_data->Element('OrderId', $this->customer_arr['OrderId']);
        $xml_data->Element('GroupId', $this->customer_arr['TransId']);
        $xml_data->Element('TransId', $this->customer_arr['TransId']);
        $xml_data->Element('Total', $this->customer_arr['Total']);
        $xml_data->Element('Currency', $this->currency);
        $xml_data->Element('Number', $this->customer_arr['Number']);
        $xml_data->Element('Expires', $this->customer_arr['Expires']);
        $xml_data->Element('Taksit', $this->customer_arr['Instalment']);
        $xml_data->Element('Cvv2Val', $this->customer_arr['Cvv2Val']);
        $xml_data->Element('Mode', 'P');
        $xml_data->Element('IPAddress', Tools::getRemoteAddr());
        $xml_data->Push('OrderItemList');

        foreach($this->item_arr as $item) {
            $xml_data->Push('OrderItem');

            if ($item->merchant_item_id != '') {
                $xml_data->Element('Id', $item->merchant_item_id);
            }

            if ($item->merchant_item_num != '') {
                $xml_data->Element('ItemNumber', $item->merchant_item_num);
            }

            $xml_data->Element('ProductCode', $item->item_code);

            // As special character '&' is not allowed ,the char is replaced
            // with space and sent to the bank to avoid causing
            // error[GENERIC ERROR] during payment

            $desc = $item->item_name;
            $desc = str_replace('&', '', utf8_decode($desc));

            if (strlen($desc) > 128) {  // MAXIMUM OF 128 CHARS ARE ALLOWED
                $desc = substr($desc, 0, 128);
            }

            $xml_data->Element('Desc', $desc);
            $xml_data->Element('Price', $item->unit_price);
            $xml_data->Element('Qty', $item->quantity);
            $xml_data->Element('Total', $item->unit_price * $item->quantity);
            $xml_data->Pop('OrderItem');
        }

        $xml_data->Pop('OrderItemList');
        $xml_data->Pop('CC5Request');

        return $xml_data->GetXML();
    }

    /**
     * Submit a server-to-server request.
     * Creates a PGIRequest object (defined in pgirequest.php) and sends it to the server.
     *
     * @param int/bool $timeout timeout for the Server2Server execution
     * @param bool $die whether to die() or not after performing the request,
     *        defaults to true
     *
     * @return array with the returned http status code (200 if OK) in index 0
     *        and the redirect url returned by the server in index 1
     */
    function CheckoutServer2Server($timeout = false, $die = true) {
        ini_set('include_path', ini_get('include_path').PATH_SEPARATOR.'.');

        require_once(dirname(__FILE__).'/pgirequest.php');

        $PGIRequest = new PGIRequest(
            $this->merchant_id,
            $this->merchant_name,
            $this->merchant_key,
            $this->server_url=="https://spos.isbank.com.tr/servlet/cc5ApiServer" ? "production" : "sandbox",
            $this->currency,
            $this->trans_type
        );
        $PGIRequest->SetLogFiles('pgierror.log', 'pgimessage.log', L_ALL);

        return $PGIRequest->SendServer2ServerCart($this->GetXML(), $timeout, $die);
    }

    /**
     * @access private
     */
    function xml2html($data, $path = '', &$rta) {
        foreach($data as $tag_name => $tag) {
            if (isset($this->ignore_tags[$tag_name])) {
                continue;
            }

            if (is_array($tag)) {
                if (! $this->is_associative_array($data)) {
                    $new_path = $path . '-' . ($tag_name +1);
                } else {
                    if (isset($this->multiple_tags[$tag_name])
                        && $this->is_associative_array($tag)
                        && ! $this->isChildOf($path, $this->multiple_tags[$tag_name])) {

                            $tag_name .= '-1';
                    }

                    $new_path = $path . (empty($path)?'':'.') . $tag_name;
                }

                $this->xml2html($tag, $new_path, $rta);
            } else {
                $new_path = $path;

                if ($tag_name != 'VALUE') {
                    $new_path = $path . "." . $tag_name;
                }

                $rta .= '<input type="hidden" name="' .
                $new_path . '" value="' .$tag . '"/>'."\n";
            }
        }
    }

    /**
     * Returns true if a given variable represents an associative array
     *
     * @access private
     */
    function is_associative_array($var) {
        return is_array($var) && ! is_numeric(implode('', array_keys($var)));
    }

    /**
     * @access private
     */
    function isChildOf($path = '', $parents = array()) {
        $intersect = array_intersect(explode('.', $path), $parents);
        return ! empty($intersect);
    }
}

?>

<?php

if (! defined('MAX_DIGITAL_DESC')) {
    define('MAX_DIGITAL_DESC', 1024);
}

require_once(_PS_INTERFACE_DIR_ . 'IPGCart.php');

/**
 * Classes used to build a shopping cart and submit it to Payment Gateway
 */
class PGYCart implements IPGCart {
    var $merchant_id;
    var $merchant_name;
    var $merchant_key;
    var $terminal_id;
    var $extra_point = '000000';
    var $multiple_point = '00';
    var $koiCode;
    var $trans_type;
    var $currency = 'YT';
    var $server_url = 'https://www.posnet.ykb.com/PosnetWebService/XML';
    var $request_url;
    var $request_diagnose_url;
    var $customer_arr;
    var $item_arr;
    var $xml_data;

    /**
     * For HTML API Conversion
     * This tags are those that can be used more than once as a sub tag
     * so a "-#" must be added always used when using the html api
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
        'string' => array()
    );

    var $ignore_tags = array(
        'xmlns' => true,
        'checkout-shopping-cart' => true,
        // Dont know how to translate these tag yet
        'merchant-private-data' => true,
        'merchant-private-item-data' => true
    );

    function PGYCart($mid, $tid, $host, $name, $key, $currency, $koiCode) {
        $this->merchant_id = $mid;
        $this->terminal_id = $tid;
        $this->merchant_name = $name;
        $this->merchant_key = $key;
        $this->currency = $currency;
        $this->server_url = $host;
        $this->koiCode = $koiCode;

        // The customer, item, shipping and tax table arrays are initialized
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
     * @param PGYItem $item an object that represents an item
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
        require_once('xml/pgy_xmlbuilder.php');

        $xml_data = new pgy_XmlBuilder();

        $xml_data->Push('posnetRequest');
        $xml_data->Element('mid', $this->merchant_id );
        $xml_data->Element('tid', $this->terminal_id);
        $xml_data->Element('username', $this->merchant_name );
        $xml_data->Element('password', $this->merchant_key);
        $xml_data->Push('sale');
        $xml_data->Element('ccno', $this->customer_arr['Number']);
        $xml_data->Element('expDate', $this->customer_arr['Expires']);
        $xml_data->Element('cvc', $this->customer_arr['Cvv2Val']);
        $xml_data->Element('amount', $this->customer_arr['Total']);
        $xml_data->Element('currencyCode', $this->currency);
        $xml_data->Element('orderID', $this->customer_arr['OrderId']);
        $xml_data->Element('installment', $this->customer_arr['Instalment']);
        $xml_data->Element('extraPoint', $this->extra_point);
        $xml_data->Element('multiplePoint', $this->multiple_point);

        if (! empty($this->koiCode)) {
            $xml_data->Element('koiCode', $this->koiCode);
        }

        $xml_data->Pop('sale');
        $xml_data->Pop('posnetRequest');

        return $xml_data->GetXML();
    }

    /**
     * Submit a server-to-server request.
     * Creates a PGYRequest object (defined in pgyrequest.php) and sends it to the server.
     *
     * @param int/bool $timeout timeout for the Server2Server execution
     * @param bool $die whether to die() or not after performing the request,
     *        defaults to true
     *
     * @return array with the returned http status code (200 if OK) in index 0
     *         and the redirect url returned by the server in index 1
     */
    function CheckoutServer2Server($timeout = false, $die = true) {
        ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . '.');

        require_once(dirname(__FILE__) . '/pgyrequest.php');

        $PGYRequest = new PGYRequest(
            $this->merchant_id,
            $this->merchant_name,
            $this->merchant_key,
            $this->server_url,
            $this->currency,
            $this->trans_type
        );
        $PGYRequest->SetLogFiles('pgyerror.log', 'pgymessage.log', L_ALL);

        return $PGYRequest->SendServer2ServerCart($this->GetXML(), $timeout, $die);
    }

    /**
     * @access private
     */
    function xml2html($data, $path = '', &$rta) {
        foreach ($data as $tag_name => $tag) {
            if (isset($this->ignore_tags[$tag_name])) {
                continue;
            }

            if (is_array($tag)) {
                if (! $this->is_associative_array($data)) {
                    $new_path = $path . '-' . ($tag_name + 1);
                } else {
                    if (isset($this->multiple_tags[$tag_name])
                        && $this->is_associative_array($tag)
                        && ! $this->isChildOf($path, $this->multiple_tags[$tag_name])) {

                        $tag_name .= '-1';
                    }

                    $new_path = $path . (empty($path) ? '' : '.') . $tag_name;
                }

                $this->xml2html($tag, $new_path, $rta);
            } else {
                $new_path = $path;

                if ($tag_name != 'VALUE') {
                    $new_path = $path . "." . $tag_name;
                }

                $rta .= '<input type="hidden" name="' .
                $new_path . '" value="' . $tag . '"/>' . "\n";
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

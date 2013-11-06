<?php

if (! defined('MAX_DIGITAL_DESC')) {
    define('MAX_DIGITAL_DESC', 1024);
}

require_once(_PS_INTERFACE_DIR_ . 'IPGCart.php');

/**
 * Classes used to build a shopping cart and submit it to Payment Gateway
 */
class PGTWCart implements IPGCart {
    /*Operation Mode. For production PROD, and for Testing TEST*/
    var $mode;
    /* API Version*/
    var $version = 'v0.01';
    var $terminal_id;
    var $terminal_id_padded;
    /*Includes provision user code for the terminal */
    var $prov_user_id ='PROVAUT';
    var $user_id ='PROVAUT';
    var $prov_password;
    var $security_data;
    var $hash_data;
    /*Merchant Number*/
    var $merchant_id;
    /* Cardholder Present Code. For normal transactions should be "0" and for 3D Transactions should be "13"*/
    var $card_holder_present_code = 0;
    /* MOTO indicator. For E-commerce should be set as "N". For MO/TO should be set as "Y"*/
    var $moto_ind = 'N';
    var $merchant_name;
    var $merchant_key;
    //var $variant = false;
    /*Transaction Type*/
    var $trans_type;
    /*Currency Code*/
    var $currency;
    /*Garantibank Server URL*/
    var $server_url;
    /*var $schema_url;
    var $base_url;
    var $checkout_url;
    var $checkout_diagnose_url;*/
    var $request_url;
    var $request_diagnose_url;
    var $customer_arr;
    var $order_arr;
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
        'string' => array()
    );

    var $ignore_tags = array(
        'xmlns' => true,
        'checkout-shopping-cart' => true,
        // Dont know how to translate these tag yet
        'merchant-private-data' => true,
        'merchant-private-item-data' => true
    );

    /**
     * Has all the logic to build the cart's xml (or html) request to be posted to PGTW servers.
     *
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
    function PGTWCart($mode, $version, $terminal_id, $prov_user_id, $merchant_id, $prov_password, $currency, $trans_type, $host) {
        $this->merchant_id = $merchant_id;
        $this->terminal_id = $terminal_id;
        $this->currency = $currency;
        $this->trans_type = $trans_type;
        $this->terminal_id_padded = sprintf('0%s', $terminal_id);
        $this->security_data = strtoupper(sha1($prov_password . $this->terminal_id_padded));
        $this->mode = $mode;
        $this->server_url = $host;

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
     * Set Order Data for the XML.
     *
     * @param array $order_data is an array that contains Order data
     *
     * @return void
     */
    function setOrderData($order_data) {
        foreach ($order_data as $key => $value) {
            $this->order_arr[$key] = $value;
        }
    }

    /**
     * Add an item to the cart.
     *
     * @param PGTWItem $item an object that represents an item
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
        require_once('xml/pgtw_xmlbuilder.php');

        $this->hash_data = strtoupper(sha1($this->customer_arr['OrderId'] . $this->terminal_id . $this->customer_arr['Number'] . $this->customer_arr['Total'] . $this->security_data));
        $xml_data = new pgtw_XmlBuilder();
        $xml_data->Push('GVPSRequest');
        $xml_data->Element('Mode', $this->mode);
        $xml_data->Element('Version', $this->version);

        $xml_data->Push('Terminal');
        $xml_data->Element('ProvUserID', $this->prov_user_id);
        $xml_data->Element('HashData', $this->hash_data);
        $xml_data->Element('UserID', $this->user_id);
        $xml_data->Element('ID', $this->terminal_id);
        $xml_data->Element('MerchantID', $this->merchant_id);
        $xml_data->Pop('Terminal');

        $xml_data->Push('Customer');
        $xml_data->Element('IPAddress', $this->customer_arr['ip_address']);
        $xml_data->Element('EmailAddress', $this->customer_arr['email']);
        $xml_data->Pop('Customer');

        $xml_data->Push('Card');
        $xml_data->Element('Number', $this->customer_arr['Number']);
        $xml_data->Element('ExpireDate', $this->customer_arr['Expires']);
        $xml_data->Element('CVV2', $this->customer_arr['Cvv2Val']);
        $xml_data->Pop('Card');

        $xml_data->Push('Order');
        $xml_data->Element('OrderID', $this->customer_arr['OrderId']);
        $xml_data->Element('GroupID', $this->customer_arr['GroupId']);
        $xml_data->Pop('Order');

        $xml_data->Push('Transaction');
        $xml_data->Element('Type', $this->trans_type);
        $xml_data->Element('InstallmentCnt', $this->customer_arr['Instalment']);
        $xml_data->Element('Amount', $this->customer_arr['Total']);
        $xml_data->Element('CurrencyCode', $this->currency);
        $xml_data->Element('CardholderPresentCode', $this->card_holder_present_code);
        $xml_data->Element('MotoInd', $this->moto_ind);
        $xml_data->Push('GSM');
        $xml_data->Element('GSMNumber', $this->customer_arr['GSMNumber']);
        $xml_data->Element('WalletID', $this->customer_arr['WalletID']);
        $xml_data->Pop('GSM');
        $xml_data->Pop('Transaction');
        $xml_data->Pop('GVPSRequest');

        return $xml_data->GetXML();
    }

    /**
     * Submit a server-to-server request.
     * Creates a PGTWRequest object (defined in pgtwrequest.php) and sends it to the server.
     *
     * @param int/bool $timeout timeout for the Server2Server execution
     * @param bool $die whether to die() or not after performing the request,
     *                  defaults to true
     *
     * @return array with the returned http status code (200 if OK) in index 0
     *               and the redirect url returned by the server in index 1
     */
    function CheckoutServer2Server($timeout = false, $die = true) {
        ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . '.');
        require_once(dirname(__FILE__) . '/pgtwrequest.php');

        $PGTWRequest = new PGTWRequest(
            $this->merchant_id,
            $this->merchant_name,
            $this->merchant_key,
            $this->server_url,
            $this->currency,
            $this->trans_type
        );
        $PGTWRequest->SetLogFiles('pgtwerror.log', 'pgtwmessage.log');

        return $PGTWRequest->SendServer2ServerCart($this->GetXML(), $timeout, $die);
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
                        AND $this->is_associative_array($tag)
                        AND ! $this->isChildOf($path, $this->multiple_tags[$tag_name])) {

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

    // Returns true if a given variable represents an associative array
    /**
     * @access private
     */
    function is_associative_array($var) {
        return is_array($var) && ! is_numeric(implode('', array_keys($var)));
    }

    /**
     * @access private
     */
    function isChildOf($path='', $parents=array()) {
        $intersect = array_intersect(explode('.', $path), $parents);
        return ! empty($intersect);
    }
}

?>

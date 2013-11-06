<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Tools
 *
 * @author gangu.km
 */
class Tools extends ToolsCore {
    /**
     * getHttpHost return the <b>current</b> host used, with the protocol (http or https) if $http is true
     * This function should not be used to choose http or https domain name.
     * Use Tools::getShopDomain() or Tools::getShopDomainSsl instead
     *
     * @param boolean $http
     * @param boolean $entities
     * @return string host
     */
    public static function getHttpHost($http = false, $entities = false) {
        $host = (isset($_SERVER['HTTP_X_FORWARDED_HOST']) ? $_SERVER['HTTP_X_FORWARDED_HOST'] : $_SERVER['HTTP_HOST']);

        if (empty($host)) {
            $host = ereg_replace("(https?)://", "", _PS_BASE_URL_);
        } else {
            if ($entities) {
                $host = htmlspecialchars($host, ENT_COMPAT, 'UTF-8');
            }

            if ($http) {
                $host = (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://') . $host;
            }
        }

        return $host;
    }

    /**
     * param $ccNo is a credit-card number.
     * returns the bank code based on the credit card number.
     *
     * Description: Based on the first 6 digits of the credit card number, bank code is detected.
     */
    public static function getBankViaCCNo($ccNo) {
        if (! isset($ccNo)) {
            return false;
        }

        $bid = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
            SELECT `bank_code`, `type`, `module`
            FROM `' . _DB_PREFIX_ . 'mediator_bin_list`
            WHERE `bin` = ' . substr($ccNo, 0, 6) . '
        ');

        if (! $bid) {
            return false;
        }

        if ($bid['type'] == 'AMEX') {
            if (strlen($ccNo) !== 15) {
                return false;
            }
        } elseif (strlen($ccNo) !== 16) {
            return false;
        }

        $paymentModules = Tools::filterPaymentModules(Module::getPaymentModules());

        /**
         * Only if module is available and enabled, return it..
         */
        if (in_array($bid['module'], $paymentModules)) {
            return $bid;
        }

        /**
         * overwrite it to return pgf by default while leaving type
         * as it's. It should be the same type with user input.
         * module is null to prevent installment options in such case.
         */
        $bid['module'] = null;
        $bid['bank_code'] = 111;

        return $bid;
    }

    /**
     * Gets credit card related payment modules and returns
     * names to be used with installment options..
     */
    public static function filterPaymentModules($m) {
        function test($n) {
            if (in_array($n['name'], array('pgtw', 'cashondelivery', 'mediator'))) {
                return false;
            }

            return true;
        }

        function getName($n) {
            return $n['name'];
        }

        return array_map('getName', array_filter($m, 'test'));
    }

    public static function maskPaymentDetails($input) {
        if (is_array($input)) {
            $keys = array(
                'card_number' => array(
                    'pattern' => '/(\d{6})(\d{6})(\d{4})/',
                    'replace' => '$1******$3'
                ),
                'card_expiry' => array(
                    'pattern' => '/\d{4}/',
                    'replace' => '****'
                ),
                'card_cvv' => array(
                    'pattern' => '/\d{3}/',
                    'replace' => '***'
                )
            );

            foreach ($keys as $key => $rgx) {
                if (array_key_exists($key, $input)) {
                    $input[$key] = preg_replace($rgx['pattern'], $rgx['replace'], $input[$key]);
                }
            }
        } else if (is_string($input)) {
            $rgx1 = '/\<ccno\>(\d{6})(\d{6})(\d{4})\<\/ccno\>/'; // pgy
            $rgx2 = '/\<Number\>(\d{6})(\d{6})(\d{4})\<\/Number\>/'; // common for pgf and pgg
            $rgx3 = '/\<cvc\>\d{3}\<\/cvc\>/'; // pgy
            $rgx4 = '/\<CVV2\>\d{3}\<\/CVV2\>/'; // pgg
            $rgx5 = '/\<Cvv2Val\>\d{3}\<\/Cvv2Val\>/'; // pgf
            $rgx6 = '/\<expDate\>\d{4}\<\/expDate\>/'; // pgy
            $rgx7 = '/\<ExpireDate\>\d{4}\<\/ExpireDate\>/'; // pgg
            $rgx8 = '/\<Expires\>\d{4}\<\/Expires\>/'; // pgf

            $input = preg_replace($rgx1, "<ccno>$1******$3</ccno>", $input);
            $input = preg_replace($rgx2, "<Number>$1******$3</Number>", $input);
            $input = preg_replace($rgx3, "<cvc>***</cvc>", $input);
            $input = preg_replace($rgx4, "<CVV2>***</CVV2>", $input);
            $input = preg_replace($rgx5, "<Cvv2Val>***</Cvv2Val>", $input);
            $input = preg_replace($rgx6, "<expDate>****</expDate>", $input);
            $input = preg_replace($rgx7, "<ExpireDate>****</ExpireDate>", $input);
            $input = preg_replace($rgx8, "<Expires>****</Expires>", $input);
        }

        return $input;
    }
    public static function sendMail($to, $subject, $body, $from, $fromName) {
        if (!$from){
            $postmanEmail = Configuration::get('PS_POSTMAN_EMAIL');
            $from = "Butigo Postman <$postmanEmail>";
        } elseif ($fromName) {
            $from = "$fromName <$from>";
        }

        $headers = "From: $from\n";
        $headers .= "Reply-To: $from\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\n";
        $body = str_replace("\n", '</br>', $body);

        return mail($to, $subject, $body, $headers);
    }

    public static function sendMailToAdmins($subject, $body, $from) {
        return self::sendMail(Configuration::get('PS_ROOT_EMAIL'), $subject, $body, $from);
    }

    /**
     * use translations files to replace english expression.
     *
     * @param mixed $string term or expression in english
     * @param string $class
     * @param boolan $addslashes if set to true, the return value will pass through addslashes(). Otherwise, stripslashes().
     * @param boolean $htmlentities if set to true(default), the return value will pass through htmlentities($string, ENT_QUOTES, 'utf-8')
     * @return string the translation if available, or the english default text.
     */
    public static function l($string, $class = 'Tools', $addslashes = FALSE, $htmlentities = TRUE) {
        global $_LANGADM;

        $key = md5(str_replace('\'', '\\\'', $string));
        $keyName = 'Tools'.$key;
        $str = (key_exists($keyName, $_LANGADM)) ? $_LANGADM[$keyName] : ((key_exists($class.$key, $_LANGADM)) ? $_LANGADM[$class.$key] : $string);
        $str = $htmlentities ? htmlentities($str, ENT_QUOTES, 'utf-8') : $str;
        return str_replace('"', '&quot;', ($addslashes ? addslashes($str) : stripslashes($str)));
    }

    public static function getCipherTool() {
        $cipherTool = new Blowfish(_COOKIE_KEY_, _COOKIE_IV_);

        if (Configuration::get('PS_CIPHER_ALGORITHM')) {
            $cipherTool = new Rijndael(_RIJNDAEL_KEY_, _RIJNDAEL_IV_);
        }

        return $cipherTool;
    }

    // available in PHP >= 5.5.0
    public static function array_column($input, $column_key, $index_key = null) {
        if ($index_key !== null) {
            // Collect the keys
            $keys = array();
            $i = 0; // Counter for numerical keys when key does not exist

            foreach ($input as $row) {
                if (array_key_exists($index_key, $row)) {
                    // Update counter for numerical keys
                    if (is_numeric($row[$index_key]) || is_bool($row[$index_key])) {
                        $i = max($i, (int) $row[$index_key] + 1);
                    }

                    // Get the key from a single column of the array
                    $keys[] = $row[$index_key];
                } else {
                    // The key does not exist, use numerical indexing
                    $keys[] = $i++;
                }
            }
        }

        if ($column_key !== null) {
            // Collect the values
            $values = array();
            $i = 0; // Counter for removing keys

            foreach ($input as $row) {
                if (array_key_exists($column_key, $row)) {
                    // Get the values from a single column of the input array
                    $values[] = $row[$column_key];
                    $i++;
                } elseif (isset($keys)) {
                    // Values does not exist, also drop the key for it
                    array_splice($keys, $i, 1);
                }
            }
        } else {
            // Get the full arrays
            $values = array_values($input);
        }

        if ($index_key !== null) {
            return array_combine($keys, $values);
        }

        return $values;
    }
}

?>

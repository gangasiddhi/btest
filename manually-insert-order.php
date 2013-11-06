<?php

require_once(dirname(__FILE__) . '/config/config.inc.php');
require_once(dirname(__FILE__) . '/init.php');

require_once(_PS_MODULE_DIR_ . '/pgf/pgf.php');
require_once(_PS_MODULE_DIR_ . '/pgg/pgg.php');
require_once(_PS_MODULE_DIR_ . '/pgy/pgy.php');
require_once(_PS_MODULE_DIR_ . '/cashondelivery/cashondelivery.php');

$cron_user = Tools::getValue('crnu');
$cron_pass = Tools::getValue('crnp');

if (! $cron_user) {
    $cron_user = $argv[1];
}

if (! $cron_pass) {
    $cron_pass = $argv[2];
}

$cron_pass = Tools::encrypt($cron_pass);

function getInvalidOrdersByDate($date_from, $date_to, $id_customer = NULL) {
    $sql = '
        SELECT *
        FROM `'._DB_PREFIX_.'orders` o
        WHERE DATE_ADD(date_add, INTERVAL -1 DAY) <= \'' . pSQL($date_to) .
            '\' AND date_add >= \'' . pSQL($date_from) . '\'
            AND o.valid = 0
            ' . ($id_customer ? ' AND id_customer = ' . (int)($id_customer) : '');

    $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql);
    $orders = array();

    if ($result) {
        foreach ($result AS $order) {
            $orders[$order['id_order']] = $order;
            $sql1 = 'SELECT *
                FROM `' . _DB_PREFIX_ . 'order_discount` WHERE `id_order`  = ' . $order['id_order'] . '';
            $result1 = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql1);

            if ($result1) {
                foreach ($result1 AS $discount) {
                    $orders[$order['id_order']]['id_discount'] = $discount['id_discount'];
                }
            }
        }

        return $orders;
    } else {
        return false;
    }
}

if ($cron_user == _CRON_USER_ && $cron_pass == _CRON_PASSWD_) {
    $date_from = '2012-07-26 00:00:00';
    $date_to = date('Y-m-d H:i:s', time());
    $invalid_orders = getInvalidOrdersByDate($date_from, $date_to);

    if ($invalid_orders) {
        $message = '';
        $message .= '<html>
            <head>
                <title></title>
                <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
            </head>
            <body>
                <p>Hi,</p>
                <p>The Following are the invoice numbers which were fixed and corrected.</p>
                <p>Kindly download the XMLs from back-office for the same manually.</p>
                <p>';

        foreach ($invalid_orders as $order) {
            $logFile = @fopen(_PS_ROOT_DIR_ . '/log/invalid_orders.txt', "a");

            fwrite($logFile, 'START----' . date("D M j G:i:s T Y") . "\n");

            $data = serialize($order);

            fwrite($logFile, 'Data---' . $data . "\n");

            $cart = new Cart($order['id_cart']);
            $customer = new Customer((int)$cart->id_customer);
            $total = floatval(number_format($cart->getOrderTotal(true, 3), 2, '.', ''));
            $module = $order['module'];
            $displayName = $order['payment'];
            $products = $cart->getProducts();

            foreach ($products as $product) {
                $qty = $product['quantity_attribute'] + $product['cart_quantity'];

                $str7 =  'The product attribute quantity (' . $product['quantity_attribute']
                    . ') to be updated is ' . $qty . ' for product id ' . $product['id_product']
                    . ' ipa ' . $product['id_product_attribute'] . ' order-id ' . $order['id_order']
                    . ' cart-id ' . $order['id_cart'] . ' respectively';

                echo $str7;
                echo "<br>";

                fwrite($logFile, sprintf("\n%s\n", $str7 . "\n"));

                $updated_ipa = Db::getInstance()->Execute('
                    UPDATE `' . _DB_PREFIX_ . 'product_attribute`
                    SET `quantity` = ' . intval($qty) . '
                    WHERE `id_product` = ' . intval($product['id_product']) . '
                        AND `id_product_attribute` = ' . intval($product['id_product_attribute']) . ''
                );

                if ($updated_ipa) {
                    $str8 =  'The product attribute quantity is updated for product id '
                        . $product['id_product'] . ' ipa ' . $product['id_product_attribute']
                        . ' order-id ' . $order['id_order'] . ' cart-id ' . $order['id_cart'] . ' respectively';

                    echo $str8;
                    echo "<br>";

                    fwrite($logFile, sprintf("\n%s\n", $str8 . "\n"));
                }

                $updated_product = Db::getInstance()->Execute('
                    UPDATE `' . _DB_PREFIX_ . 'product`
                    SET `quantity` =
                        (
                            SELECT SUM(`quantity`)
                            FROM `' . _DB_PREFIX_ . 'product_attribute`
                            WHERE `id_product` = ' . intval($product['id_product']) . '
                        )
                    WHERE `id_product` = ' . intval($product['id_product'])
                );

                if ($updated_product) {
                    $str9 =  'The product quantity is updated for product id ' . $product['id_product']
                        . ' ipa ' . $product['id_product_attribute'] . ' order-id ' . $order['id_order']
                        . ' cart-id ' . $order['id_cart'] . ' respectively';

                    echo $str9;
                    echo "<br>";

                    fwrite($logFile, sprintf("\n%s\n", $str9 . "\n"));
                }

            }

            if (isset($order['id_discount']) AND $order['id_discount']) {
                $disc = new Discount($order['id_discount']);
                $disc->quantity = $disc->quantity + 1;
                $disc->update();

                if (Db::getInstance()->Execute('DELETE FROM `' . _DB_PREFIX_ . 'order_discount` WHERE `id_order`  = ' . $order['id_order'] . '')) {
                    $str6 =  'Deleted the order discount id (' . $order['id_discount'] . ') for Order Id: '
                        . $order['id_order'] . '';

                    echo "</br>";
                    echo $str6;
                    echo "</br>";

                    fwrite($logFile, sprintf("\n%s\n", $str6 . "\n"));
                }
            }

            if (Db::getInstance()->Execute('DELETE FROM `' . _DB_PREFIX_ . 'orders` WHERE `id_order`  = ' . $order['id_order'] . '')) {
                echo "</br>";

                $str =  'Deleted the order with order id,cart id: ' . $order['id_order'] . '---' . $order['id_cart'] . ' respectively';

                echo $str;
                echo "</br>";

                fwrite($logFile, sprintf("\n%s\n", $str . "\n"));
            }

            if ($module == 'pgf') {
                $pgf = new PGF();
                $pgf->validateOrder(
                    intval($cart->id),
                    _PS_OS_PREPARATION_,
                    $total,
                    $displayName,
                    $order['installment_count'],
                    $order['installment_interest'],
                    $order['installment_amount'],
                    NULL,
                    array(),
                    NULL,
                    false,
                    $customer->secure_key
                );
                $orderObj = new Order(intval($pgf->currentOrder));

                //generating the XML for an order
                Order::createOrderxmlDaily((int)$orderObj->id);

                echo "</br>";

                $str2 =  'Order has been succesfully inserted with cart id---' . $cart->id . '.Payment via ('
                    . $displayName . '--' . $module . ')';

                echo $str2;

                $str3 = 'New order id is ' . $orderObj->id;

                fwrite($logFile, sprintf("\n%s\n", $str2 . "\n"));
                fwrite($logFile, sprintf("\n%s\n", $str3 . "\n"));

                echo "</br>";
            } elseif($module == 'pgg') {
                $pgg = new PGG();
                $pgg->validateOrder(
                    intval($cart->id),
                    _PS_OS_PREPARATION_,
                    $total,
                    $displayName,
                    $order['installment_count'],
                    $order['installment_interest'],
                    $order['installment_amount'],
                    NULL,
                    array(),
                    NULL,
                    false,
                    $customer->secure_key
                );
                $orderObj = new Order(intval($pgg->currentOrder));

                //generating the XML for an order
                Order::createOrderxmlDaily((int)$orderObj->id);

                echo "</br>";

                $str2 =  'Order has been succesfully inserted with cart id---' . $cart->id . '.Payment via ('
                    . $displayName . '--' . $module . ')';

                echo $str2;

                $str3 = 'New order id is ' . $orderObj->id;

                fwrite($logFile, sprintf("\n%s\n", $str2 . "\n"));
                fwrite($logFile, sprintf("\n%s\n", $str3 . "\n"));

                echo "</br>";
            } elseif($module == 'pgy') {
                $pgy = new PGY();
                $pgy->validateOrder(
                    intval($cart->id),
                    _PS_OS_PREPARATION_,
                    $total,
                    $displayName,
                    $order['installment_count'],
                    $order['installment_interest'],
                    $order['installment_amount'],
                    NULL,
                    array(),
                    NULL,
                    false,
                    $customer->secure_key
                );
                $orderObj = new Order(intval($pgy->currentOrder));

                //generating the XML for an order
                Order::createOrderxmlDaily((int)$orderObj->id);

                echo "</br>";

                $str2 =  'Order has been succesfully inserted with cart id---' . $cart->id
                    . '.Payment via (' . $displayName . '--' . $module . ')';

                echo $str2;

                $str3 = 'New order id is ' . $orderObj->id;

                fwrite($logFile, sprintf("\n%s\n", $str2 . "\n"));
                fwrite($logFile, sprintf("\n%s\n", $str3 . "\n"));

                echo "</br>";
            } elseif($module == 'cashondelivery') {
                $cashOnDelivery = new CashOnDelivery();
                $cashOnDelivery->validateOrder(
                    (int)$cart->id,
                    Configuration::get('PS_OS_PREPARATION'),
                    $total,
                    $displayName,
                    1,
                    0,
                    $total,
                    NULL,
                    array(),
                    NULL,
                    false,
                    $customer->secure_key
                );
                $orderObj = new Order((int)$cashOnDelivery->currentOrder);

                //generating the XML for an order
                Order::createOrderxmlDaily((int)$orderObj->id);

                echo "</br>";

                $str4 = 'Order has been succesfully inserted with cart id---'
                    . $cart->id . '.Payment via (' . $displayName . '--' . $module . ')';

                echo $str4;

                $str5 = 'New order id is' . $orderObj->id;

                fwrite($logFile, sprintf("\n%s\n", $str4 . "\n"));
                fwrite($logFile, sprintf("\n%s\n", $str5 . "\n"));

                echo "</br>";
            }
        }

        fwrite($logFile, 'END' . "\n");
        fclose($logFile);

        $message .= "</p>
            <p>Regards, <br>Butigo Postman</p>
            </body>
            </html>";

        $to = "root@butigo.com";
        $subject = "Invoice Numbers of invalid orders to Generate XMLs";

        // Always set content-type when sending HTML email
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html; charset=utf-8" . "\r\n";

        // More headers
        $headers .= "From: Butigo Postman <postman@butigo.com>\r\n";
        $headers .= "Cc: managers@butigo.com\r\n";
        $headers .= "Reply-To: root@butigo.com\r\n";

        mail($to, $subject, $message, $headers);
    } else {
        echo 'There are no invalid orders.';
    }
} else {
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
    header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");

    header("Cache-Control: no-store, no-cache, must-revalidate");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");

    header("Location: ../");

    exit;
}

?>

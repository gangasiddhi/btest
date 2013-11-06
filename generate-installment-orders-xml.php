<?php
/*
* 2007-2011 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2011 PrestaShop SA
*  @version  Release: $Revision: 6599 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

require_once(dirname(__FILE__).'/config/config.inc.php');
require_once(dirname(__FILE__).'/init.php');

global $cookie;
$hours = Tools::getValue('hr')*60*60*24;
$now = time();
$date_to = date('Y-m-d H:i:s', $now);
$date_from = date('Y-m-d H:i:s', $now - $hours);

$date_to1 = date('Y-m-d', $now);
$date_from1 = date('Y-m-d', $now - $hours);

$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
    SELECT `id_order`
    FROM `'._DB_PREFIX_.'orders`
    WHERE  `installment_count` >= 6 AND `invoice_date` BETWEEN  \''.pSQL($date_from).'\' AND  \''.pSQL($date_to).'\'
    ORDER BY id_order ASC');


if($result){

    $xml_dir = _PS_DOWNLOAD_DIR_.'order_xml_logs/'.$date_from1.'_'.$date_to1.'_xmls';

    if (!mkdir($xml_dir, 0777, true)) {
        die('Failed to create folders...');
    }

    foreach ($result AS $ord){
            $order = new Order(intval($ord['id_order']));
            $res = Tools::generateXml($order);
            $output = $res;

            $fh = fopen($xml_dir . "/" . $order->invoice_number . ".xml", 'w');
            fwrite($fh, $output);
            fclose($fh);
    }

    echo "Order XMLs from $date_from to $date_to generated successfully.";
}
else{
        echo "No orders from $date_from to $date_to";
}

?>

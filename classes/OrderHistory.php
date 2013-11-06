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
*  @version  Release: $Revision: 7540 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

class OrderHistoryCore extends ObjectModel
{
	/** @var integer Order id */
	public 		$id_order;

	/** @var integer Order state id */
	public 		$id_order_state;

	/** @var integer Employee id for this history entry */
	public 		$id_employee;

	/** @var string Object creation date */
	public 		$date_add;

	/** @var string Object last modification date */
	public 		$date_upd;

	protected $tables = array ('order_history');

	protected	$fieldsRequired = array('id_order', 'id_order_state');
	protected	$fieldsValidate = array('id_order' => 'isUnsignedId', 'id_order_state' => 'isUnsignedId', 'id_employee' => 'isUnsignedId');

	protected 	$table = 'order_history';
	protected 	$identifier = 'id_order_history';

	protected	$webserviceParameters = array(
		'objectsNodeName' => 'order_histories',
		'fields' => array(
			'id_order_state' => array('required' => true, 'xlink_resource'=> 'order_states'),
			'id_order' => array('xlink_resource' => 'orders'),
		),
	);

	public function getFields()
	{
		parent::validateFields();

		$fields['id_order'] = (int)($this->id_order);
		$fields['id_order_state'] = (int)($this->id_order_state);
		$fields['id_employee'] = (int)($this->id_employee);
		$fields['date_add'] = pSQL($this->date_add);

		return $fields;
	}

	public function changeIdOrderState($new_order_state = NULL, $id_order, $id_order_detail = NULL)
	{
		if ($new_order_state != NULL)
		{
			Hook::updateOrderStatus((int)($new_order_state), (int)($id_order), $id_order_detail);
			$order = new Order((int)($id_order));

			/* Best sellers */
			$newOS = new OrderState((int)($new_order_state), $order->id_lang);
			$oldOrderStatus = OrderHistory::getLastOrderState((int)($id_order));
			$cart = Cart::getCartByOrderId($id_order);
			$isValidated = $this->isValidated();
			if (Validate::isLoadedObject($cart))
				foreach ($cart->getProducts() as $product)
				{
					/* If becoming logable => adding sale */
					if ($newOS->logable AND (!$oldOrderStatus OR !$oldOrderStatus->logable))
						ProductSale::addProductSale($product['id_product'], $product['cart_quantity']);
					/* If becoming unlogable => removing sale */
					elseif (!$newOS->logable AND ($oldOrderStatus AND $oldOrderStatus->logable))
						ProductSale::removeProductSale($product['id_product'], $product['cart_quantity']);
					if (!$isValidated AND $newOS->logable AND isset($oldOrderStatus) AND $oldOrderStatus AND $oldOrderStatus->id == Configuration::get('PS_OS_ERROR'))
					{
						Product::updateQuantity($product);
					}
				}

			$this->id_order_state = (int)($new_order_state);

			/* Change invoice number of order ? */
			if (!Validate::isLoadedObject($newOS) OR !Validate::isLoadedObject($order))
				die(Tools::displayError('Invalid new order state'));

			/* The order is valid only if the invoice is available and the order is not cancelled */
			$order->valid = $newOS->logable;
			$order->update();

			/*Send the orderDetails to the Araskargo for shippment tracking*/
			if($new_order_state == (int)Configuration::get('PS_OS_PREPARATION')){
				Module::hookExec('externalShippingIntegration',array('order' => (int)($id_order)));
			}

			Hook::postUpdateOrderStatus((int)($new_order_state), (int)($id_order));
		}
	}

	public static function getLastOrderState($id_order)
	{
		$id_order_state = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
		SELECT `id_order_state`
		FROM `'._DB_PREFIX_.'order_history`
		WHERE `id_order` = '.(int)($id_order).'
		ORDER BY `date_add` DESC, `id_order_history` DESC');
		if (!$id_order_state)
			return false;
		return new OrderState($id_order_state, Configuration::get('PS_LANG_DEFAULT'));
	}

	public static function getPreviousOrderStateId($id_order) {
		$sql = 'SELECT `id_order_state`
		FROM `'._DB_PREFIX_.'order_history`
		WHERE `id_order` = '.(int)($id_order).'
		ORDER BY `date_add` DESC LIMIT 0 , 2';
		$result = Db::getInstance()->ExecuteS($sql);

		// If there is 1 state(last, not previous) return false
		if (!$result OR count($result) < 2) {
			return false;
		}

		return (int) $result[1]['id_order_state'];
		//return new OrderState($id_order_state, Configuration::get('PS_LANG_DEFAULT'));
	}

	public function addWithemail($autodate = true, $templateVars = false) {
		$log = Logger::getLogger(get_class($this));
		$lastOrderState = $this->getLastOrderState($this->id_order);

		if (! parent::add($autodate)) {
			$log->error('History was not written due to error: ' . mysql_error());
			return false;
		}

		$sql = '
			SELECT osl.`template`, c.`lastname`, c.`firstname`, osl.`name` AS osname, c.`email`
			FROM `' . _DB_PREFIX_ . 'order_history` oh
				LEFT JOIN `' . _DB_PREFIX_ . 'orders` o ON oh.`id_order` = o.`id_order`
				LEFT JOIN `' . _DB_PREFIX_ . 'customer` c ON o.`id_customer` = c.`id_customer`
				LEFT JOIN `' . _DB_PREFIX_ . 'order_state` os ON oh.`id_order_state` = os.`id_order_state`
				LEFT JOIN `' . _DB_PREFIX_ . 'order_state_lang` osl ON (os.`id_order_state` = osl.`id_order_state` AND osl.`id_lang` = o.`id_lang`)
			WHERE oh.`id_order_history` = ' . (int) $this->id . '
				AND os.`send_email` = 1
		';

		$log->debug($sql);

		$result = Db::getInstance()->getRow($sql);

		$log->debug('Result: ' . print_r($result, true));

		if (isset($result['template']) AND Validate::isEmail($result['email'])) {
			$log->info('Got template and e-mail from history, continuing..');

			$topic = $result['firstname'] . ', ' . $result['osname'];
			$data = array(
				'{lastname}' => $result['lastname'],
				'{firstname}' => $result['firstname'],
				'{id_order}' => (int) $this->id_order
			);

			if ($templateVars) {
				$data = array_merge($data, $templateVars);
			}

			$order = new Order((int) $this->id_order);
			$data['{total_paid}'] = Tools::displayPrice((float) $order->total_paid, new Currency((int) $order->id_currency), false);
			$data['{order_name}'] = sprintf("#%06d", (int) $order->id);

			$log->debug('Prepared order (' . $this->id_order . ') details to send by e-mail: ' . print_r($data, true));

			// An additional email is sent the first time a virtual item is validated
			if ($virtualProducts = $order->getVirtualProducts()
				AND (! $lastOrderState OR ! $lastOrderState->logable)
				AND $newOrderState = new OrderState($this->id_order_state, Configuration::get('PS_LANG_DEFAULT'))
				AND $newOrderState->logable) {

				global $smarty;

				$assign = array();

				foreach ($virtualProducts AS $key => $virtualProduct) {
					$id_product_download = ProductDownload::getIdFromIdProduct($virtualProduct['product_id']);
					$product_download = new ProductDownload($id_product_download);
					$assign[$key]['name'] = $product_download->display_filename;
					$dl_link = $product_download->getTextLink(false, $virtualProduct['download_hash'])
						.'&id_order='.$order->id
						.'&secure_key='.$order->secure_key;
					$assign[$key]['link'] = $dl_link;

					if ($virtualProduct['download_deadline'] != '0000-00-00 00:00:00') {
						$assign[$key]['deadline'] = Tools::displayDate($virtualProduct['download_deadline'], $order->id_lang);
					}

					if ($product_download->nb_downloadable != 0) {
						$assign[$key]['downloadable'] = $product_download->nb_downloadable;
					}
				}

				$smarty->assign('virtualProducts', $assign);
				$smarty->assign('id_order', $order->id);
				$iso = Language::getIsoById((int)($order->id_lang));
				$links = $smarty->fetch(_PS_MAIL_DIR_.$iso.'/download-product.tpl');
				$tmpArray = array('{nbProducts}' => count($virtualProducts), '{virtualProducts}' => $links);
				$data = array_merge ($data, $tmpArray);

				Mail::Send((int)$order->id_lang, 'download_product', Mail::l('Virtual product to download', $order->id_lang), $data, $result['email'], $result['firstname'].' '.$result['lastname']);
			}

			if (Validate::isLoadedObject($order)) {
				$log->info('Sending out an e-mail containing information regarding status of order..');
				
				/*Sending the Exchange/Refund/Cancel Voucher details to the Sailthru*/
				if(($result['template'] == 'refund' || 
					$result['template'] == 'order_canceled' || 
					$result['template'] == 'exchange' ||
					$result['template'] == 'payment' ||
					$result['template'] == 'payment_error' ||
					$result['template'] == 'shipped' ||
					$result['template'] == 'credit' ||
					$result['template'] == 'manualCredit' ||
					$result['template'] == 'manualrefund' ||
					$result['template'] == 'account' ||
					$result['template'] == 'order_waiting_for_customer_approval') AND Module::isInstalled('sailthru')){
					$vars = array();
					foreach($data  AS $key => $dat){
						$vars[str_replace(array('{','}'), '', $key)] = $dat;
					}					
					$orderDetail = array('emailTemplate' => $result['template'],
										 'customerEmail' =>  $result['email'],
										 'customerFirstName' => $result['firstname'],
										 'customerLastName' => $result['lastname'],
										 'orderData' => $vars);
					Module::hookExec('sailThruMailSend', array(
						'sailThruEmailTemplate' => 'Order-Statuses',
						'orderStatusDetail' => $orderDetail
					));
				}else{
					Mail::Send((int)$order->id_lang, $result['template'], $topic, $data, $result['email'], $result['firstname'] . ' ' . $result['lastname']);
				}
				
			} else {
				$log->error('Could not send e-mail regarding status of order! Seems like order was not a valid object!');
				$log->debug('Passed object instead of order was: ' . print_r($order, true));
			}
		}

		return true;
	}

	public function isValidated()
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
		SELECT COUNT(oh.`id_order_history`) AS nb
		FROM `'._DB_PREFIX_.'order_state` os
		LEFT JOIN `'._DB_PREFIX_.'order_history` oh ON (os.`id_order_state` = oh.`id_order_state`)
		WHERE oh.`id_order` = '.(int)$this->id_order.'
		AND os.`logable` = 1');
	}

	public static function getOrderHistoryId($orderId){
		$sql = 'SELECT `id_order_history`
				FROM `'._DB_PREFIX_.'order_history`
				WHERE `id_order` = 	'.(int)$orderId;
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
	}

	public static function isOrderStateExist($orderId, $orderStateId) {
		$sql = 'SELECT id_order_state FROM  `bu_order_history` '.
		' WHERE id_order_state=' . pSQL($orderStateId) . ' and id_order =' . pSQL($orderId);
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
		return $result  ? true : false;
	}
}

<?php

class OrderHistoryDetailsCore extends ObjectModel
{

	/** @var integer Order id */
	public $id_order;

	/** @var integer Order detail id */
	public $id_order_detail;

	/** @var integer Order state id */
	public $id_order_state;

	/** @var integer amount for this history entry */
	public $amount;
	public $current_refund_amt;

	/** @var float Installment refunded amount for the order. */
	public $installment_interest_refund_amt;

	/** @var float Product price with tax */
	public $refund_product_price_with_tax;

	/** @var float Discounts price refunded */
	public $discounts;

	/**
	 * Keeps discount if created with same time. For example exchange voucher.
	 * When we want to exchange callation, we need to id_discount of created for delete discount.
	 * We using this for that purpose for now. You can use for similar purpose.
	 *
	 * @var int
	 */
	public $id_discount;

	/** @var integer quantity for this history entry */
	public $quantity;

	/** @var varchar product reference for this history entry */
	public $product_reference;

	/** @var float shipping cost for the order. */
	public $shipping_cost;

	/** @var int id of the employess who made the refund, exchange.... for that order. */
	public $id_employee;

	/** @var string Object creation date */
	public $date_add;
	protected $tables = array('order_history_detail');
	protected $fieldsRequired = array('id_order', 'id_order_state');
	protected $fieldsValidate = array('id_order' => 'isUnsignedId', 'id_order_state' => 'isUnsignedId', 'id_order_detail' => 'isUnsignedId', 'id_discount' => 'isUnsignedId');
	protected $table = 'order_history_detail';
	protected $identifier = 'id_history';

	public function getFields()
	{
		parent::validateFields();

		$fields['id_order'] = (int) ($this->id_order);
		$fields['id_order_detail'] = (int) ($this->id_order_detail);
		$fields['id_order_state'] = (int) ($this->id_order_state);
		$fields['amount'] = floatval($this->amount);
		$fields['current_refund_amt'] = floatval($this->current_refund_amt);
		$fields['refund_product_price_with_tax'] = floatval($this->refund_product_price_with_tax);
		$fields['installment_interest_refund_amt'] = floatval($this->installment_interest_refund_amt);
		$fields['quantity'] = (int) ($this->quantity);
		$fields['product_reference'] = pSQL($this->product_reference);
		$fields['shipping_cost'] = pSQL($this->shipping_cost);
		$fields['discounts'] = pSQL($this->discounts);
		$fields['id_employee'] = pSQL($this->id_employee);
		$fields['date_add'] = pSQL($this->date_add);
		$fields['id_discount'] = pSQL($this->id_discount);

		return $fields;
	}

	static public function getPartialRefundAmount($id_order, $id_order_detail = NULL, $state = 0) {
		$sql = '
		SELECT current_refund_amt AS amount
		FROM `' . _DB_PREFIX_ . 'order_history_detail`
		WHERE `id_order` = ' . (int) $id_order . '
		' . ($state == _PS_OS_MANUALREFUND_ ? '' : 'AND `id_order_detail` = ' . $id_order_detail) . '
		' . ($state != 0 ? 'AND `id_order_state` = ' . (int) $state : '');

		$result = Db::getInstance()->getRow($sql);
		return $result['amount'];
	}

	static public function getAllPartialRefundAmounts($id_order, $id_order_detail = NULL, $state = 0) {
		$sql = '
		SELECT current_refund_amt AS amount
		FROM `' . _DB_PREFIX_ . 'order_history_detail`
		WHERE `id_order` = ' . (int) $id_order . '
		' . ($state == _PS_OS_MANUALREFUND_ ? '' : 'AND `id_order_detail` = ' . $id_order_detail) . '
		' . ($state != 0 ? 'AND `id_order_state` = ' . (int) $state : '');

		$result = Db::getInstance()->ExecuteS($sql);

		foreach ($result as &$item) {
			$item = $item['amount'];
		}

		return $result;


	}


	static public function getCancelAmount($id_order, $state)
	{
//		echo
//		'SELECT SUM(amount +shipping_cost) AS cancel_amt
//		FROM `'._DB_PREFIX_.'order_history_detail`
//		WHERE `id_order` = '.(int)$id_order.'
//		AND `id_order_state` = '.(int)_PS_OS_CANCELED_.' ';

		$result = Db::getInstance()->getRow('
		SELECT SUM(amount + shipping_cost) AS cancel_amt
		FROM `' . _DB_PREFIX_ . 'order_history_detail`
		WHERE `id_order` = ' . (int) $id_order . '
		AND `id_order_state` = ' . (int) _PS_OS_CANCELED_ . ' '
		);
		return $result['cancel_amt'];
	}

	/* static public  function getOrderDetailCount($id_order)
	  {
	  $result = Db::getInstance()->getRow('
	  SELECT SUM(quantity) AS product_count
	  FROM `'._DB_PREFIX_.'order_history_detail`
	  WHERE `id_order` = '.(int)$id_order
	  );
	  return $result['product_count'];
	  } */

	static public function getOrderDetail($id_order_detail, $state = 0, $details = true, $id_lang = 4)
	{
		$sql = '
		SELECT ohd.*, os.color, osl.name
		FROM `' . _DB_PREFIX_ . 'order_history_detail` ohd
		LEFT JOIN `' . _DB_PREFIX_ . 'order_state` os ON (os.`id_order_state` = ohd.`id_order_state`)
		LEFT JOIN `' . _DB_PREFIX_ . 'order_state_lang` osl ON (osl.`id_order_state` = ohd.`id_order_state` AND osl.`id_lang` = ' . (int) $id_lang . ')
		WHERE ohd.`id_order_detail` = ' . (int) $id_order_detail . '
		' . ($state == 0 ? ' ' : 'AND ohd.`id_order_state` = ' . (int) $state) . '';

		$result = Db::getInstance()->ExecuteS($sql);

		if ($details) {
			if ($result)
				return $result;
			else
				return false;
		}
		else {
			foreach ($result as $res) {
				$id_history = $res['id_history'];
			}
			if (isset($id_history))
				return $id_history;
			else
				return false;
		}
	}

	static public function returnOrderStatus($id_order, $state, $type)
	{
		$result = Db::getInstance()->ExecuteS('
		SELECT `id_order_state`, `quantity`
		FROM `' . _DB_PREFIX_ . 'order_history_detail`
		WHERE `id_order` = ' . (int) $id_order . '
		' . ($type == 'Mcr' ? 'AND `id_order_state` = ' . $state : ' ') . ''
		);

		$credit_qty = 0;
		if ($result) {
			foreach ($result as $res) {
				if ($type == 'Mcr') {
					$credit_qty += $res['quantity'];
				} elseif ($res['id_order_state'] != $state)
					return $state;
			}
			if ($type == 'Mcr')
				return $credit_qty;
			if ($type == 'R')
				return _PS_OS_REFUND_;
			if ($type == 'E')
				return _PS_OS_EXCHANGE_;
			if ($type == 'Cr')
				return _PS_OS_FULLCREDITED_;
		}
		elseif ($type == 'Mcr')
			return false;
		else
			return $state;
	}

	static public function updateOrderDetail($id_history, $amount = 0.00, $quantity = 0, $shipping_cost = 0.00, $customer_rep_id)
	{
		$result = Db::getInstance()->getRow('
			UPDATE `' . _DB_PREFIX_ . 'order_history_detail` SET
			`amount` = `amount` + ' . $amount . '
			' . ($quantity > 0 ? ' ,`quantity` = `quantity` + ' . $quantity : '') . '
			,`current_refund_amt` = ' . $amount . '
			' . ($shipping_cost > 0 ? ' , shipping_cost  = ' . $shipping_cost : '') . '
			, `id_employee` = ' . $customer_rep_id . '
			WHERE `id_history` = ' . (int) $id_history
		);
		if ($result)
			return true;
		else
			return false;
	}

}

?>

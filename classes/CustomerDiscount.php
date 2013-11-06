<?php



class	CustomerDiscount extends ObjectModel
{
	/** @var integer discount id */
	public 		$id;

    /** @var integer discount type */
	public $id_discount_type;
	
	/** @var integer customer id */
	public 		$id_customer;

	/** @var string discount validity,till validity date */
	public 		$valid_upto;

	protected $tables = array ('customer_discount');

	protected 	$table = 'customer_discount';
	protected 	$identifier = 'id_discount';

	public function getFields()
	{
		parent::validateFields();
		if (isset($this->id))
			$fields['id_customer_discount'] = intval($this->id);
		$fields['id_discount'] = intval($this->id_discount);
		$fields['id_discount_type'] = intval($this->id_discount_type);
		$fields['id_customer'] = intval($this->id_customer);
		$fields['valid_upto'] = pSQL($this->valid_upto);

		return $fields;
	}

	static public function customerDiscountExists($id_customer, $discount_type)
	{
		$result = Db::getInstance()->getRow('
		SELECT id_discount
		FROM `'._DB_PREFIX_	.'customer_discount`
		WHERE `id_customer` = '.intval($id_customer).'
		AND `id_discount_type` = '.intval($discount_type)
		);
		if($result)
			return true;
		else
			return false;
	}

	static public function getCustomerDiscountValidity($id_discount,$discount_type,$id_customer)
	{
		$result = Db::getInstance()->getRow('
		SELECT valid_upto
		FROM `'._DB_PREFIX_	.'customer_discount`
		WHERE `id_discount` = '.intval($id_discount).'
		AND `id_discount_type` = '.intval($discount_type).'
		AND `id_customer` = '.intval($id_customer)
		);
		if($result)
			return $result['valid_upto'];
		else
			return false;
	}
}
?>

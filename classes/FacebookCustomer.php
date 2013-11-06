<?php

class FacebookCustomerCore extends ObjectModel
{
	public 		$id_login;

	public 		$id_customer;

	/** @var string Determine employee profile */
	public 		$oauth_uid;

	/** @var string Lastname */
	//public 		$lastname;

	/** @var string Firstname */
	//public 		$firstname;

	/** @var string e-mail */
	//public 		$email;

	/** @var string Password */
	//public 		$password;

 	protected 	$fieldsRequired = array('id_customer', 'oauth_uid');
// 	protected 	$fieldsSize = array('lastname' => 32, 'firstname' => 32, 'email' => 128, 'password' => 32);
// 	protected 	$fieldsValidate = array('lastname' => 'isName', 'firstname' => 'isName', 'email' => 'isEmail',
//		'password' => 'isPasswd');

	protected 	$table = 'customer_fb';
	protected 	$identifier = 'id_login';

	

	public	function getFields()
	{
	 	parent::validateFields();

		$fields['id_customer'] = (int)$this->id_customer;
		$fields['oauth_uid'] = (int)$this->oauth_uid;
		//$fields['lastname'] = pSQL($this->lastname);
		//$fields['firstname'] = pSQL(Tools::ucfirst($this->firstname));
		//$fields['email'] = pSQL($this->email);
		//$fields['password'] = pSQL($this->password);

		return $fields;
	}

	public static function checkUserExists($uid, $fb_register = true)
	{
		$result = Db::getInstance()->getRow('
		SELECT c.`id_customer`
		FROM `'._DB_PREFIX_.'customer_fb` cf
		LEFT JOIN `'._DB_PREFIX_.'customer` c ON(c.`id_customer` = cf.`id_customer`)
		WHERE '.($fb_register ? 'cf.`oauth_uid` = '.$uid : 'cf.`id_customer` = '.$uid).'
		');
		
		if ($result['id_customer'])
			return $result['id_customer'];
		else
			return false;

	}

	public static function customerStyleCategoryExists($id_customer)
	{
		if($id_customer = self::checkUserExists($id_customer,false))
		{
			$result = Db::getInstance()->getRow('
			SELECT cs.`id_customer`
			FROM `'._DB_PREFIX_.'customer_stylesurvey` cs
			WHERE cs.`id_customer` = '.$id_customer.' ');

			if ($result['id_customer'])
				return true;
			else
				return false;
		}
		else
			return true;
	}

}

?>

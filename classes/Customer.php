<?php

class CustomerCore extends ObjectModel
{
	public 		$id;

	/** @var string Secure key */
	public		$secure_key;

	/** @var string protected note */
	public		$note;

	/** @var integer Gender ID */
	public		$id_gender = 9;

	/** @var integer Default group ID */
	public		$id_default_group;

	/** @var string Lastname */
	public 		$lastname;

	/** @var string Firstname */
	public 		$firstname;

	/** @var string Birthday (yyyy-mm-dd) */
	public 		$birthday = NULL;

	/** @var string e-mail */
	public 		$email;

	/** @var boolean Newsletter subscription */
	public 		$newsletter;

	/** @var string Newsletter ip registration */
	public		$ip_registration_newsletter;

	/** @var string Newsletter ip registration */
	public		$newsletter_date_add;

	/** @var boolean Opt-in subscription */
	public 		$optin;

	/** @var integer Password */
	public 		$passwd;

	/** @var datetime Password */
	public $last_passwd_gen;

	/** @var boolean Status */
	public 		$active = true;

	/** @var boolean Status */
	public 		$is_guest = 0;

	/** @var boolean True if carrier has been deleted (staying in database as deleted) */
	public 		$deleted = 0;

	/** @var string Object creation date */
	public 		$date_add;

	/** @var string Object last modification date */
	public 		$date_upd;

//	public		$years;
//	public		$days;
//	public		$months;

	/** @var string Customer Personal Style */
	public		$category_name;

	/** @var int  Customer age */
	public 		$age;

	/** @var int  Customer shoe size */
	public 		$shoe_size;

	/** @var int  Customer dress size */
	public 		$dress_size;

	//public		$ready;

	/** @var int  Customer has seen popup */
	public	    $showroom_seen;
	//public	    $combination;

	/**
	* @var int Control there is any purhaced order before.
	* Seting 1 when order status shipped that user created.
	*/
	public $placed_order;

	public $citizen_id;

	protected $tables = array ('customer');

 	protected 	$fieldsRequired = array('passwd', 'email');
 	protected 	$fieldsSize = array('passwd' => 32, 'email' => 128);
 	protected 	$fieldsValidate = array('secure_key' => 'isMd5', 'lastname' => 'isName', 'firstname' => 'isName', 'email' => 'isEmail', 'passwd' => 'isPasswd',
		 'id_gender' => 'isUnsignedId', 'birthday' => 'isBirthDate', 'newsletter' => 'isBool', 'optin' => 'isBool', 'active' => 'isBool', 'note' => 'isCleanHtml',
		  'is_guest' => 'isBool', 'placed_order' => 'isBool', 'citizen_id' => 'isInt');

	protected	$webserviceParameters = array(
		'fields' => array(
			'id_default_group' => array('xlink_resource' => 'groups'),
			'newsletter_date_add' => array(),
			'ip_registration_newsletter' => array(),
			'last_passwd_gen' => array('setter' => null),
			'secure_key' => array('setter' => null),
			'deleted' => array(),
			'passwd' => array('setter' => 'setWsPasswd'),
		),
	);

	protected 	$table = 'customer';
	protected 	$identifier = 'id_customer';

	protected static $_defaultGroupId = array();
	protected static $_customerHasAddress = array();

	public function getFields()
	{
		parent::validateFields();
		if (isset($this->id))
			$fields['id_customer'] = (int)($this->id);
		$fields['secure_key'] = pSQL($this->secure_key);
		$fields['note'] = pSQL($this->note, true);
		$fields['id_gender'] = (int)($this->id_gender);
		$fields['id_default_group'] = (int)($this->id_default_group);
		$fields['lastname'] = pSQL($this->lastname);
		$fields['firstname'] = pSQL($this->firstname);
		$fields['birthday'] = pSQL($this->birthday);
		$fields['age'] = pSQL($this->age);
		$fields['category_name'] = pSQL($this->category_name);
		$fields['shoe_size'] = intval($this->shoe_size);
		$fields['dress_size'] = pSQL($this->dress_size);
		$fields['email'] = pSQL($this->email);
		$fields['newsletter'] = (int)($this->newsletter);
		$fields['newsletter_date_add'] = pSQL($this->newsletter_date_add);
		$fields['ip_registration_newsletter'] = pSQL($this->ip_registration_newsletter);
		$fields['optin'] = (int)($this->optin);
		$fields['passwd'] = pSQL($this->passwd);
		$fields['last_passwd_gen'] = pSQL($this->last_passwd_gen);
		$fields['active'] = (int)($this->active);
		$fields['date_add'] = pSQL($this->date_add);
		$fields['date_upd'] = pSQL($this->date_upd);
		$fields['is_guest'] = (int)($this->is_guest);
		$fields['deleted'] = (int)($this->deleted);
		$fields['optin'] = intval($this->optin);
		$fields['placed_order'] = intval($this->placed_order);
		$fields['citizen_id'] = intval($this->citizen_id);

		return $fields;
	}

	public function add($autodate = true, $nullValues = true)
	{
		$this->secure_key = md5(uniqid(rand(), true));
		$this->last_passwd_gen = date('Y-m-d H:i:s', strtotime('-'.Configuration::get('PS_PASSWD_TIME_FRONT').'minutes'));
		if (empty($this->id_default_group))
			$this->id_default_group = 1;
		/* Can't create a guest customer, if this feature is disabled */
		if ($this->is_guest AND !Configuration::get('PS_GUEST_CHECKOUT_ENABLED'))
			return false;
	 	if (!parent::add($autodate, $nullValues))
			return false;

		$row = array('id_customer' => (int)($this->id), 'id_group' => (int)$this->id_default_group);
		return Db::getInstance()->AutoExecute(_DB_PREFIX_.'customer_group', $row, 'INSERT');
	}

	public function update($nullValues = false)
	{
		//$this->birthday = (empty($this->years) ? $this->birthday : (int)$this->years.'-'.(int)$this->months.'-'.(int)$this->days);
		if ($this->newsletter AND !$this->newsletter_date_add)
			$this->newsletter_date_add = date('Y-m-d H:i:s');
	 	return parent::update(false);
	}

	public function delete()
	{
		$addresses = $this->getAddresses((int)(Configuration::get('PS_LANG_DEFAULT')));
		foreach ($addresses AS $address)
		{
			$obj = new Address((int)($address['id_address']));
			$obj->delete();
		}
		Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'customer_group` WHERE `id_customer` = '.(int)($this->id));
		Discount::deleteByIdCustomer((int)($this->id));
		return parent::delete();
	}

	/**
	  * Return customers list
	  *
	  * @return array Customers
	  */
	public static function getCustomers()
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT `id_customer`, `email`, `firstname`, `lastname`
		FROM `'._DB_PREFIX_.'customer`
		ORDER BY `id_customer` ASC');
	}

	/**
	  * Return customer instance from its e-mail (optionnaly check password)
	  *
	  * @param string $email e-mail
	  * @param string $passwd Password is also checked if specified
	  * @return Customer instance
	  */
	public function getByEmail($email, $passwd = NULL)
	{
	 	if (! Validate::isEmail($email) OR ($passwd AND ! Validate::isPasswd($passwd))) {
	 		die (Tools::displayError('Either email or password is invalid!'));
	 	}

		$result = Db::getInstance()->getRow('
		SELECT *
		FROM `'._DB_PREFIX_	.'customer`
		WHERE `active` = 1
		AND `email` = \''.pSQL($email).'\'
		'.(isset($passwd) ? 'AND `passwd` = \''.md5(pSQL(_COOKIE_KEY_.$passwd)).'\'' : '').'
		AND `deleted` = 0
		AND `is_guest` = 0');

		if (!$result)
			return false;
		$this->id = $result['id_customer'];
		foreach ($result AS $key => $value)
			if (key_exists($key, $this))
				$this->{$key} = $value;

		return $this;
	}

	public static function getIdByEmail($email) {
		$sql = 'SELECT id_customer FROM `'._DB_PREFIX_	.'customer`
		WHERE `active` = 1
		AND `email` = \''.pSQL($email).'\'';
		return Db::getInstance()->getValue($sql);
	}

	/**
	  * Check id the customer is active or not
	  *
	  * @return boolean customer validity
	  */
	public static function isBanned($id_customer)
	{
	 	if (!Validate::isUnsignedId($id_customer))
			return true;
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
		SELECT `id_customer`
		FROM `'._DB_PREFIX_.'customer`
		WHERE `id_customer` = \''.(int)($id_customer).'\'
		AND active = 1
		AND `deleted` = 0');
		if (isset($result['id_customer']))
			return false;
        return true;
	}

	/**
	 * Checks if the customer has already completed the
	 * survey or not.
	 *
	 * @return boolean survey completion indication
	 */
	public function hasCompletedSurvey() {
		if (! $this->id) {
			return false;
		}

		// Not checking for old customers to not force them
		// to take survey again
		$date_after = '2012-09-17 00:00:00';
		// Check if customer has taken survey (when category_name
		// is not empty that means customer has filled the survey)
		$result = Db::getInstance()->getRow('
			SELECT `' . $this->identifier . '`
			FROM `' . _DB_PREFIX_ . $this->table . '`
			WHERE `' . $this->identifier . '` = ' . (int)($this->id) . '
				AND `date_add` >= \'' . $date_after . '\'
				AND `category_name` IS NOT NULL AND `category_name` != ""
		');

		// Row returned means has taken survey
		return isset($result['id_customer']);
	}

	/**
	  * Check if e-mail is already registered in database
	  *
	  * @param string $email e-mail
	  * @param $return_id boolean
	  * @param $ignoreGuest boolean, for exclure guest customer
	  * @return Customer ID if found, false otherwise
	  */
	public static function customerExists($email, $return_id = false, $ignoreGuest = true)
	{
	 	if (! Validate::isEmail($email)) {
	 		die (Tools::displayError('Given email is invalid: ' . $email));
	 	}

		$result = Db::getInstance()->getRow('
		SELECT `id_customer`
		FROM `'._DB_PREFIX_.'customer`
		WHERE `email` = \''.pSQL($email).'\''
		.($ignoreGuest ? 'AND `is_guest` = 0' : ''));

		if ($return_id)
			return (int)($result['id_customer']);
		else
			return isset($result['id_customer']);
	}

	/**
	  * Check if, except current customer, someone else registered this e-email
	  *
	  * @return integer Number of customers who have also this e-mail
	  * @deprecated
	  */
	public function cantChangeemail()
	{
	 	Tools::displayAsDeprecated();

		if (! Validate::isEmail($this->email)) {
	 		die (Tools::displayError('Given e-mail is invalid: ' . $this->email));
	 	}

		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
		SELECT COUNT(`id_customer`) AS total
		FROM `'._DB_PREFIX_.'customer`
		WHERE `email` = \''.pSQL($this->email).'\' AND `id_customer` != '.(int)($this->id));

		return $result['total'];
	}

	/**
	  * Check if an address is owned by a customer
	  *
	  * @param integer $id_customer Customer ID
	  * @param integer $id_address Address ID
	  * @return boolean result
	  */
	public static function customerHasAddress($id_customer, $id_address)
	{
		if (!array_key_exists($id_customer, self::$_customerHasAddress))
		{
			self::$_customerHasAddress[$id_customer] = (bool)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
			SELECT `id_address`
			FROM `'._DB_PREFIX_.'address`
			WHERE `id_customer` = '.(int)($id_customer).'
			AND `id_address` = '.(int)($id_address).'
			AND `deleted` = 0');
		}
		return self::$_customerHasAddress[$id_customer];
	}

	public static function resetAddressCache($id_customer)
	{
		if (array_key_exists($id_customer, self::$_customerHasAddress))
			unset(self::$_customerHasAddress[$id_customer]);
	}

	/**
	  * Return customer addresses
	  *
	  * @param integer $id_lang Language ID
	  * @return array Addresses
	  */
	public function getAddresses($id_lang)
	{
		$sql = '
		SELECT a.*, cl.`name` AS country, s.name AS state, s.iso_code AS state_iso
		FROM `'._DB_PREFIX_.'address` a
		LEFT JOIN `'._DB_PREFIX_.'country` c ON (a.`id_country` = c.`id_country`)
		LEFT JOIN `'._DB_PREFIX_.'country_lang` cl ON (c.`id_country` = cl.`id_country`)
		LEFT JOIN `'._DB_PREFIX_.'state` s ON (s.`id_state` = a.`id_state`)
		WHERE `id_lang` = '.(int)($id_lang).' AND `id_customer` = '.(int)($this->id).' AND a.`deleted` = 0';
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql);
	}


	/**
	  * Returns customer last connections
	  *
	  * @param integer $nb Number of connections wanted
	  * @return array Connections
	  */
	public function getConnections($nb = 10)
	{
		Tools::displayAsDeprecated();

		return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT `ip_address`, `date_add`
		FROM `'._DB_PREFIX_.'connections`
		WHERE `id_guest` IN (SELECT `id_guest` FROM `'._DB_PREFIX_.'guest` WHERE `id_customer` = '.(int)($this->id).')
		ORDER BY `date_add` DESC
		LIMIT 0,'.(int)($nb));
	}

	/**
	  * Count the number of addresses for a customer
	  *
	  * @param integer $id_customer Customer ID
	  * @return integer Number of addresses
	  */
	public static function getAddressesTotalById($id_customer)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
		SELECT COUNT(a.`id_address`)
		FROM `'._DB_PREFIX_.'address` a
		WHERE a.`id_customer` = '.(int)($id_customer).'
		AND a.`deleted` = 0');
	}

	/**
	  * Check if customer password is the right one
	  *
	  * @param string $passwd Password
	  * @return boolean result
	  */
	public static function checkPassword($id_customer, $passwd)
	{
	 	if (! Validate::isUnsignedId($id_customer) OR ! Validate::isMd5($passwd)) {
	 		die (Tools::displayError('Error: 201306051439'));
	 	}

		return (bool)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
		SELECT `id_customer`
		FROM `'._DB_PREFIX_.'customer`
		WHERE `id_customer` = '.(int)($id_customer).'
		AND `passwd` = \''.pSQL($passwd).'\'');
	}

	/**
	  * Return customers who have subscribed to the newsletter
	  *
	  * @return array Customers
	  * @deprecated
	  */
	public static function getNewsletteremails()
	{
		Tools::displayAsDeprecated();
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT `email`, `firstname`, `lastname`, `newsletter`, `ip_registration_newsletter`, `newsletter_date_add`
		FROM `'._DB_PREFIX_.'customer`
		WHERE `newsletter` = 1
		AND `active` = 1');
	}

	/**
	  * Return the number of customers who registered today
	  *
	  * @return integer number of customers who registered today
	  * @deprecated
	  */
	public static function getTodaysRegistration()
	{
		Tools::displayAsDeprecated();
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
		SELECT COUNT(`id_customer`) as nb
		FROM `'._DB_PREFIX_.'customer`
		WHERE DAYOFYEAR(`date_add`) = DAYOFYEAR(NOW())
		AND YEAR(`date_add`) = YEAR(NOW())');
		if (!$result['nb'])
			return '0';
		return $result['nb'];
	}

	/**
	  * Return the number of customers who registered two days back
	  * @param string $date date
	  * @return array Corresponding customers who registered two days back
	  */
	public static function getSHowroomCustomersRegistrationByDate($date)
	{
		$sql = "SELECT `id_customer`, `email`, `firstname`, `lastname`
			FROM `" . _DB_PREFIX_ . "customer`
			WHERE `date_add` > DATE_ADD('" . pSQL($date) . "', INTERVAL -2 DAY)
				AND `date_add` <= '" . pSQL($date) . "'
				AND `category_name` != ''
		    	AND `showroom_seen` = 0
	    ";
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql);
		return $result;
	}

	/**
	  * Light back office search for customers
	  *
	  * @param string $query Searched string
	  * @return array Corresponding customers
	  */
	public static function searchByName($query)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT c.*
		FROM `'._DB_PREFIX_.'customer` c
		WHERE c.`email` LIKE \'%'.pSQL($query).'%\'
		OR c.`id_customer` LIKE \'%'.pSQL($query).'%\'
		OR c.`lastname` LIKE \'%'.pSQL($query).'%\'
		OR c.`firstname` LIKE \'%'.pSQL($query).'%\'');
	}

	/**
	  * Return several useful statistics about customer
	  *
	  * @return array Stats
	  */
	public function getStats()
	{
		$result = Db::getInstance()->getRow('
		SELECT COUNT(`id_order`) AS nb_orders, SUM(`total_paid` / o.`conversion_rate`) AS total_orders
		FROM `'._DB_PREFIX_.'orders` o
		WHERE o.`id_customer` = '.(int)($this->id).'
		AND o.valid = 1');

		$result2 = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
		SELECT MAX(c.`date_add`) AS last_visit
		FROM `'._DB_PREFIX_.'guest` g
		LEFT JOIN `'._DB_PREFIX_.'connections` c ON c.id_guest = g.id_guest
		WHERE g.`id_customer` = '.(int)($this->id));

		$result3 = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
		SELECT (YEAR(CURRENT_DATE)-YEAR(c.`birthday`)) - (RIGHT(CURRENT_DATE, 5)<RIGHT(c.`birthday`, 5)) AS age
		FROM `'._DB_PREFIX_.'customer` c
		WHERE c.`id_customer` = '.(int)($this->id));

		$result['last_visit'] = $result2['last_visit'];
		$result['age'] = ($result3['age'] != date('Y') ? $result3['age'] : '--');
		return $result;
	}

	public function getLastConnections()
    {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
        SELECT c.date_add, COUNT(cp.id_page) AS pages, TIMEDIFF(MAX(cp.time_end), c.date_add) as time, http_referer,INET_NTOA(ip_address) as ipaddress
        FROM `'._DB_PREFIX_.'guest` g
        LEFT JOIN `'._DB_PREFIX_.'connections` c ON c.id_guest = g.id_guest
        LEFT JOIN `'._DB_PREFIX_.'connections_page` cp ON c.id_connections = cp.id_connections
        WHERE g.`id_customer` = '.(int)($this->id).'
        GROUP BY c.`id_connections`
        ORDER BY c.date_add DESC
        LIMIT 10');
    }

	/**
	  * Return last cart ID for this customer
	  *
	  * @return integer Cart ID
	  * @deprecated
	  */
	public function getLastCart()
	{
		Tools::displayAsDeprecated();
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
		SELECT MAX(c.`id_cart`) AS id_cart
		FROM `'._DB_PREFIX_.'cart` c
		WHERE c.`id_customer` = '.(int)($this->id));
		if (isset($result['id_cart']))
			return $result['id_cart'];
		return false;
	}
	/*
	* Specify if a customer already in base
	*
	* @param $id_customer Customer id
	* @return boolean
	*/
	// DEPRECATED
	public function customerIdExists($id_customer)
	{
		return self::customerIdExistsStatic((int)($id_customer));
	}

	public static function customerIdExistsStatic($id_customer)
	{
		$row = Db::getInstance()->getRow('
		SELECT `id_customer`
		FROM '._DB_PREFIX_.'customer c
		WHERE c.`id_customer` = '.(int)($id_customer));

		return isset($row['id_customer']);
	}

	public function cleanGroups()
	{
		Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'customer_group` WHERE `id_customer` = '.(int)($this->id));
	}

	public function addGroups($groups)
	{
		foreach ($groups as $group)
		{
			$row = array('id_customer' => (int)($this->id), 'id_group' => (int)($group));
			Db::getInstance()->AutoExecute(_DB_PREFIX_.'customer_group', $row, 'INSERT');
		}
	}

	public static function getGroupsStatic($id_customer)
	{
		$groups = array();
		$result = Db::getInstance()->ExecuteS('
		SELECT cg.`id_group`
		FROM '._DB_PREFIX_.'customer_group cg
		WHERE cg.`id_customer` = '.(int)($id_customer));
		foreach ($result AS $group)
			$groups[] = (int)($group['id_group']);
		return $groups;
	}

	public function getGroups()
	{
		return self::getGroupsStatic((int)($this->id));
	}

	public function isUsed()
	{
		return false;
	}

	/**
	 * @param int $id_group
	 * @return int
	 * @deprecated
	 */
	public function isMemberOfGroup($id_group)
	{
		Tools::displayAsDeprecated();
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow('
		SELECT count(cg.`id_group`) as nb
		FROM '._DB_PREFIX_.'customer_group cg
		WHERE cg.`id_customer` = '.(int)($this->id).'
		AND cg.`id_group` = '.(int)($id_group));

		return $result['nb'];
	}
	/**/
	/*
	* Find out to which group a customer belongs
	*
	* @param $id_customer Customer id
	* @return the group id to which the customer belongs
	*/
	static public function memberOfGroup($id_customer)
	{
        $result = Db::getInstance()->getRow('
		SELECT cg.`id_group`
		FROM '._DB_PREFIX_.'customer_group cg
		WHERE cg.`id_customer` = '.intval($id_customer));
		return intval($result['id_group']);
	}

	public function getBoughtProducts($pagination = false, $limit = 10, $page = 1) {
		$sql = '
		SELECT '.($pagination ? 'SQL_CALC_FOUND_ROWS ' : '').' * FROM `'._DB_PREFIX_.'orders` o
		LEFT JOIN `'._DB_PREFIX_.'order_detail` od ON o.id_order = od.id_order
		WHERE o.valid = 1 AND o.`id_customer` = '.(int)($this->id)
		.' ORDER BY o.`date_add` DESC '
		.($pagination ? 'LIMIT '.(((int)($page) - 1) * (int)($limit)).', '.(int)($limit) : '');

		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql);

        if ($pagination) {
            $result['totalItem'] = (int) Db::getInstance()->getValue('SELECT FOUND_ROWS() as rowCount');
        }

        return $result;

	}

	/**
	 * @deprecated
	 * @return bool
	 */
	public function getNeedDNI() {
		Tools::displayAsDeprecated();

		return false;
	}

	public static function getDefaultGroupId($id_customer) {
		if (! isset(self::$_defaultGroupId[(int)($id_customer)])) {
			self::$_defaultGroupId[(int)($id_customer)] = Db::getInstance()->getValue('
				SELECT `id_default_group`
				FROM `' . _DB_PREFIX_ . 'customer`
				WHERE `id_customer` = ' . (int) $id_customer
			);
		}

		return self::$_defaultGroupId[(int)($id_customer)];
	}

	public static function getCurrentCountry($id_customer) {
		global $cart;

		if (! $cart OR ! $cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')}) {
			$id_address = (int) (Db::getInstance()->getValue('
				SELECT `id_address`
				FROM `' . _DB_PREFIX_ . 'address`
				WHERE `id_customer` = ' . (int) $id_customer . '
					AND `deleted` = 0
				ORDER BY `id_address`
			'));
		} else {
			$id_address = $cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')};
		}

		$ids = Address::getCountryAndState($id_address);

		return (int)($ids['id_country'] ? $ids['id_country'] : Configuration::get('PS_COUNTRY_DEFAULT'));
	}

	public function toggleStatus() {
		parent::toggleStatus();

		/* Change status to active/inactive */
		return Db::getInstance()->Execute('
			UPDATE `' . pSQL(_DB_PREFIX_ . $this->table) . '`
			SET `date_upd` = NOW()
			WHERE `' . pSQL($this->identifier) . '` = ' . (int) $this->id
		);
	}


	public function isGuest() {
		return (bool) $this->is_guest;
	}

	public function transformToCustomer($id_lang, $password = NULL) {
		if (! $this->isGuest()) {
			return false;
		}

		if (empty($password)) {
			$password = Tools::passwdGen();
		}

		if (! Validate::isPasswd($password)) {
			return false;
		}

		$this->is_guest = 0;
		$this->passwd = Tools::encrypt($password);

		if ($this->update()) {
			$vars = array(
				'{firstname}' => $this->firstname,
				'{lastname}' => $this->lastname,
			    '{email}' => $this->email,
			    '{passwd}' => $password
			);

			Mail::Send((int)$id_lang, 'guest_to_customer', Mail::l('Your guest account has been transformed to customer account'), $vars, $this->email, $this->firstname.' '.$this->lastname);

			return true;
		}
		return false;
	}

	public static function printNewsIcon($id_customer, $tr)
	{
		$customer = new Customer($tr['id_customer']);
		if (!Validate::isLoadedObject($customer))
			die(Tools::displayError('Invalid customer' . $id_customer));
		echo '<a href="index.php?tab=AdminCustomers&id_customer='.(int)($customer->id).'&changeNewsletterVal&token='.Tools::getAdminTokenLite('AdminCustomers').'">'.
				($customer->newsletter ? '<img src="../img/admin/enabled.gif" />' : '<img src="../img/admin/disabled.gif" />').
			'</a>';
	}

	public static function printOptinIcon($id_customer, $tr)
	{
		$customer = new Customer($tr['id_customer']);
		if (!Validate::isLoadedObject($customer))
			die(Tools::displayError('Invalid customer' . $id_customer));
		echo '<a href="index.php?tab=AdminCustomers&id_customer='.(int)($customer->id).'&changeOptinVal&token='.Tools::getAdminTokenLite('AdminCustomers').'">'.
				($customer->optin ? '<img src="../img/admin/enabled.gif" />' : '<img src="../img/admin/disabled.gif" />').
			'</a>';
	}

	public function setWsPasswd($passwd)
	{
		if ($this->id != 0)
		{
			if ($this->passwd != $passwd)
				$this->passwd = Tools::encrypt($passwd);
		}
		else
			$this->passwd = Tools::encrypt($passwd);
		return true;
	}

	/* update password */
    public function updatePassword($passwd)
	{
		//$row = array('passwd' => $passwd );
		//return Db::getInstance()->AutoExecute(_DB_PREFIX_.'customer', $row, 'UPDATE','email=""');
		$time = date('Y-m-d H:i:s', time());
		return Db::getInstance()->Execute('
				UPDATE `'._DB_PREFIX_.'customer`
				SET `passwd` = \''.md5(pSQL(_COOKIE_KEY_.$passwd)).'\', last_passwd_gen = \''.pSQL($time).'\'
				WHERE `id_customer` = '.$this->id);

	}

	public function addTags($id_customer, $tags, $tag_type, $priority, $clean = false)
	{
		if ($clean)
			self::cleanTags();

		if($priority == 1)
			$index = 1;
		foreach ($tags as $tag)
		{
			$row = array('id_customer' => intval($id_customer),
						'id_tag' => intval($tag),
						'tag_type' => pSQL($tag_type),
						'tag_priority' => ($priority == 1 ? intval($index++) : 1));
			Db::getInstance()->AutoExecute(_DB_PREFIX_.'customer_tag', $row, 'INSERT');
		}
	}

	public function getCategory()
	{
		return Db::getInstance()->getRow('
		SELECT c.`category_name`,sci.`shoes_cat_id`,sci.`shoes_seemore_cat_id`,sci.`shoes_featured_cat_id`,sci.`handbags_cat_id`,sci.`handbags_seemore_cat_id`,sci.jewelry_cat_id,sci.jewelry_seemore_cat_id
		FROM `'._DB_PREFIX_.'customer` c
		LEFT JOIN `'._DB_PREFIX_.'showroom_category_ids` sci ON (c.`category_name` = sci.`category_name`)
		WHERE c.`id_customer` = '.intval($this->id));
		/* if($result)
			return $result;
		else
			return false; */
	}

	/*public function getAllCategory()
	{
		return Db::getInstance()->ExecuteS('
		SELECT distinct(sci.`shoes_cat_id`)
		FROM `'._DB_PREFIX_.'showroom_category_ids` sci');
	}*/

//	public function getCategory()
//	{
//		$result = Db::getInstance()->getRow('
//		SELECT cc.`category_name` as name
//		FROM `'._DB_PREFIX_.'customer_category` cc
//		WHERE cc.`id_customer` = '.intval($this->id));
//		if($result)
//			return $result['name'];
//		else
//			return false;
//	}

	 /*update the field ie increment showroom_c if the customer visited*/
	public function updateShowroomSeen($value)
	{
		return Db::getInstance()->Execute('
				UPDATE `'._DB_PREFIX_.'customer`
				SET `showroom_seen` = '.$value.'
				WHERE `id_customer` = '.intval($this->id)
			);
	}

	static public function getShoeAttributeId($id_customer, $id_lang)
	{
		$result = Db::getInstance()->getRow('
		SELECT al.`id_attribute` as ai
		FROM `'._DB_PREFIX_.'attribute_lang` al
		LEFT JOIN `'._DB_PREFIX_.'customer` c ON al.`name` = c.`shoe_size`
		WHERE c.`id_customer` = '.intval($id_customer).' AND al.`id_lang` = '.intval($id_lang));
		return $result['ai'];
	}

	static public function getShoeSize($id_customer)
	{
		$result = Db::getInstance()->getRow('
		SELECT c.`shoe_size` as ss
		FROM
		`'._DB_PREFIX_.'customer` c
		WHERE c.`id_customer` = '.intval($id_customer));
		return $result['ss'];
	}


	/**
	 * Get Favlists number products by Customer ID
	 *
	 * @return array Results
	 */
	public static function getFavouriteProductsByIdCustomer($id_customer)
	{
		if (!Validate::isUnsignedId($id_customer))
			die (Tools::displayError('Invalid customer: ' . $id_customer));
		$result =  (Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT *
		FROM `'._DB_PREFIX_.'favlist_product` fp
		LEFT JOIN `'._DB_PREFIX_.'favlist` f ON (f.`id_favlist` = fp.`id_favlist`)
		WHERE f.`id_customer` = '.(int)($id_customer).''
		));

		if($result)
			return $result;
		else
			return false;
	}

	/*ShowRoom Disappear Start*/
	/*Return customer brought product Ids list, */
	public function customerBoughtProductslist($id_customer, $limit_by_days=false)
	{

		if($limit_by_days)
		{
			// Last 30 days list
			$days = 30;
			$now = time();
			$date_to = date('Y-m-d H:i:s', $now);
			$date_from = date('Y-m-d H:i:s', $now - $days*24*60*60);

			$query = '
				SELECT od.`product_id`, o.`date_add`
				FROM `bu_customer` c
				LEFT JOIN `'._DB_PREFIX_.'orders` o ON o.`id_customer` = c.`id_customer`
				LEFT JOIN `'._DB_PREFIX_.'order_detail` od ON od.`id_order` = o.`id_order`
				WHERE c.`id_customer`='.$id_customer.' AND o.`date_add` BETWEEN "'.$date_from.'" AND "'.$date_to.'"';
		}
		else
		{
			$query = '
				SELECT od.`product_id`, o.`date_add`
				FROM `bu_customer` c
				LEFT JOIN `'._DB_PREFIX_.'orders` o ON o.`id_customer` = c.`id_customer`
				LEFT JOIN `'._DB_PREFIX_.'order_detail` od ON od.`id_order` = o.`id_order`
				WHERE c.`id_customer`='.$id_customer;
		}

		$brought_products =  Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($query);

		foreach($brought_products as $product)
				$brought_product_ids[$product['product_id']] = $product['date_add'];

		if(!empty($brought_product_ids))
			return $brought_product_ids;
		else
			return array();

	}

	/*Disappear the price reduced products from the showrooms (showroom, LookBook, Ivana Botique),
	 * Suppose if these prodcuts are alreday bought by the customer.  */
	public function disappearDiscountedProducts($products)
	{
		global $cookie;
		// Discounted products list
		//$discount_product_ids = Product::getDiscountedProductslist();

		//List of Price Reduced Product ids.
		$price_reduced_product_ids = Product::getPriceReducedProductList();

		//List of Customer bought product ids.
		$customer_brought_products = $this->customerBoughtProductslist($cookie->id_customer,false);

		/*//List of product ids , which are price reduced and already bought by a customer.
		$customer_bought_price_reduced_product_ids = array_intersect($price_reduced_product_ids, $customer_brought_products);

		//unset the products form the supplied products, those are in price_reduced_product_ids.
		if(!empty($customer_bought_price_reduced_product_ids))
		{
			foreach($products as $key=>$product)
				if(in_array($product['id_product'], $customer_bought_price_reduced_product_ids))
						unset($products[$key]);
		}*/

		//List of product ids , which are price reduced and bought by a customer.
		$customer_bought_price_reduced_product_ids = array_intersect_key($price_reduced_product_ids, $customer_brought_products);

		//List of product ids , which are price reduced and already bought by a customer.
		$customer_already_bought_price_reduced_product_ids = array();

		//unset the products form the supplied products, those are in customer_already_bought_price_reduced_product_ids
		if(!empty($customer_bought_price_reduced_product_ids))
		{	$i = 0;
			foreach($customer_bought_price_reduced_product_ids as $product_id => $date_add)
			{
				if(strtotime($customer_brought_products[$product_id]) < strtotime($date_add ))
						$customer_already_bought_price_reduced_product_ids[$i] = $product_id;
				$i++;
			}
		}

		//unset the products form the supplied products, those are in price_reduced_product_ids.
		if(!empty($customer_already_bought_price_reduced_product_ids))
		{
			foreach($products as $key=>$product)
				if(in_array($product['id_product'], $customer_already_bought_price_reduced_product_ids))
						unset($products[$key]);
		}

		return $products;
	}
	/*ShowRoom Disappear END*/

        /*To check whether the customer is registered within the last two weeks
         * @param $id_customer is the Id of the customer
         */

        public static function isCustomerRegisteredBetween($id_customer,$fromDate, $toDate){
                $sql = 'SELECT `id_customer`
		FROM '._DB_PREFIX_.'customer c
		WHERE c.`id_customer` = '.(int)($id_customer).' AND c.`date_add` BETWEEN \''.$fromDate.'\' AND \''.$toDate.'\'';

		$row = Db::getInstance()->getRow($sql);

		return isset($row['id_customer']);
	}

}

?>

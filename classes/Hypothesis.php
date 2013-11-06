<?php

class HypothesisCore extends ObjectModel
{
	
	public $id;

	/** @var string description of Hypothesis */
	public $description;

	/** @var string design name  */
	public $design_name;

	/** @var string page name  */
	public $hypothesis_design;

	/** @var string name of the hypothesis  */
	public $name;
	
	/** @var string key value */
	public $cookie_key;

	/** @var string key value */
	public $guest_key;

	protected $tables = array ('customer_hypothesis');

	protected 	$fieldsRequired = array('cookie_key1', 'cookie_key2');

	protected 	$fieldsSize = array('description' => 32, 'cookie_key1' => 32, 'cookie_key2' => 32);

	protected 	$fieldsValidate = array('description' => 'isCleanHtml');

	protected 	$table = 'customer_hypothesis';
	
	protected 	$identifier = 'id_hypothesis';

	/** @var array COOKIE/GUEST KEYS cache */
	protected static $_HYPO_KEYS;
	
	/** @var array names of the HYPOTHESIS DESINGS cache */
	protected static $_HYPO_NAMES;

	/** @var array names of the HYPOTHESIS DESINGS cache */
	protected static $_HYPO_IDS_KEYS;


	public function getFields()
	{
		parent::validateFields();
		$fields['id_hypothesis'] = (int)($this->id);
		$fields['description'] = pSQL($this->description);
		$fields['design_name'] = pSQL($this->design_name);
		$fields['page_name'] = pSQL($this->page_name);
		$fields['page_name'] = pSQL($this->page_name);
		$fields['name'] = pSQL($this->name);
		$fields['cookie_key'] = pSQL($this->cookie_key1);
		$fields['guest_key'] = pSQL($this->cookie_key2);
		return $fields;
	}

	/* get the name for that particular hypothesis design.
	* returns name for that particular hypothesis design.
	* @params
	* $hypothesis_design - name of the hypothesis design
	* $state - whether the user id logged in(cookie) or logged out(guest)
	*/
	public static function setUpHypothesis($hypothesis_design, $state)
	{
		global $cookie;

		self::getHypothesisNames();
		self::getHypokeys($hypothesis_design);
		
	    if(empty($cookie->hypo_keys))
		{	//if the [hypo_keys] is not set in the global variable $cookie
			//or if a customer is not assigned any hypothesis key,then the customer is assigned  an hypothesis key and the name of the
			// hypothesis design.
			$hypothesis_name = self::setCookieValue($hypothesis_design,true,$state);
			
//			echo $hypothesis_name ;echo "<br/>";
//			echo 'for the first time.assign hypo';echo "<br/>";
//			exit;
		}
		else
		{
			if(!empty(self::$_HYPO_IDS_KEYS) AND isset(self::$_HYPO_IDS_KEYS[$cookie->id_customer]))
			{
				$hypothesis_name = 	self::$_HYPO_NAMES[$hypothesis_design][$state][self::$_HYPO_IDS_KEYS[$cookie->id_customer]];
				$cookie->hypo_keys .= '-'.$hypothesis_design.':'.self::$_HYPO_IDS_KEYS[$cookie->id_customer];//assign hypothesis key to the COOKIE.
				
//				echo $hypothesis_name ;echo "<br/>";
//				echo 'logged in again.get prev hypo assigned';echo "<br/>";
//				exit;
			}
			else
			{	//$cookie->hypo_keys is a string which stores values like 'page name:key of the hypothesis design-page name:key of the hypothesis design.'
				$tmpfields = explode('-', $cookie->hypo_keys);
				foreach($tmpfields as $pageAndCookie)
				{
					//extract keys of the hypothesis design for each page.
					$temp = explode(':', $pageAndCookie);
					if (sizeof($temp) == 2 AND in_array($temp[1] , self::$_HYPO_KEYS[$hypothesis_design][$state]))
						$cookie_key = $temp[1];
				}

				//if the [hypo_keys] is set in the global variable $cookie ,
				//check if the customer already has hypothesis key for the page he visits or the customer is already exposed to that hypothesis design
				// then the name of the hypothesis design he is already exposed is returned
				if( isset($cookie_key) AND in_array($cookie_key , self::$_HYPO_KEYS[$hypothesis_design][$state]))
				{
					$hypothesis_name = 	self::$_HYPO_NAMES[$hypothesis_design][$state][$cookie_key];
//					echo 'jkl25';

				}
				//else if the customer is not assigned any hypothesis key for the page he visits ,then assign hypothesis key
				//and return the name of the hypothesis design
				else
				{
					$hypothesis_name = self::setCookieValue($hypothesis_design,false,$state);

				}
//				echo 'jkl24';
			}
		}
//		echo 'testing2.enter for new customers';
		return $hypothesis_name ;

	}

	/* set the cookie value for hypothesis for each customer.
	* get the name for that particular hypothesis design.
	* returns name for that particular hypothesis design.
	* @params
	* $hypothesis_design - name of the hypothesis design
	* $first_time true or false
	* $state - whether the user id logged in(cookie) or logged out(guest)
	*/
	public static function setCookieValue($hypothesis_design,$first_time = false,$state)
	{	
		global $cookie;

		self::getHypothesisNames();

		//get the hypothesis key for the page the user visits from the configuration table
		//the hypothesis key in this case can be null or value of the hypothesis key that was assigned to the previous customer for that page
		$prev_cookie_val = Configuration::get('PS_'.strtoupper($hypothesis_design).'_'.strtoupper($state).'_KEY');
		
		$count = sizeof(self::$_HYPO_KEYS[$hypothesis_design][$state]);
		$key = 0;
		
		if(!$prev_cookie_val)
		{
			//if $prev_cookie_val is null,then the first hypothesis key in $_HYPO_KEYS is assigned.
			$cookie_value = self::$_HYPO_KEYS[$hypothesis_design][$state][$key];
		}
		else
		{
			//if a page has multiple hypothesis to be tested then each customer is exposed to only one hypothesis in a
			//cyclic manner so that all the hypothesis are equallt distributed among the shop customers
			foreach(self::$_HYPO_KEYS[$hypothesis_design][$state] as $index => $res)
			{
				if($prev_cookie_val == $res)//get the index of the previous hypothesis key.
				{
					if($index == $count-1)
						$key = 0; // make key zero if end of array reached.Go back to the beginning of the array.
					else
						$key = $index + 1;//increment the key value is end of array not reached.

				}
			}
			$cookie_value = self::$_HYPO_KEYS[$hypothesis_design][$state][$key];
		}

		if($first_time == true)
		{	//assign hypothesis key to the COOKIE along with page name the user has visited.(hypothesis keys not set in COOKIE)
			$cookie->hypo_keys = $hypothesis_design.':'.$cookie_value;
		}
		else
		{	//if  hypothesis keys of different pages is set in the COOKIE,then concatenate (hypothesis key and page name)
			//for the hypothesis design the user has never been exposed to.
			$cookie->hypo_keys .= '-'.$hypothesis_design.':'.$cookie_value;//assign hypothesis key to the COOKIE.
		}

		if($cookie->id_customer)
		{
			$customer_keys = array($cookie->id_customer => $cookie_value );
			$idAndkey = serialize($customer_keys);
			self::writeHypothesisString($hypothesis_design, $idAndkey);
		}
		
		Configuration::updateValue('PS_'.strtoupper($hypothesis_design).'_'.strtoupper($state).'_KEY', $cookie_value);
		$hypothesis_name = self::$_HYPO_NAMES[$hypothesis_design][$state][$cookie_value];

		return $hypothesis_name;
	}

	/*splitting the global $cookie value to extract the hypothesis key
	 * returns the hypothesis key assigned to the customer for that particular hypothesis design.
	 * @params
	 * $hypo_keys - string assinged to global $cookie->hypo_keys
	 * $hypothesis_design - name of the hypothesis design
	 * $state - whether the user id logged in(cookie) or logged out(guest)
	 */
	public static function splitHypoKeys($hypo_keys, $hypothesis_design, $state)
	{
		self::getHypothesisNames();
		$tmpfields = explode('-', $hypo_keys);
		foreach($tmpfields as $pageAndCookie)
		{
			//extract keys of the hypothesis design for each page.
			$temp = explode(':', $pageAndCookie);
			if (sizeof($temp) == 2 AND in_array($temp[1] , self::$_HYPO_KEYS[$hypothesis_design][$state]))
				$cookie_key = $temp[1];
		}
		if(isset($cookie_key))
		{
//			$hypothesis_name = self::$_HYPO_NAMES[$hypothesis_design][$state][$cookie_key];
//			$hypo_array = array($hypothesis_name,$cookie_key);
//			return $hypo_array;
			return $cookie_key;
		}
		else
			return false;
	}

	/*
	 * Get the name and key of each hypothesis design depending on whether the customer is logged in(cookie) or logged out(guest)
	 * caching in $_HYPO_NAMES ,$_HYPO_KEYS array
	 */
	public static function getHypothesisNames()
	{
		self::$_HYPO_KEYS = array();
		self::$_HYPO_NAMES = array();
		$result = Db::getInstance()->ExecuteS('SELECT `name`, `cookie_key`, `design_name`,`guest_key`
		FROM `'._DB_PREFIX_.'customer_hypothesis`'
		);
		/*
		 * Array $_HYPO_NAMES(names of the hypothesis design cache).It is formed accordingly.
		 * -If the customer is logged ,the names of the design hypothesis belonging to the respective pages are read from the database and stored in the array
		 * -If the customer is not logged ,the names of the design hypothesis belonging to the respective pages are read from the database and stored in the array
		 * 
		 * Array $_HYPO_KEYS(values of the hypothesis design cache).It is formed accordingly.
		 * -If the customer is logged ,the cookie keys belonging to the respective pages are read from the database and stored in the array
		 * -If the customer is not logged ,the guest keys belonging to the respective pages are read from the database and stored in the array
		 */
		foreach($result as $res)
		{
			self::$_HYPO_NAMES[$res['design_name']]['cookie'][$res['cookie_key']]= $res['name'];
			self::$_HYPO_NAMES[$res['design_name']]['guest'][$res['guest_key']] = $res['name'];
			self::$_HYPO_KEYS[$res['design_name']]['cookie'][] = $res['cookie_key'];
			self::$_HYPO_KEYS[$res['design_name']]['guest'][] = $res['guest_key'];
		}
	}

	/*
	 * Get the customer id and the hypothesis key assinged for each customer
	 * caching in $_HYPO_IDS_KEYS array
	 * @params
	 * $hypothesis_design - design name of the hypothesis
	 * $id_customer - id of the customer
	 */
	public static function getHypokeys($hypothesis_design,$id_customer = NULL)
	{
		$result = Db::getInstance()->ExecuteS('SELECT `id_customer`, `hypothesis_key`
		FROM `'._DB_PREFIX_.'hypothesis_values`
		WHERE `design_name` LIKE  \''.$hypothesis_design.'\''
		);
		//self::$_HYPO_IDS_KEYS = unserialize($result['hypothesis_key_string']);
		foreach($result as $res)
		self::$_HYPO_IDS_KEYS[$res['id_customer']] = $res['hypothesis_key'] ;
//		echo "vibh";print_r(self::$_HYPO_IDS_KEYS);
//		if($result)
//			return $result;
//		else
//			return false;

	}

   /*
    * store the id of the customer and key of the hypothesis design in an array
    * in the bu_hypothesis_values table.
    * @params
    * $hypothesis_design - design name of the hypothesis
    * $id_customer - id of the customer
    * $hypothesis_key - cookie key of the hypothesis
    * returns true or false
    */
	public static function insertCustomerIdAndKey($hypothesis_design, $id_customer ,$hypothesis_key )
	{
		$row = array('design_name' => $hypothesis_design, 'id_customer' => $id_customer, 'hypothesis_key' => $hypothesis_key);
		if(Db::getInstance()->AutoExecute(_DB_PREFIX_.'hypothesis_values', $row, 'INSERT'))
				return true;
	}

	/*
	 * store the id of the customer and key of the hypothesis design in an array
	 * in the bu_hypothesis_values table in serialized format.
	 * id_customer is the key and key is the value
	 * Concatenate space so that the array keys becomes a string.
	 * Values in the input array with numeric keys will be renumbered with incrementing keys starting from zero in the result array.
	 
	
	public static function writeHypothesisString($hypothesis_design, $idAndkey)
	{

		self::getHypokeys($hypothesis_design);
		if(!self::$_HYPO_IDS_KEYS)
		{
			$serialized = serialize($idAndkey);
			$row = array('design_name' => $hypothesis_design , 'hypothesis_key_string' => $serialized);
			 Db::getInstance()->AutoExecute(_DB_PREFIX_.'hypothesis_values', $row, 'INSERT');
		}
		else
		{
//			print_r(self::$_HYPO_IDS_KEYS );echo "<br/>";
//			print_r($idAndkey);echo "<br/>";
			$merged = array_merge(self::$_HYPO_IDS_KEYS , $idAndkey);
			//print_r($merged);echo "<br/>";
			$serialized = serialize($merged);
			//echo $serialized;exit;
			Db::getInstance()->Execute('
			UPDATE `'._DB_PREFIX_.'hypothesis_values`
			SET `hypothesis_key_string` = \''.$serialized.'\'
			where `design_name` LIKE \''.$hypothesis_design.'\'
			');
		}
	}*/

	
	/*
	 * hypothesis design can be there on multiple pages.In that case key of the hypothesis design is assigned on one page.
	 * In order to access the key in other pages page relation is maintained in bu_hypothesis_related_pages.
	 * returns the name of the hypothesis design.
	 * @params
	 * $page_name - name of the page user is visiting
	 * $state - whether the user id logged in(cookie) or logged out(guest)
	 */
	public static function returnRelatedHypothesis($page_name, $state)
	{
		global $cookie;
		$result = Db::getInstance()->getRow('SELECT `design_name`
		FROM `'._DB_PREFIX_.'hypothesis_related_pages`
		WHERE `page_name` LIKE  \''.$page_name.'\''
		);
		if($result)
		{
			self::getHypokeys($result['design_name']);
			if(isset(self::$_HYPO_IDS_KEYS[$cookie->id_customer]))
			{
				self::getHypothesisNames();
				$hypothesis_name = 	self::$_HYPO_NAMES[$result['design_name']][$state][self::$_HYPO_IDS_KEYS[$cookie->id_customer]];
				if(!empty($cookie->hypo_keys) )
					$customerkey  = self::splitHypoKeys($cookie->hypo_keys, $result['design_name'], $state);
				if(!isset($customerkey))
					$cookie->hypo_keys .= (empty($cookie->hypo_keys) ? '':'-').$result['design_name'].':'.self::$_HYPO_IDS_KEYS[$cookie->id_customer];//assign hypothesis key to the COOKIE.
//				echo $hypothesis_name ;echo "<br/>";
//				echo "br1"; print_r(self::$_HYPO_IDS_KEYS);echo "<br/>";
//				echo 'logged in again.ANOTHER FUNCTION';echo "<br/>";
				return $hypothesis_name;
			}
			else
				return false;
		}
		else
			return false;
	}
}
?>

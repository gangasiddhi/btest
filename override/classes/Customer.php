<?php

/**
 * This is an Customer class
 *
 * @author gangu.km
 */
class Customer extends CustomerCore
{

	public function getCustomerEmailByOrderid($order_id)
	{
		$query = 'SELECT c.`email`
				  FROM `' . _DB_PREFIX_ . 'customer` c
				  WHERE c.`id_customer` = (SELECT o.`id_customer` FROM `' . _DB_PREFIX_ . 'orders` o WHERE o.`id_order` = ' . $order_id . ')';
		$result = Db::getInstance()->getRow($query);

		return $result['email'];
	}

	public function getCustomerStylePoints($customerId)
	{
		$query = 'SELECT SUM(l.`points`) AS StylePoints
				  FROM `' . _DB_PREFIX_ . 'loyalty` l
				  WHERE l.`id_loyalty_state` IN ( 2, 5 ) AND l.`id_customer` = ' . $customerId;
		$result = Db::getInstance()->getRow($query);

		return $result['StylePoints'];
	}

	/* To get the Customer Details
	 *
	 * Return the customer Details */

	public function getCustomerDetails($customerId)
	{
		$query = "SELECT bc.id_customer, bc.email, bc.firstname AS 'first_name' , bc.lastname AS 'last_name', bc.id_default_group, bc.birthday,
            CONCAT_WS(' ', bc.firstname, bc.lastname) AS 'name',
            IFNULL(bc.age, '') AS 'age',
            IFNULL(bc.shoe_size, '') AS 'shoe_size',
            IFNULL(bc.dress_size, '') AS 'dress_size',
            IFNULL(bc.category_name, '') AS 'style_survey_result',
			IFNULL(
                (
                    SELECT bcs.`question10`
                    FROM `" . _DB_PREFIX_ . "customer_stylesurvey` bcs
                    WHERE bc.`id_customer` = bcs.`id_customer`
                ),
                ''
            ) AS 'color',
            IFNULL(
                (                    
                    SELECT SUM(l.`points`)
                    FROM `" . _DB_PREFIX_ . "loyalty` l
                    WHERE bc.`id_customer` = l.`id_customer` AND l.`id_loyalty_state` IN ( 2, 5 )
                ),
                0
            ) AS 'style_points',
            bc.date_add AS 'registration_date',
            IFNULL(
                (
                    SELECT bc.date_add
                    FROM `" . _DB_PREFIX_ . "guest` bg
                    RIGHT JOIN `" . _DB_PREFIX_ . "connections` bc ON (bg.id_guest = bc.id_guest)
                    WHERE bg.id_customer = bc.id_customer
                    ORDER BY bc.date_add desc
                    LIMIT 1
                ),
                ''
            ) AS 'last_visit_date',
			IFNULL(
                (                    
                    SELECT con.http_referer
					FROM `" . _DB_PREFIX_ . "guest` g
					LEFT JOIN `" . _DB_PREFIX_ . "connections` con ON (con.id_guest = g.id_guest)
					WHERE g.id_customer = bc.id_customer AND con.http_referer IS NOT NULL
					ORDER BY con.date_add ASC
                    LIMIT 1
                ),
                ''
            ) AS 'source'
        FROM `" . _DB_PREFIX_ . "customer` bc
        WHERE bc.active = 1 AND bc.id_customer = " . $customerId;
		
		$result = Db::getInstance()->getRow($query);

		return $result;
	}

	/*Get the customer birthdate
	 * @param $id_customer (int) is customer id
	 */
	public function getCustomerBirthdate($id_customer)
	{

		$query = 'SELECT birthday
				  FROM `' . _DB_PREFIX_ . 'customer`
				  WHERE id_customer ` = ' . $id_customer;
		$result = Db::getInstance()->getRow($query);

		return $result['birthday'];
	}

	/*Get the customers who have the birthday today
	 * @param $month is current month (Ex: 04 for April, 05 for may, etc)
	 * @param $date is today date (Ex: 01, 02, ..., 31)
	 */
	public function getTodayBirthdayCustomerDetails($month, $date)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('SELECT c.id_customer, c.email
				  FROM `' . _DB_PREFIX_ . 'customer` c
				  WHERE c.birthday LIKE "%-' . $month . '-' . $date . '"');
	}

	public static function getCustomerOrdersInTheLastYearFromToday($id_lang, $id_customer, $startDate, $endDate, $showHiddenStatus = false)
    {
        $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
        SELECT o.*, (SELECT SUM(od.`product_quantity`) FROM `'._DB_PREFIX_.'order_detail` od WHERE od.`id_order` = o.`id_order`) nb_products
        FROM `'._DB_PREFIX_.'orders` o
        WHERE o.`id_customer` = '.(int)$id_customer.' AND o.date_add BETWEEN "'.$startDate.'" AND "'.$endDate.'"
        GROUP BY o.`id_order`
        ORDER BY o.`date_add` DESC');
        if (!$res)
            return array();

        foreach ($res AS $key => $val)
        {
            $res2 = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
            SELECT os.`id_order_state`, osl.`name` AS order_state, os.`invoice`
            FROM `'._DB_PREFIX_.'order_history` oh
            LEFT JOIN `'._DB_PREFIX_.'order_state` os ON (os.`id_order_state` = oh.`id_order_state`)
            INNER JOIN `'._DB_PREFIX_.'order_state_lang` osl ON (os.`id_order_state` = osl.`id_order_state` AND osl.`id_lang` = '.(int)$id_lang.')
            WHERE oh.`id_order` = '.(int)($val['id_order']).(!$showHiddenStatus ? ' AND os.`hidden` != 1' : '').'
            ORDER BY oh.`date_add` DESC, oh.`id_order_history` DESC
            LIMIT 1');
            if ($res2)
                $res[$key] = array_merge($res[$key], $res2[0]);
        }
        return $res;
    }
	
	public function getCustomerRegisteredSource($customerId){
		
		$query = "SELECT con.http_referer AS refered_link
				FROM `" . _DB_PREFIX_ . "connections` con
				LEFT JOIN `" . _DB_PREFIX_ . "guest` g ON (g.id_guest = con.id_guest)
				LEFT JOIN `" . _DB_PREFIX_ . "customer` c ON (c.id_customer = g.id_customer)
				WHERE c.id_customer = ".$customerId." AND con.http_referer != ''
				ORDER BY con.date_add ASC ";

		/*This should be read from the master because, this is instantly checked after the customer registered*/
		$res = Db::getInstance()->getRow($query);
		
		return $res['refered_link'];
	}
	
	public function getEmailById($customerId){
		$query = "SELECT c.email
				FROM `" . _DB_PREFIX_ . "customer` c
				WHERE c.active = 1 AND c.id_customer = ".$customerId;
		
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($query);
		
		return $result['email'];
	}
	
	
	public function getCustomerLastPurchaseDate($customerId = null){
		if($customerId == null){
			$customerId = $this->id;
		}
		
		$sql = 'SELECT MAX(o.date_add) as LastPuschaseDate
			    FROM `' . _DB_PREFIX_ . 'customer` c
				LEFT JOIN `' . _DB_PREFIX_ . 'orders` o ON (o.id_customer = c.id_customer)
				WHERE c.id_customer = '.$customerId.' AND o.valid = 1' ;
		
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
		
		return $result['LastPuschaseDate'];
		
	}
	
	public function isCustomerPurchasedTheProduct($customerId,$productId, $productAttributeId ){
		$sql = 'SELECT od.id_order
				FROM `'._DB_PREFIX_.'orders` o
				LEFT JOIN `'._DB_PREFIX_.'order_detail` od ON (o.id_order = od.id_order)
				WHERE o.id_customer = '.$customerId.' AND od.product_id = '.$productId.' AND od.product_attribute_id = '.$productAttributeId;
		
		$result = Db::getInstance()->getRow($sql);
		
		if(empty($result)){
			return FALSE;
		} else {
			return TRUE;
		}
	}
}

?>

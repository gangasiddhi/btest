<?php

class CustomerStockRemainderCore extends ObjectModel
{

	public $id;

	/* Customer Id */
	public $id_customer;

	/* Customer choosen  id_product */
	public $id_product;
	
	/* Customer choosen product attribute id */
	public $id_product_attribute;

	/* Customer choosen shoe size */
	public $shoe_size;
	
	/* Status of the Stock Alarm  sent */
	public $status;

	/** @var string Object creation date */
	public $date_add;

	/** @var string Object last modification date */
	public $date_upd;
	
	protected $table = 'customer_stock_remainder';
	protected $identifier = 'id';
	
	protected $fieldsRequired = array('id_customer', 'id_product_attribute');
	protected $fieldsValidate = array('id_customer' => 'isUnsignedId');

	public function getFields()
	{
		parent::validateFields();
		$fields['id_customer'] = (int) $this->id_customer;
		$fields['id_product'] = (int) $this->id_product;
		$fields['id_product_attribute'] = (int) $this->id_product_attribute;
		$fields['shoe_size'] = (int) $this->shoe_size;
		$fields['status'] = (int) $this->status;
		$fields['date_add'] = pSQL($this->date_add);
		$fields['date_upd'] = pSQL($this->date_upd);
		return $fields;
	}

	public static function isCustomerIdExists($customerId)
	{
		$query = 'SELECT `id`
				  FROM `' . _DB_PREFIX_ . 'customer_stock_remainder`
				  WHERE `id_customer` = ' . $customerId;
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($query);

		return $result['id'];
	}

	public function getRemainderCustomersForThisProduct($productAttributeId)
	{
		$query = 'SELECT `id_customer`
				  FROM `' . _DB_PREFIX_ . 'customer_stock_remainder`
				  WHERE status = 1 AND id_product_attribute = ' . $productAttributeId;

		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($query);

		return $result;
	}

	public function getCustomerSelectedShoeSizes($customerId)
	{
		$query = 'SELECT `id_product_attribute`
				  FROM `' . _DB_PREFIX_ . 'customer_stock_remainder`
				  WHERE status = 1 AND id_customer = ' . $customerId;

		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($query);

		return $result;
	}

	public function isCustomerAlreadyChoosen($customerId, $productAttributeId)
	{
		$query = 'SELECT `id`
				  FROM `' . _DB_PREFIX_ . 'customer_stock_remainder`
				  WHERE status = 1 AND id_customer = ' . $customerId . ' AND id_product_attribute = ' . $productAttributeId;

		$result = Db::getInstance()->getRow($query);
		
		if ($result['id']) {
			return $result['id'];
		} else {
			return 0;
		}
	}
	
	public function getCustomersNotRemainded(){		
		$query = 'SELECT * 
				  FROM `' . _DB_PREFIX_ . 'customer_stock_remainder`
				  WHERE status = 1';
		
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($query);
		
		$newStockRemainderList = CustomerStockRemainder::disableTheStockRemainderForAlreadyPurchasedProducts($result);
		
		return $newStockRemainderList;
	}

	public function changeTheStatus($emailSentCustomersList)
	{
		$logFilePath = _PS_LOG_DIR_ . '/StockAlarm/stock_alarm_'.date('Y-m-d',time()).'.log';
		if (!file_exists($logFilePath)) {
			$logFile = @fopen($logFilePath, "w");
		} else {
			$logFile = @fopen($logFilePath, "a");
		}
		if (!$logFile) {
			error_log("Log file is not writable : $logFilePath");
		}
		
		fwrite($logFile, "\n".'START----' . date("D M j G:i:s T Y") . "\n");
	
		foreach($emailSentCustomersList as $productAttributeId => $customerIds){
			fwrite($logFile, 'productAttributeId: '.$productAttributeId."\n");
			fwrite($logFile, 'Customer Ids: '.print_r($customerIds ,true)."\n");
			foreach ($customerIds as $customerId) {
					$customerStockRemainderId = CustomerStockRemainder::isCustomerAlreadyChoosen($customerId, $productAttributeId);
					$customerStockRemainder = new CustomerStockRemainder((int) $customerStockRemainderId);
					$customerStockRemainder->status = 0;
					if($customerStockRemainder->save()){					
						fwrite($logFile, "Successfuly Updated the status of customerId ".$customerId." AND productAttributeId ".$productAttributeId."\n");
					}else{
						fwrite($logFile,"Error While Updated the status of customerId ".$customerId." AND productAttributeId ".$productAttributeId."\n");
					}			
			}
		}
		
		fclose($logFile);

		return;
	}
	
	/*Disabling Stock Alarm for that product if the customer as already purchased for that product.*/
	public function disableTheStockRemainderForAlreadyPurchasedProducts($stockRemaiderList) {
		$newStockRemainderList = array();
		$arraySize = count($stockRemaiderList);
		if($arraySize > 0){			
			$j = 0;
			for($i = 0; $i< $arraySize ; $i++){
				$isCustomerPurchased = Customer::isCustomerPurchasedTheProduct($stockRemaiderList[$i]['id_customer'],$stockRemaiderList[$i]['id_product'], $stockRemaiderList[$i]['id_product_attribute']);
				
				/*if the customer is not purchased then sent through the list*/
				if(!$isCustomerPurchased){
					$newStockRemainderList[$j] = $stockRemaiderList[$i];
					$j++;
				} else {
					/*if customer has purchased then disable, that means no need to sent the stock alarm*/
					$sql = 'UPDATE `'._DB_PREFIX_.'customer_stock_remainder` 
							SET status = 0 
							WHERE id_customer = '.$stockRemaiderList[$i]['id_customer'].' AND id_product = '.$stockRemaiderList[$i]['id_product'].' AND id_product_attribute = '.$stockRemaiderList[$i]['id_product_attribute'];
					Db::getInstance()->Execute($sql);
				}
			}
			
		}
		return $newStockRemainderList;
	}

}

?>

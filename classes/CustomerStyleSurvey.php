<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class CustomerStyleSurveyCore extends ObjectModel
{
	public 		$id;

	/** @var integer */
	public 		$id_customer;

	/** @var string  */
	public		$question1;

	/** @var string */
	public		$question2;

	/** @var string */
	public		$question3;

	/** @var string */
	public		$question4;

	/** @var string */
	public 		$question5;

	/** @var string */
	public 		$question6;

	/** @var string */
	public 		$question7;

	/** @var string */
	public 		$question8;

	/** @var string */
	public 		$question9;

	/** @var string */
	public		$question10;

	/** @var datetime */
	public 		$date_add;

	protected $tables = array ('stylesurvey');

/*	protected 	$fieldsRequired = array('id_customer','question1', 'question2', 'question3', 'question4', 'question5', 'question6', 'question7',
										'question8', 'question9', 'question10');*/
	protected 	$fieldsRequired = array('id_customer');

	protected 	$fieldsSize = array('question1' => 32, 'question2' => 32, 'question3' => 32, 'question4' => 32, 'question5' => 32, 'question6' => 32,
									'question7' => 32, 'question8' => 32, 'question9' => 32, 'question10' => 128);

	protected 	$fieldsValidate = array('question1' => 'isString', 'question2' => 'isString', 'question3' => 'isString', 'question4' => 'isString',
										'question5' => 'isString', 'question6' => 'isString', 'question7' => 'isString',
										'question8' => 'isString', 'question9' => 'isString', 'question10' => 'isString', 'date_add' => 'isString');

	protected 	$table = 'customer_stylesurvey';
	protected 	$identifier = 'id_survey';

	public function getFields()
	{
		parent::validateFields();

		if (isset($this->id))
			$fields['id_survey'] = (int)($this->id);

		$fields['id_customer'] = pSQL($this->id_customer);
		$fields['question1'] = pSQL($this->question1);
		$fields['question2'] = pSQL($this->question2);
		$fields['question3'] = pSQL($this->question3);
		$fields['question4'] = pSQL($this->question4);
		$fields['question5'] = pSQL($this->question5);
		$fields['question6'] = pSQL($this->question6);
		$fields['question7'] = pSQL($this->question7);
		$fields['question8'] = pSQL($this->question8);
		$fields['question9'] = pSQL($this->question9);
		$fields['question10'] = pSQL($this->question10);
		$fields['date_add'] = pSQL($this->date_add);

		return $fields;
	}

	public function add($autodate=false, $nullValues=false){
		if (!parent::add($autodate, $nullValues))
			return false;
	}

	public static function getByCustomerId($id_customer) {
		return DB::getInstance()->getRow(
			'SELECT *
			FROM `' . _DB_PREFIX_ . 'customer_stylesurvey`
			WHERE `id_customer` = ' . pSQL($id_customer)
		);
	}
}

?>

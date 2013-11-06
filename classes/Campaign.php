<?php

/**
 * Description of Campaign
 *
 * @author gangadhar
 */
class Campaign extends ObjectModel
{

	public $id;

	/** @var integer Group Id */
	public $priority;

	/** @var string Campaign Name */
	public $name;

	/** @var integer Group Id */
	public $id_group;

	/** @var integer Discount Id */
	public $id_discount;

	/** @var integer , active(1), Disabled(0) */
	public $active = 1;

	/** @var String */
	public $campaign_url_data;
	
	/** @var String */
	public $campaign_page;
	
	/** @var String */
	public $campaign_text;

	/** @var String */
	public $campaign_expiry_text;

	/* @var String Object. */
	public $date_from;

	/* @var String Object. */
	public $date_to;

	/** @var string Object creation date */
	public $date_add;

	/** @var string Object last modification date */
	public $date_upd;
	protected $tables = array('campaign');
	protected $fieldsRequired = array('name', 'priority', 'id_group', 'active', 'campaign_page', 'date_from', 'date_to', 'campaign_text', 'campaign_expiry_text');
	protected $fieldsSize = array('name' => '32', 'date_from' => '32', 'date_to' => '32');
	protected $fieldsValidate = array('priority' => 'isUnsignedId', 'id_group' => 'isUnsignedId', 'active' => 'isBool', 'date_from' => 'isDate', 'date_to' => 'isDate');
	protected $fieldsRequiredLang = array('name','campaign_page','campaign_text', 'campaign_expiry_text');
	protected $fieldsSizeLang = array('name' => 32);
	protected $fieldsValidateLang = array('name' => 'isGenericName');
	protected $table = 'campaign';
	protected $identifier = 'id_campaign';

	public function getFields()
	{
		parent::validateFields();
		if (isset($this->id))
			$fields['id_campaign'] = (int) ($this->id);
		$fields['priority'] = (int) ($this->priority);
		$fields['id_group'] = (float) ($this->id_group);
		$fields['id_discount'] = (int) ($this->id_discount);
		$fields['active'] = (int) ($this->active);
		$fields['date_from'] = pSQL($this->date_from);
		$fields['date_to'] = pSQL($this->date_to);
		$fields['date_add'] = pSQL($this->date_add);
		$fields['date_upd'] = pSQL($this->date_upd);

		return $fields;
	}

	public function getTranslationsFieldsChild()
	{
		if (!parent::validateFieldsLang())
			return false;
		return parent::getTranslationsFields(array('name', 'campaign_url_data', 'campaign_page', 'campaign_text', 'campaign_expiry_text'));
	}

	public function add($autodate = true, $nullValues = false)
	{
		return parent::add();
	}

	public function getCampaigns($id_lang)
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT c.*, cl.*
		FROM `' . _DB_PREFIX_ . 'campaign` c
		LEFT JOIN `' . _DB_PREFIX_ . 'campaign_lang` AS cl ON (c.`id_campaign` = cl.`id_campaign` AND cl.`id_lang` = ' . (int) ($id_lang) . ')
		ORDER BY c.`priority` ASC');
	}

}

?>

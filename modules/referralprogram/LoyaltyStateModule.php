<?php

if (!defined('_CAN_LOAD_FILES_'))
	exit;

class LoyaltyStateModule extends ObjectModel
{
	public $name;
	public $id_order_state;

	protected $fieldsValidate = array('id_order_state' => 'isInt');
	protected $fieldsRequiredLang = array('name');
	protected $fieldsSizeLang = array('name' => 128);
	protected $fieldsValidateLang = array('name' => 'isGenericName');

	protected $table = 'loyalty_state';
	protected $identifier = 'id_loyalty_state';

	public function getFields()
	{
		parent::validateFields();
		$fields['id_order_state'] = (int)($this->id_order_state);
		return $fields;
	}

	/**
	* Check then return multilingual fields for database interaction
	*
	* @return array Multilingual fields
	*/
	public function getTranslationsFieldsChild()
	{
		parent::validateFieldsLang();
		return parent::getTranslationsFields(array('name'));
	}

	public static function getDefaultId() { return 1; }
	public static function getValidationId() { return 2; }
	public static function getCancelId() { return 3; }
	public static function getConvertId() { return 4; }
	public static function getNoneAwardId() { return 5; }
	public static function getRefundId() { return 6; }
	public static function getPartialRefundId() {return 7;}
    public static function getFullExchangeId() { return 8; }
    public static function getPartialExchangeId() { return 9; }

	public static function insertDefaultData()
	{
		$loyaltyModule = new ReferralProgram();
		$languages = Language::getLanguages();

		$defaultTranslations = array('default' => array('id_loyalty_state' => (int)LoyaltyStateModule::getDefaultId(), 'default' => $loyaltyModule->getL('Awaiting validation'), 'en' => 'Awaiting validation', 'fr' => 'En attente de validation'));
		$defaultTranslations['validated'] = array('id_loyalty_state' => (int)LoyaltyStateModule::getValidationId(), 'id_order_state' => Configuration::get('PS_OS_DELIVERED'), 'default' => $loyaltyModule->getL('Available'), 'en' => 'Available', 'fr' => 'Disponible');
		$defaultTranslations['cancelled'] = array('id_loyalty_state' => (int)LoyaltyStateModule::getCancelId(), 'id_order_state' => Configuration::get('PS_OS_CANCELED'), 'default' => $loyaltyModule->getL('Cancelled'), 'en' => 'Cancelled', 'fr' => 'Annulés');
		$defaultTranslations['converted'] = array('id_loyalty_state' => (int)LoyaltyStateModule::getConvertId(), 'default' => $loyaltyModule->getL('Already converted'), 'en' => 'Already converted', 'fr' => 'Déjà convertis');
		$defaultTranslations['none_award'] = array('id_loyalty_state' => (int)LoyaltyStateModule::getNoneAwardId(), 'default' => $loyaltyModule->getL('Unavailable on discounts'), 'en' => 'Unavailable on discounts', 'fr' => 'Non disponbile sur produits remisés');
		$defaultTranslations['refund'] = array('id_loyalty_state' => (int)LoyaltyStateModule::getRefundId(), 'default' => $loyaltyModule->getL('Unavailable on refund'), 'en' => 'Unavailable on refund', 'fr' => 'Non refundable sur produits remisés');
		$defaultTranslations['partialrefund'] = array('id_loyalty_state' => (int)LoyaltyStateModule::getPartialRefundId(), 'default' => $loyaltyModule->getL('Unavailable on Partial Refund'), 'en' => 'Unavailable on Partial Refund', 'fr' => 'Non refundable sur produits remisés');
                $defaultTranslations['exchange'] = array('id_loyalty_state' => (int)LoyaltyStateModule::getFullExchangeId(), 'default' => $loyaltyModule->getL('Unavailable on exchange'), 'en' => 'Unavailable on exchange', 'fr' => 'Non refundable sur produits remisés');
                $defaultTranslations['partialexchange'] = array('id_loyalty_state' => (int)LoyaltyStateModule::getPartialExchnageId(), 'default' => $loyaltyModule->getL('Unavailable on exchange'), 'en' => 'Unavailable on exchange', 'fr' => 'Non partial exchange sur produits remisés');

		foreach ($defaultTranslations AS $loyaltyState)
		{
			$state = new LoyaltyStateModule((int)$loyaltyState['id_loyalty_state']);
			if (isset($loyaltyState['id_order_state']))
				$state->id_order_state = (int)$loyaltyState['id_order_state'];
			$state->name[(int)Configuration::get('PS_LANG_DEFAULT')] = $loyaltyState['default'];
			foreach ($languages AS $language)
				if (isset($loyaltyState[$language['iso_code']]))
					$state->name[(int)$language['id_lang']] = $loyaltyState[$language['iso_code']];
			$state->save();
		}

		return true;
	}

}

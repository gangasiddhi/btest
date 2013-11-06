<?php

/**
 * Description of loyaltycampaign
 *
 * @author gangadhar
 */
class LoyaltyCampaign extends Module
{

	public function __construct()
	{
		$this->name = 'loyaltycampaign';
		$this->tab = 'front_office';
		$this->version = 1.0;
		$this->author = 'Gangadhar';

		parent::__construct();

		$this->displayName = $this->l('Loyalty Campaign');
		$this->description = $this->l('This module must be enabled if you want to use Loyalty Campaign.');
	}

	public function install()
	{
		if (!parent::install() OR
				!$this->registerHook('footer') OR
				!$this->registerHook('createAccount')) {
			return false;
		}

		return true;
	}

	public function uninstall()
	{
		return (parent::uninstall());
	}

	/* Dynamically add the newly registered customer to the group(cohorts) */

	public function hookCreateAccount($params)
	{
		if ($params['cookie']->logged && $params['cookie']->id_customer) {
			$customer = new Customer((int) $params['cookie']->id_customer);
			$groupId = array(Group::getGroupIdByName('CampaignNewSubsAfter250613', (int) $params['cookie']->id_lang));
			$customer->addGroups($groupId);
			return;
		}
	}

	/* Display the discount/voucher popups */

	public function hookFooter($params)
	{
		global $smarty;
		if(!isset($_COOKIE['campaign']) AND $_COOKIE['campaign'] != 1 AND $params['cookie']->logged AND strpos($_SERVER['PHP_SELF'], 'showroom') === false AND strpos($_SERVER['PHP_SELF'], 'stylesurvey') === false AND strpos($_SERVER['PHP_SELF'], 'order') === false){
			$campaignGroupsDiscountCodeList = array('CampaignMem100613NotPurch' => 'J20PD24',
													'CampaignOpened1NotPurch15' => 'J24DP10',
													'CampaignFPDISPN15NMEM24DAY' => 'J100D20P',
													'CampaignNewSubsAfter250613' => 'J24D25P',
													'CampaignDailyMail' => 'J24FSG1',
													'Campaign2006MNP-1' => 'YD10NC'
													);
			
			$customer = new Customer((int) $params['cookie']->id_customer);
			$campaignGroups = $this->getCampaignGroups((int) $params['cookie']->id_lang);
			$customerCampaignGroup = '';
			$isCustomerUsedTheCamapignVoucher = 0;
			foreach ($campaignGroups as $campaignGroup) {
				if ($customer->isMemberOfGroup(Group::getGroupIdByName($campaignGroup, (int) $params['cookie']->id_lang))) {
					$customerCampaignGroup = $campaignGroup;
					$customerCampaignDiscountId = Discount::getDiscountIdByname($campaignGroupsDiscountCodeList[$campaignGroup]);
					$isCustomerUsedTheCamapignVoucher = Discount::checkCustomerAsUsedDiscount($customerCampaignDiscountId['id_discount'],$params['cookie']->id_customer);
					break;
				}
			}
			
			if(!$isCustomerUsedTheCamapignVoucher){
				//$customerLastPurchaseDate = $customer->getCustomerLastPurchaseDate();
				//$numberOfDaysCustomerHasNotPurchased = intval((time() - strtotime($customerLastPurchaseDate)) / (60 * 60 * 24));

				$couponCodeTextArray = array('FreeShipping' => array('DiscountText' => $this->l('Free Shipping'),
						'DiscountExpiryText' => $this->l('Free shipping Expiry Text')),
					'FreeShipping1TL' => array('DiscountText' => $this->l('1 TL Free Shipping'),
						'DiscountExpiryText' => $this->l('1 TL Free Shipping Expiry Text')),
					'Discount10TL' => array('DiscountText' => $this->l('10TL Discount Code'),
						'DiscountExpiryText' => $this->l('10TL Discount Expiry Text')),
					'Discount10Percent' => array('DiscountText' => $this->l('%10 Discount Code'),
						'DiscountExpiryText' => $this->l('%10 Discount Expiry Text')),
					'Discount15Percent' => array('DiscountText' => $this->l('%15 Discount Code'),
						'DiscountExpiryText' => $this->l('%15 Discount Expiry Text')),
					'Discount20Percent' => array('DiscountText' => $this->l('%20 Discount Code'),
						'DiscountExpiryText' => $this->l('%20 Discount Expiry Text')),
					'Discount25Percent' => array('DiscountText' => $this->l('%25 Discount Code'),
						'DiscountExpiryText' => $this->l('%25 Discount Expiry Text')),
					'Discount20Percent100TL' => array('DiscountText' => $this->l('%20 Discount Code , 100TL'),
						'DiscountExpiryText' => $this->l('%20 Discount and 100TL, Expiry Text')),
				);

				switch ($customerCampaignGroup) {
					/*case 'CampaignMem100613NotPurch' : $smarty->assign(array('campaignImage' => $this->_path . 'img/Discount-Coupon.png',
							'couponCode' => 'J20PD24',
							'couponCodeText' => $couponCodeTextArray['Discount20Percent']['DiscountText'],
							'discountExpiry' => $couponCodeTextArray['Discount20Percent']['DiscountExpiryText']));
						break;
					case 'CampaignOpened1NotPurch15' : $smarty->assign(array('campaignImage' => $this->_path . 'img/Discount-Coupon.png',
							'couponCode' => 'J24DP10',
							'couponCodeText' => $couponCodeTextArray['Discount10Percent']['DiscountText'],
							'discountExpiry' => $couponCodeTextArray['Discount10Percent']['DiscountExpiryText']));
						break;
					case 'CampaignFPDISPN15NMEM24DAY' : $smarty->assign(array('campaignImage' => $this->_path . 'img/Discount-Coupon.png',
							'couponCode' => 'J100D20P',
							'couponCodeText' => $couponCodeTextArray['Discount20Percent100TL']['DiscountText'],
							'discountExpiry' => $couponCodeTextArray['Discount20Percent100TL']['DiscountExpiryText']));
						break;
					case 'CampaignNewSubsAfter250613' : $smarty->assign(array('campaignImage' => $this->_path . 'img/Discount-Coupon.png',
							'couponCode' => 'J24D25P',
							'couponCodeText' => $couponCodeTextArray['Discount15Percent']['DiscountText'],
							'discountExpiry' => $couponCodeTextArray['Discount15Percent']['DiscountExpiryText']));
						break;
					case 'CampaignDailyMail' : $smarty->assign(array('campaignImage' => $this->_path . 'img/Discount-Coupon.png',
							'couponCode' => 'J24FSG1',
							'couponCodeText' => $couponCodeTextArray['FreeShipping']['DiscountText'],
							'discountExpiry' => $couponCodeTextArray['FreeShipping']['DiscountExpiryText']));
						break;*/
					case 'Campaign2006MNP-1' : $smarty->assign(array('campaignImage' => $this->_path . 'img/Discount-Coupon.png',
							'couponCode' => 'YD10NC',
							'couponCodeText' => $couponCodeTextArray['Discount10Percent']['DiscountText'],
							'discountExpiry' => $couponCodeTextArray['Discount10Percent']['DiscountExpiryText']));
						break;
					default : break;
				}

				return $this->display(__FILE__, 'loyaltycampaign.tpl');
			}
		}
		
		return;
	}

	/* Get the campaign groups */

	public function getCampaignGroups($langId)
	{
		$sql = 'SELECT gl.name
			    FROM `' . _DB_PREFIX_ . 'group_lang` gl
				WHERE gl.name LIKE "%Campaign%" AND gl.id_lang = ' . $langId;

		$results = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql);

		$groups = array();

		foreach ($results as $result) {
			$groups[] = $result['name'];
		}

		return $groups;
	}

}

?>

<?php

/* SSL Management */
//$useSSL = true;
require_once(dirname(__FILE__).'/../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../init.php');

include_once(dirname(__FILE__).'/ReferralProgramModule.php');
include_once(dirname(__FILE__).'/LoyaltyModule.php');
include_once(dirname(__FILE__).'/LoyaltyStateModule.php');
if (!$cookie->isLogged())
	Tools::redirect('authentication.php?back=identity.php');

Tools::addCSS(_THEME_CSS_DIR_.'modules/referralProgram/referral-invite.css' ,'all');

$smarty->assign('page_name' , 'referralprogram-stylepoints');
include(dirname(__FILE__).'/../../header.php');

// get discount value (ready to display)
//$discount = Discount::display(floatval(Configuration::get('REFERRAL_DISCOUNT_VALUE_'.intval($cookie->id_currency))), intval(Configuration::get('REFERRAL_DISCOUNT_TYPE')), new Currency($cookie->id_currency));

$activeTab = 'pending';
$error = false;
// Mailing revive
$revive_sent = false;
$nbRevive = 0;
if (Tools::isSubmit('revive'))
{
	$key = Tools::getValue('friendChecked');
	//if($key==Tools::getValue('friendChecked'))
	if (Configuration::get('PS_CIPHER_ALGORITHM'))
		$cipherTool = new Rijndael(_RIJNDAEL_KEY_, _RIJNDAEL_IV_);
	else
		$cipherTool = new Blowfish(_COOKIE_KEY_, _COOKIE_IV_);
	$referralprogram = new ReferralProgramModule((int)($key));
	$vars = array(
		'{email}' => $cookie->email,
		'{lastname}' => $cookie->customer_lastname,
		'{firstname}' => $cookie->customer_firstname,
		'{email_friend}' => $referralprogram->email,
		'{lastname_friend}' => $referralprogram->lastname,
		'{firstname_friend}' => $referralprogram->firstname,
		'{link}' => '?ref_by='.$cipherTool->encrypt($referralprogram->id_sponsor)
		//'{discount}' => $discount
	);
	$referralprogram->save();
	Mail::Send((int)($cookie->id_lang), 'referralprogram-reinvite', Mail::l('Referral Program Re-Invite'), $vars, $referralprogram->email, $referralprogram->firstname.' '.$referralprogram->lastname, "invite@butigo.com",'' /*strval(Configuration::get('PS_SHOP_NAME'))*/, NULL, NULL, dirname(__FILE__).'/mails/', false);
	$revive_sent = true;
	//$nbRevive++;
}
$customer = new Customer(intval($cookie->id_customer));
$stats = $customer->getStats();
$discounts = Discount::getCustomerDiscounts(intval($cookie->id_lang), intval($cookie->id_customer), true, false);
$nbDiscounts = 0;
foreach ($discounts AS $discount)
	if ($discount['quantity_for_user'])
		$nbDiscounts++;
$smarty->assign(array(
		'nbDiscounts' => intval($nbDiscounts),
		'discounts' => $discounts));

$customerPoints = intval(LoyaltyModule::getValidPointsByCustomer(intval($cookie->id_customer)));
$pendingPoints = intval(LoyaltyModule::getPendingPointsByCustomer(intval($cookie->id_customer)));
$orders = LoyaltyModule::getAllByIdCustomer(intval($cookie->id_customer), intval($cookie->id_lang));
$referralEmails = ReferralProgramModule::getRefferalEmails(intval($cookie->id_customer));
$smarty->assign(array(
	'orders' => $orders,
	'totalPoints' => $customerPoints,
	'referralEmails' => $referralEmails,
	'pendingPoints'=> $pendingPoints
));


//$orderQuantity = intval(Configuration::get('REFERRAL_ORDER_QUANTITY'));

// Smarty display
$smarty->assign(array(
	'activeTab' => $activeTab,
	//'orderQuantity' => $orderQuantity,
	'nbFriends' => intval(Configuration::get('REFERRAL_NB_FRIENDS')),
	'error' => $error,
	'pendingFriends' => ReferralProgramModule::getSponsorFriend(intval($cookie->id_customer), 'pending'),
	'revive_sent' => $revive_sent,
	//'nbRevive' => $nbRevive,
	'subscribeFriends' => ReferralProgramModule::getSponsorFriend(intval($cookie->id_customer), 'subscribed'),
	'mails_exists' => (isset($mails_exists) ? $mails_exists : array())
));

echo Module::display(dirname(__FILE__).'/referralprogram.php', 'referralprogram-stylepoints.tpl');

include(dirname(__FILE__).'/../../footer.php');

?>

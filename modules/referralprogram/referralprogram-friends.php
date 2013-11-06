<?php

/* SSL Management */
//$useSSL = false;



Tools::addCSS(_THEME_CSS_DIR_.'modules/referralProgram/referral-invite.css' ,'all');
Tools::addCSS(__PS_BASE_URI__.'ct/ajax_ct.css','all');
Tools::addJS(array(_PS_JS_DIR_.'main.js',__PS_BASE_URI__.'ct/ajax_ct.js'));

//$smarty->assign('page_name' , 'referralprogram-friends');
//include(dirname(__FILE__).'/../../header.php');

// get discount value (ready to display)
//$discount = Discount::display(floatval(Configuration::get('REFERRAL_DISCOUNT_VALUE_'.intval($cookie->id_currency))), intval(Configuration::get('REFERRAL_DISCOUNT_TYPE')), new Currency($cookie->id_currency));

$activeTab = 'sponsor';
$error = false;

// Mailing invitation to friend sponsor
$invitation_sent = false;
$nbInvitation = 0;
if (Configuration::get('PS_CIPHER_ALGORITHM'))
	$cipherTool = new Rijndael(_RIJNDAEL_KEY_, _RIJNDAEL_IV_);
else
	$cipherTool = new Blowfish(_COOKIE_KEY_, _COOKIE_IV_);
//$blowfish = new Blowfish(_COOKIE_KEY_, _COOKIE_IV_);
$ref_id = $cipherTool->encrypt($cookie->id_customer);
setcookie('bu_refid', $ref_id, time()+(3600), '/', '', 0); //expires in one hour
setcookie('bu_refname', strval($cookie->customer_firstname).' '.strval($cookie->customer_lastname), time()+(3600), '/', '', 0);

if (Tools::isSubmit('submitSponsorFriends') AND Tools::getValue('friendsEmail') AND sizeof($friendsEmail = Tools::getValue('friendsEmail')) >= 1)
{ 
	$activeTab = 'sponsor';
	if (Tools::getValue('conditionsValided'))
	{
		$error = 'conditions not valided';
	}
	else
	{
		$friendsFirstName = Tools::getValue('friendsFirstName');
		$friendMessage = Tools::getValue('friendMessage'); //$_POST['friendMessage'];
		$mails_exists = array();
		foreach ($friendsEmail as $key => $friendEmail)
		{
			$friendEmail = strval($friendEmail);
			$friendFirstName = strval($friendsFirstName[$key]);
			if (empty($friendEmail) AND empty($friendFirstName))
				continue;
			elseif (empty($friendEmail) OR !Validate::isEmail($friendEmail))
				$error = 'email invalid';
			elseif (empty($friendFirstName) OR !Validate::isName($friendFirstName))
				$error = 'name invalid';
			elseif (ReferralProgramModule::isEmailExists($friendEmail) OR Customer::customerExists($friendEmail))
			{
				//$error = 'email exists';
				$mails_exists[] = $friendEmail;
			}
			else
			{
				//creation of records in referralprogram database
				$referralprogram = new ReferralProgramModule();
				$referralprogram->id_sponsor = (int)($cookie->id_customer);
				$referralprogram->firstname = $friendFirstName;
				$referralprogram->lastname = 'NULL';
				$referralprogram->email = $friendEmail;
				if (!$referralprogram->validateFields(false))
				   $error = 'name invalid';
				else
				{  
					if ($referralprogram->save())
					{ 
						$vars = array(
								'{email}' => strval($cookie->email),
								'{firstname}' => strval($cookie->customer_firstname),
								'{email_friend}' => $friendEmail,
								'{firstname_friend}' => $friendFirstName,
								'{link}' => '?ref_by='.$ref_id.'&utm_campaign=invitefriendsvariation1&utm_medium=viral&utm_source=invitefriendssingleemail&utm_content='.$ref_id,
								'{message_friend}'=> strval($friendMessage)
								//'{discount}' => $discount,
						);

						Mail::Send((int)($cookie->id_lang), 'referralprogram-invitation', Mail::l('Referral Program'), $vars, $friendEmail, $friendFirstName, "invite@butigo.com" ,strval($cookie->customer_firstname).' '.strval($cookie->customer_lastname), NULL, NULL, dirname(__FILE__).'/mails/',false);
						$invitation_sent = true;
						$nbInvitation++;
						$activeTab = 'pending';
						Tools::redirect('friends.php?invited='.$nbInvitation);
					}
					else
					{
						$error = 'cannot add friends';
						Tools::redirect('friends.php?error=1');
					}
				}
			}
			if ($error)
				break;
		}
		if ($nbInvitation > 0)
			unset($_POST);

		//Not to stop the sending of e-mails in case of doubloon
		if (sizeof($mails_exists))
			$error = 'email exists';
	}
}

$customer = new Customer(intval($cookie->id_customer));
$stats = $customer->getStats();

$orderQuantity = intval(Configuration::get('REFERRAL_ORDER_QUANTITY'));
//$canSendInvitations = false;
//if (intval($stats['nb_orders']) < $orderQuantity)
//	$canSendInvitations = true;

// Smarty display
$smarty->assign(array(
	'activeTab' => $activeTab,
	//'discount' => $discount
	'orderQuantity' => $orderQuantity,
	//'canSendInvitations' => $canSendInvitations,
	'nbFriends' => intval(Configuration::get('REFERRAL_NB_FRIENDS')),
	'error' => $error,
	'invitation_sent' => $invitation_sent,
	'nbInvitation' => $nbInvitation,
	'refer_id' => $ref_id,
	'refer_link' => '?ref_by='.$ref_id,
	//'has_title' =>  false ,
	'subscribeFriends' => ReferralProgramModule::getSponsorFriend(intval($cookie->id_customer), 'subscribed'),
	'mails_exists' => (isset($mails_exists) ? $mails_exists : array())
));

//echo Module::display(dirname(__FILE__).'/referralprogram.php', 'referralprogram-friends.tpl');
//
//include(dirname(__FILE__).'/../../footer.php');

?>

<?php
$useSSL = true;
require_once(dirname(__FILE__).'/../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../init.php');
require_once(dirname(__FILE__).'/../pgg/pgg.php');

$error = array();
if (!$cookie->isLogged())
    Tools::redirect('authentication.php?back=order.php');

//$merchant_id = Configuration::get('GCHECKOUT_MERCHANT_ID');
//$merchant_key = Configuration::get('GCHECKOUT_MERCHANT_KEY');
//$server_type = Configuration::get('GCHECKOUT_MODE');

/* Gather submitted payment card details */

if (Tools::isSubmit('paymentSubmit'))
{
	$cardName        = Tools::getValue('ccname');
	$cardNumber      = Tools::getValue('ccnum');
	$cardExpiry      = Tools::getValue('ccexp_Month')/*.'.'*/.Tools::getValue('ccexp_Year');
	$cardCVV2        = Tools::getValue('ccvv2');

	$installment_count	 = Tools::getValue('instlmnt');//number of installments
	$final_total	 = Tools::getValue('finalTotal');//amount with interest depending on the installment type
	$total = floatval(number_format($cart->getOrderTotal(true, 3), 2, '.', ''));
	
	if (empty($cardName) || empty($cardNumber) || empty($cardExpiry))
	{
		Tools::redirectLink(Tools::getHttpHost(true, true).__PS_BASE_URI__.'modules/pgg/payment_error.php');
	}
	elseif (Tools::strlen($cardNumber) > 16 || Tools::strlen($cardCVV2) > 3)
	{
		Tools::redirectLink(Tools::getHttpHost(true, true).__PS_BASE_URI__.'modules/pgg/payment_error.php');
	}

	$pgg = new PGG();
	$pgg->hookValidation(array('card_name' => $cardName, 'card_number' => $cardNumber, 'card_expiry' => $cardExpiry, 'card_cvv' => $cardCVV2));
}
else
{
	Tools::redirectLink(__PS_BASE_URI__.'modules/pgg/payment_error.php');
}

//include(dirname(__FILE__).'/../../header.php');
/*if(Tools::getValue($error))
{
	if($error == true)
	{
	 echo 'Enter your credit card details correctly';
	}
}*/
//$smarty->assign('error_msg', $error_msg );

/*echo 'OrderId : '.$data[$root]['OrderId']['VALUE'].'<br>';
echo 'AuthCode : '.$data[$root]['AuthCode']['VALUE'].'<br>';
echo 'Response : '.$data[$root]['Response']['VALUE'].'<br>';
echo 'ProcReturnCode : '.$data[$root]['ProcReturnCode']['VALUE'].'<br>';
echo 'TransId : '.$data[$root]['TransId']['VALUE'].'<br>';
echo 'SETTLEID : '.$data[$root]['Extra']['SETTLEID']['VALUE'].'<br>';
echo 'TRXDATE : '.$data[$root]['Extra']['TRXDATE']['VALUE'].'<br>';*/
//$order = new Order($pgg->currentOrder);
//$pgg->storeCard($order->id, $cardName, $cardNumber);

//Tools::redirectLink(__PS_BASE_URI__.'order-confirmation.php?id_cart='.$cart->id.'&id_module='.$pgg->id.'&id_order='.$pgg->currentOrder.'&key='.$order->secure_key);

//include_once(dirname(__FILE__).'/../../footer.php');

?>
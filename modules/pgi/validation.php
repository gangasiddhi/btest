<?php
$useSSL = true;
require_once(dirname(__FILE__).'/../../config/config.inc.php');
require_once(dirname(__FILE__).'/../../init.php');
require_once(dirname(__FILE__).'/../pgi/pgi.php');

$error = array();

if (!$cookie->isLogged())
    Tools::redirect('authentication.php?back=order.php');

/* Gather submitted payment card details */

if (Tools::isSubmit('paymentSubmit'))
{
	$cardName        = Tools::getValue('ccname');
	$cardNumber      = Tools::getValue('ccnum');
	$cardExpiry      = Tools::getValue('ccexp_Month').'.'.Tools::getValue('ccexp_Year');
	$cardCVV2        = Tools::getValue('ccvv2');

	$installment_count	 = Tools::getValue('instlmnt');//number of installments
	$final_total	 = Tools::getValue('finalTotal');//amount with interest depending on the installment type
	$total = floatval(number_format($cart->getOrderTotal(true, 3), 2, '.', ''));

	echo $installment_count."<br/>";
	echo $final_total;
	//exit;

	if(isset($_POST['pgi_instl']))
		$pgi_instl = Tools::getValue('pgi_instl');
	else
		$pgi_instl = 1;
	$log = true;

	if ($log)
	{
		$myFile = _PS_LOG_DIR_."/pgi-total.txt";
		$fh = fopen($myFile, 'w') or die("can't open file");
		fwrite($fh, "install: $pgi_instl,\n".Tools::getValue('pgi_instl'));
		fclose($fh);
	}

	if (empty($cardName) || empty($cardNumber) || empty($cardExpiry))
	{
		Tools::redirectLink(Tools::getHttpHost(true, true).__PS_BASE_URI__.'modules/mediator/payment_error.php');
	}
	elseif (Tools::strlen($cardNumber) > 16 || Tools::strlen($cardCVV2) > 3)
	{
		Tools::redirectLink(Tools::getHttpHost(true, true).__PS_BASE_URI__.'modules/mediatior/payment_error.php');
	}

	$pgi = new PGI();
	$pgi->hookValidation(array('card_name' => $cardName, 'card_number' => $cardNumber, 'card_expiry' => $cardExpiry, 'card_cvv' => $cardCVV2, 'pgi_instl'=>$pgi_instl));
}
else
{
	Tools::redirectLink(__PS_BASE_URI__.'modules/mediator/payment_error.php');
}

?>

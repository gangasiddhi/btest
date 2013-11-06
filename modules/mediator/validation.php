<?php

require_once(dirname(__FILE__) . '/../../config/config.inc.php');
require_once(dirname(__FILE__) . '/../../init.php');

require_once(dirname(__FILE__) . '/../pga/pga.php');
require_once(dirname(__FILE__) . '/../pgf/pgf.php');
require_once(dirname(__FILE__) . '/../pgi/pgi.php');
require_once(dirname(__FILE__) . '/../pgg/pgg.php');
require_once(dirname(__FILE__) . '/../pgy/pgy.php');

$useSSL = true;
$error = array();
$log = false;

if (! $cookie->isLogged()) {
    Tools::redirect('authentication.php?back=order.php');
}

// Gather submitted payment card details
if (Tools::isSubmit('paymentSubmit')) {
    if (! $cart->checkQuantities()) {
        Tools::redirect($link->getPageLink('order.php', false) . '?step=1', '');
    }

    // Bank code, based on the this validation is going to perform
    $bankModule = Tools::getValue('bankModule', 'pgf');
    $bank = strtoupper($bankModule);
    $cardNumber = Tools::getValue('ccnum');
    $cardExpiry = Tools::getValue('ccexp_Month') . substr(Tools::getValue('ccexp_Year'), 2, 2); // Format MMYY
    $cardCVV2 = Tools::getValue('ccvv2');
    $installment_count = (int) Tools::getValue('instlmnt'); // number of installments
    $final_total = Tools::getValue('finalTotal'); // amount with interest depending on the installment type
    $total = $each_inst_amount = $cart_total = $cart->getOrderTotal();
    $installmnt_intrst = 0;
	//This installment is used to avoid the charge for the extra installmment charge for the 3 installment.
	$pgy_installment_count = 0;
	
    LogHandler::appendLog("return-bank-code.txt", "\nCardNumber: $cardNumber, bank: $bankCode", 'development');

    $myFile = _PS_LOG_DIR_ . "/payment-modules-$bankModule.txt";
    $bankObj = new $bank();

    if ($bankModule == 'pgy') {
        $cardExpiry = substr(Tools::getValue('ccexp_Year'), 2, 2) . Tools::getValue('ccexp_Month'); // format: YYMM
        // 3+5 Installment & 3 Months Deferment      
    }
	
    // interest depending on the installment type
    if ($installment_count > 1) {
        // dynamically getting installment interest from db..
        $installmnt_intrst = Configuration::get($bank . '_' . $installment_count . '_INSTALLMENT_INTEREST_RATE');
        $total = $cart_total + ($cart_total * ($installmnt_intrst / 100));
        $each_inst_amount = Tools::ps_round((float)($total / $installment_count), 2);
    }

    $total = Tools::ps_round((float)($total), 2);
    $card = Tools::getBankViaCCNo($cardNumber);
    $cvv_len = ($card['type'] == 'AMEX') ? 4 : 3;

    $logMessage = "Cardnumber: $cardNumber\t CardExpiry:$cardExpiry \t cvv2:$cardCVV2 "
        . "\n Installments: $installment_count \t Total: $final_total \t Cart Total: $total";

    LogHandler::appendLog($myFile, $logMessage, 'development');

    if (empty($cardNumber) || empty($cardExpiry)) {
        Tools::redirect('payment_error.php');
    } elseif (Tools::strlen($cardNumber) > 16 || (Tools::strlen($cardCVV2) > $cvv_len || Tools::strlen($cardCVV2) < 3)) {
        Tools::redirect('payment_error.php');
    }

    $params = array(
        'card_number' => $cardNumber,
        'card_expiry' => $cardExpiry,
        'card_cvv' => $cardCVV2,
        'instalment_count' => $installment_count,
        'each_instalment' => $each_inst_amount,
        'instalment_interest' => $installmnt_intrst,
        'total' => $total
    );

    $bankObj->hookValidation($params);
} else {
    Tools::redirect('payment_error.php');
}

?>

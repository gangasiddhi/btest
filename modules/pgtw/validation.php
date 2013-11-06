<?php

$useSSL = true;

require_once(dirname(__FILE__) . '/../../config/config.inc.php');
require_once(dirname(__FILE__) . '/../../init.php');
require_once(dirname(__FILE__) . '/pgtw.php');
require_once(dirname(__FILE__) . '/lib/pgtwlog.php');

$error = array();
$log = new PGTWLog('pgtwerror.log', 'pgtwmessage.log', L_ALL);

if (! $cookie->isLogged()) {
    Tools::redirect('authentication.php?back=order.php');
}

/* Gather submitted payment card details */
if (Tools::getValue('req') == 1) {
	$log->LogRequest('[validation] req: 1');

	$cellPhone = Tools::getValue('cell_phone');
	$cardChoice = Tools::getValue('card_choice', 1);
	$installment = Tools::getValue('installment_choice', 0); // number of installments
	$bonus = Tools::getValue('bonus', 0);
	$finalTotal = Tools::getValue('finalTotal', 0);

	$log->LogRequest("[validation] Initial Request: $cellPhone, $cardChoice, $installment, $bonus, $finalTotal");

	if (empty($cellPhone) OR empty($cardChoice) OR empty($finalTotal)) {
		$log->LogRequest('[validation] cellPhone or cardChoice or finalTotal is empty, aborting request..');

		die(Tools::jsonEncode(array('trans' => '1')));
	} else if (strlen($cellPhone) < 10 OR ! $finalTotal > 0) {
		$log->LogRequest('[validation] cellPhone is less than 10 digits or finalTotal is not greater than 0, aborting request..');

		die(Tools::jsonEncode(array('trans' => '1')));
	}

	$pgtw = new PGTW();
	$result = $pgtw->hookValidation(array(
		'cellPhone' => $cellPhone,
		'cardChoice' => $cardChoice,
		'installment' => $installment,
		'bonus' => $bonus,
		'finalTotal' => $finalTotal
	));

	die(Tools::jsonEncode($result));
} elseif (Tools::getValue('req') == 2) {
	$cellPhone = Tools::getValue('cell_phone');
	$cardChoice = Tools::getValue('card_choice', 1);
	$installment = Tools::getValue('installment_choice', 0); // number of installments
	$bonus = Tools::getValue('bonus', 0);
	$finalTotal = Tools::getValue('finalTotal', 0);
        $interetRate = 0;
        
        if($installment == 3){
            $interetRate = Configuration::get('PS_THREE_INSTALL_INTEREST_RATE');
        }elseif($installment == 6){
            $interetRate = Configuration::get('PS_SIX_INSTALL_INTEREST_RATE');
        }elseif($installment == 12){
            $interetRate = Configuration::get('PS_TWELVE_INSTALL_INTEREST_RATE');
        }
        
        $eachInstallmentAmount =  number_format( $finalTotal / $installment, 2, '.', '');
        
	$log->LogRequest("[validation] Query Request: $cellPhone, $cardChoice, $installment, $interetRate, $eachInstallmentAmount, $bonus, $finalTotal");

	$pgtw = new PGTW();
	$req2Result = $pgtw->queryOrderStatus(array(
		'cellPhone' => $cellPhone,
		'cardChoice' => $cardChoice,
		'installment' => $installment,
		'bonus' => $bonus,
		'finalTotal' => $finalTotal,
                'interetRate' => $interetRate,
                'eachInstallmentAmount' => $eachInstallmentAmount
	));

	die(Tools::jsonEncode($req2Result));
}

?>

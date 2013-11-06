<?php

class AdminXml extends AdminTab
{
	public function __construct()
	{
		parent::__construct();
	}

	public function displayForm($isMainTab = true)
	{
		global $currentIndex, $cookie;
		
		echo '
		<h2>'.$this->l('Export Order XML / XLS').'</h2>
		<fieldset style="float:left;width:300px"><legend><img src="../img/admin/export.gif" alt="" /> '.$this->l('By Invoice Numbers').'</legend>
			<form action="'.$currentIndex.'&token='.$this->token.'" method="post">
				<label style="width:90px">'.$this->l('From:').' </label>
				<div class="margin-form" style="padding-left:100px">
					<input type="text" size="4" maxlength="10" name="invoice_from" value="" style="width: 120px;" /> <sup>*</sup>
				</div>
				<p></p>
				<label style="width:90px">'.$this->l('To:').' </label>
				<div class="margin-form" style="padding-left:100px">
					<input type="text" size="4" maxlength="10" name="invoice_to" value="" style="width: 120px;" /> <sup>*</sup>
				</div>
				<p></p>
				<div class="margin-form" style="padding-left:100px">
					<input type="submit" value="'.$this->l('Export XMLs').'" name="submitExportXML" class="button" />
				</div>
				<div class="small"><sup>*</sup> '.$this->l('Required fields').'</div>
			</form>
		</fieldset>';

		includeDatepicker(array('date_from', 'date_to'));
		echo '
		<fieldset style="float:left;width:320px;margin-left:10px;"><legend><img src="../img/admin/export.gif" alt="" /> '.$this->l('By Order Dates').'</legend>
			<form action="'.$currentIndex.'&token='.$this->token.'" method="post">
				<label style="width:90px">'.$this->l('From:').' </label>
				<div class="margin-form" style="padding-left:100px">
					<input type="text" name="date_from" id="date_from" value=""> <sup>*</sup>
				</div>
				<p></p>
				<label style="width:90px">'.$this->l('To:').' </label>
				<div class="margin-form" style="padding-left:100px">
					<input type="text" name="date_to" id="date_to" value=""> <sup>*</sup>
				</div>
				<p></p>
				<div class="margin-form" style="padding-left:100px">
					<input type="submit" value="'.$this->l('Export XLS').'" name="submitExportXLS" class="button" />
				</div>
				<div class="small"><sup>*</sup> '.$this->l('Required fields').'</div>
			</form>
		</fieldset>
		
		<div class="clear">&nbsp;</div>';

		return parent::displayForm();
	}

	public function display()
	{
		$this->displayForm();
		$this->displayOptionsList();
	}

	public function postProcess()
	{
		global $currentIndex;

		if (Tools::isSubmit('submitExportXML'))
		{
			$orders = Order::getOrdersIdByInvoiceNumbers(Tools::getValue('invoice_from'), Tools::getValue('invoice_to'));
			if (sizeof($orders))
				Tools::redirectAdmin('xml.php?multiple_xmls&invoice_from='.urlencode(Tools::getValue('invoice_from')).'&invoice_to='.urlencode(Tools::getValue('invoice_to')).'&token='.$this->token);
			$this->_errors[] = $this->l('No invoice found for this period');
		}
		elseif (Tools::isSubmit('submitExportXLS'))
		{
			$orders = Order::getInvoiceDetailsByDate(Tools::getValue('date_from'), Tools::getValue('date_to'));
			if (sizeof($orders))
				Tools::redirectAdmin('xls.php?create_xls&date_from='.urlencode(Tools::getValue('date_from')).'&date_to='.urlencode(Tools::getValue('date_to')).'&token='.$this->token);
			$this->_errors[] = $this->l('No invoice found for this period');
		}
		else
			parent::postProcess();
	}
}

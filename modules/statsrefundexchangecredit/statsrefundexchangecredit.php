<?php
/*
* 2007-2011 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2011 PrestaShop SA
*  @version  Release: $Revision: 6594 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_CAN_LOAD_FILES_'))
	exit;

class Statsrefundexchangecredit extends Module
{
    private $_html = '';
	private $_query = '';
	private $_option = 0;
	private $_id_product = 0;

    function __construct()
    {
        $this->name = 'statsrefundexchangecredit';
        $this->tab = 'analytics_stats';
        $this->version = 1.0;
		$this->author = 'PrestaShop';
		$this->need_instance = 0;

        parent::__construct();

        $this->displayName = $this->l('Refund Exchange Credit details');
        $this->description = $this->l('Get detailed statistics for refund_exchange.');
    }

	public function install()
	{
		return (parent::install() AND $this->registerHook('AdminStatsModules'));
	}

	public function uninstall() 
	{
		return (parent::uninstall());
	}
	
	public function getCreditsGivenManually()
	{

		return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT o.`invoice_number` AS Invoicenumber, ohd.`quantity` AS Quantity, o.`date_add` AS Orderdate, ohd.`date_add` AS Creditdate
		FROM `'._DB_PREFIX_.'order_history_detail` ohd
		LEFT JOIN `'._DB_PREFIX_.'orders` o ON o.id_order = ohd.id_order
		WHERE ohd.`id_order_state` IN (20) AND  ohd.date_add BETWEEN '.ModuleGraph::getDateBetween().'
		');

	}

	public function getFullyCredited()
	{

		return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT o.`invoice_number` AS Invoicenumber, ohd.`quantity` AS Quantity, o.`date_add` AS Orderdate, ohd.`date_add` AS Creditdate
		FROM `'._DB_PREFIX_.'order_history_detail` ohd
		LEFT JOIN `'._DB_PREFIX_.'orders` o ON o.id_order = ohd.id_order
		WHERE ohd.`id_order_state` IN (19) AND  ohd.date_add BETWEEN '.ModuleGraph::getDateBetween().'
		');
	}

	public function getPartiallyCredited()
	{

		return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT o.`invoice_number` AS Invoicenumber, ohd.`quantity` AS Quantity, o.`date_add` AS Orderdate, ohd.`date_add` AS Creditdate
		FROM `'._DB_PREFIX_.'order_history_detail` ohd
		LEFT JOIN `'._DB_PREFIX_.'orders` o ON o.id_order = ohd.id_order
		WHERE ohd.`id_order_state` IN (18) AND  ohd.date_add BETWEEN '.ModuleGraph::getDateBetween().'
		');

	}

	public function getFullRefund()
	{

		return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT o.`invoice_number` AS Invoicenumber, ohd.`current_refund_amt` AS Refundamount, ohd.`product_reference` AS ProductRefNo, ohd.`quantity` AS Quantityrefunded, o.`date_add` AS Orderdate, ohd.`date_add` AS Refunddate
		FROM `'._DB_PREFIX_.'order_history_detail` ohd
		LEFT JOIN `'._DB_PREFIX_.'orders` o ON o.id_order = ohd.id_order
		WHERE ohd.`id_order_state` IN (7) AND  ohd.date_add BETWEEN '.ModuleGraph::getDateBetween().'
		');

	}

	public function getPartialRefund()
	{

		return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT o.`invoice_number` AS Invoicenumber, ohd.`current_refund_amt` AS Refundamount, ohd.`product_reference` AS ProductRefNo, ohd.`quantity` AS Quantityrefunded, o.`date_add` AS Orderdate, ohd.`date_add` AS Refunddate
		FROM `'._DB_PREFIX_.'order_history_detail` ohd
		LEFT JOIN `'._DB_PREFIX_.'orders` o ON o.id_order = ohd.id_order
		WHERE ohd.`id_order_state` IN (13) AND  ohd.date_add BETWEEN '.ModuleGraph::getDateBetween().'
		');

	}

	public function getManualRefund()
	{

		return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT o.`invoice_number` AS Invoicenumber, ohd.`current_refund_amt` AS Refundamount, o.`date_add` AS Orderdate, ohd.`date_add` AS Refunddate
		FROM `'._DB_PREFIX_.'order_history_detail` ohd
		LEFT JOIN `'._DB_PREFIX_.'orders` o ON o.id_order = ohd.id_order
		WHERE ohd.`id_order_state` IN (14)  AND  ohd.date_add BETWEEN '.ModuleGraph::getDateBetween().'
		 ');
	}

	public function getFullExchange()
	{
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT o.`invoice_number` AS InvoiceNo, ohd.`current_refund_amt` AS Exchange_Amt, ohd.`product_reference` AS ProductRefNo, ohd.`quantity` AS Quantityexchanged, o.`date_add` AS Orderdate, ohd.`date_add` AS Exchangedate
		FROM `'._DB_PREFIX_.'order_history_detail` ohd
		LEFT JOIN `'._DB_PREFIX_.'orders` o ON o.id_order = ohd.id_order
		WHERE ohd.`id_order_state` IN (12)  AND  ohd.date_add BETWEEN '.ModuleGraph::getDateBetween().'
		');

	}

	public function getPartialExchange()
	{
//		$date_fm = '2012-07-23 00:00:00';
//		$date_to ='2012-07-24  23:59:59';
//					$dateBetween = ModuleGraph::getDateBetween();
//	echo '
//SELECT o.`invoice_number` AS InvoiceNo, ohd.`current_refund_amt` AS Exchangeamt, ohd.`product_reference` AS ProductRefNo, ohd.`quantity` AS QuantityExchanged, o.`date_add` AS Orderdate, ohd.`date_add` AS Exchangedate
//		FROM `'._DB_PREFIX_.'order_history_detail` ohd
//		LEFT JOIN `'._DB_PREFIX_.'orders` o ON o.id_order = ohd.id_order
//		WHERE ohd.`id_order_state` IN (17) AND  ohd.date_add >= \''.pSQL($date_fm).'\' AND ohd.date_add <= \''.pSQL($date_to).'\'';

//		echo $date_fm.'---'.$date_to; exit;
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
		SELECT o.`invoice_number` AS InvoiceNo, ohd.`current_refund_amt` AS Exchangeamt, ohd.`product_reference` AS ProductRefNo, ohd.`quantity` AS QuantityExchanged, o.`date_add` AS Orderdate, ohd.`date_add` AS Exchangedate
		FROM `'._DB_PREFIX_.'order_history_detail` ohd
		LEFT JOIN `'._DB_PREFIX_.'orders` o ON o.id_order = ohd.id_order
		WHERE ohd.`id_order_state` IN (17) AND  ohd.date_add BETWEEN '.ModuleGraph::getDateBetween().'
				');




//		print_r($result);exit;
	}


	public function hookAdminStatsModules($params)
	{
		global $cookie, $currentIndex;
		$id_order_state  = (int)(Tools::getValue('id_order_state '));
//		$currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
//		$required_states = array();
		$required_states = array(_PS_OS_PARTIALEXCHANGE_,_PS_OS_FULLCREDITED_,_PS_OS_PARTIALCREDITED_,_PS_OS_REFUND_,_PS_OS_PARTIALREFUND_,_PS_OS_MANUALREFUND_,_PS_OS_EXCHANGE_);

//			echo 'test'.$id_order_state ;
			$this->_html .= '<fieldset class="width3"><legend><img src="../modules/'.$this->name.'/logo.gif" /> '.$this->displayName.'</legend>';

		if ($id_order_state = (int)(Tools::getValue('id_order_state')))
		{
//
			if($id_order_state == _PS_OS_PARTIALEXCHANGE_)
			{
				$result = $this->getPartialExchange();
			}
			elseif($id_order_state == _PS_OS_FULLCREDITED_)
			{
				$result = $this->getFullyCredited();
			}
			elseif($id_order_state == _PS_OS_PARTIALCREDITED_)
			{
				$result = $this->getPartiallyCredited();
			}
			elseif($id_order_state == _PS_OS_REFUND_)
			{
				$result = $this->getFullRefund();
			}
			elseif($id_order_state == _PS_OS_PARTIALREFUND_)
			{
				$result = $this->getPartialRefund();
			}
			elseif($id_order_state == _PS_OS_MANUALREFUND_)
			{
				$result = $this->getManualRefund();
			}
			elseif($id_order_state == _PS_OS_EXCHANGE_)
			{
				$result = $this->getFullExchange();
			}
//			print_r ($result);exit;

			if(isset($result) AND $result)
			{
				foreach($result AS $res)
				{
//					echo 'test';
					$keys = array_keys($res);
				}

				$this->_html .= '<div style=" height: auto; width:600px;">
				<table class="table" border="0" cellspacing="0" cellspacing="0"><thead>
					<tr>';
					foreach($keys AS $key)
						$this->_html .= '<th>'.$key.'</th>';
					$this->_html .= '</tr>
				</thead>';
						foreach($result AS $res)
						{
							$this->_html .='<tr>';
							foreach($keys AS $key)
								$this->_html .= '<td>'.$res[$key].'</td>';
							$this->_html .='</tr>';
						}
				$this->_html .= '</tbody></table><br /></div><br />

				<a href="'.$_SERVER['REQUEST_URI'].'&export=1&id_order_state_export='.$id_order_state.'"><img src="../img/admin/asterisk.gif" />'.$this->l('XLS Export').'</a><br />';
			}
			else
				$this->_html .=   "<p> There is no data from".ModuleGraph::getDateBetween()."</p>";
		}

		if (Tools::getValue('export'))
		{

			$id_order_state=Tools::getValue('id_order_state_export');
			if($id_order_state == _PS_OS_PARTIALEXCHANGE_)
			{
				$filename = 'Partial_exchange';
				$result = $this->getPartialExchange();
			}
			elseif($id_order_state == _PS_OS_FULLCREDITED_)
			{
				$filename = 'Full_credited';
				$result = $this->getFullyCredited();
			}
			elseif($id_order_state == _PS_OS_PARTIALCREDITED_)
			{
				$filename = 'Partial_credited';
				$result = $this->getPartiallyCredited();
			}
			elseif($id_order_state == _PS_OS_REFUND_)
			{
				$filename = 'Full_refund';
				$result = $this->getFullRefund();
			}
			elseif($id_order_state == _PS_OS_PARTIALREFUND_)
			{
				$filename = 'Partial_refund';
				$result = $this->getPartialRefund();
			}
			elseif($id_order_state == _PS_OS_MANUALREFUND_)
			{
				$filename = 'Manual_refund';
				$result = $this->getManualRefund();
			}
			elseif($id_order_state == _PS_OS_EXCHANGE_)
			{
				$filename = 'Full_exchange';
				$result = $this->getFullExchange();
			}

			if($result)
			{
				foreach($result AS $res)
				{
					$keys = array_keys($res);
				}
				$xls_output= '';
				$xls_output .= '<table class="table" border="0" cellspacing="0" cellspacing="0"><thead>	<tr>';
					foreach($keys AS $key)
						$xls_output .= '<th>'.$key.'</th>';
					$xls_output .= '</tr>
				 </thead>';
				foreach($result AS $res)
				{
					$xls_output .='<tr>';
					foreach($keys AS $key)
						$xls_output .= '<td>'.$res[$key].'</td>';
					$xls_output .='</tr>';
				}
				$dates = explode(' AND ',ModuleGraph::getDateBetween());
				foreach($dates AS $date)
				{
					$dates1[] = str_split($date,12);
				}
				foreach($dates1 AS $date)
				{
					$dat = trim($date[0], "'");
					$dates2[] = explode("'", $date[0]);
				}
				$dates3[] = $dates2[0][1];
				$dates3[] = $dates2[1][1];

				$filename = $filename.'_'.$dates3[0].'_'.$dates3[1].".xls";
				$xls_output.= '</tbody></table><br /></div><br />';
				ob_end_clean();
				 header('Content-Type: text/html; charset=utf-8');
				 header("Content-type: application/vnd.ms-excel; charset=utf-8");
				 header("Content-Disposition: attachment; filename=\"$filename\"");

				 echo $xls_output;
				 exit;
			}

		}
		else
		{
			$states = OrderState::getOrderStates((int)($cookie->id_lang));
//			print_r($states);exit;
				$this->_html .= '<label>'.$this->l('Choose a states report').'</label>

						<form action="'.$_SERVER['REQUEST_URI'].'" method="post" id="orderstatesForm">
			<div class="margin-form">
					<select name="id_order_state" onchange="$(\'#orderstatesForm\').submit();">
						<option value="0">'.$this->l('All').'</option>';
			foreach ($states as $state)
				if(in_array($state['id_order_state'],$required_states))
				$this->_html .= '<option value="'.$state['id_order_state'].'"'.($id_order_state == $state['id_order_state'] ? ' selected="selected"' : '').'>'.$state['name'].'</option>';
			$this->_html .= '
					</select>
				</form>
			</div>
				';
		}

		$this->_html .= '</fieldset><br />';
//		if(!Tools::getValue('export'))
		return $this->_html;
	}

}




<?php
class StatsCohort extends Module
{
	private $_html = '';
	public function __construct()
    {
        $this->name = 'statscohort';
        $this->tab = 'analytics_stats';
        $this->version = 1.0;
		$this->author = 'PrestaShop';
		$this->need_instance = 0;

        parent::__construct();

        $this->displayName = $this->l('Stats Cohort');
        $this->description = '';
    }

	public function install()
	{
		return (parent::install() && $this->registerHook('AdminStatsModules'));
	}

	public function uninstall() 
	{
		return (parent::uninstall());
	}
	
	public function getContent()
	{
		Tools::redirectAdmin('index.php?tab=AdminStats&module=statscohort&token='.Tools::getAdminTokenLite('AdminStats'));
	}

	public function hookAdminStatsModules()
	{
		global $cookie;
		$db = Db::getInstance();

		$currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));

		$result = $db->ExecuteS('
		SELECT
			COUNT(DISTINCT o.id_order) as countOrders,
			SUM(o.total_paid_real) as revenue,
			gl.name,
			g.id_group
		FROM '._DB_PREFIX_.'group g
		LEFT JOIN '._DB_PREFIX_.'customer_group cg ON g.id_group = cg.id_group
		LEFT JOIN '._DB_PREFIX_.'orders o ON cg.id_customer = o.id_customer
		LEFT JOIN '._DB_PREFIX_.'group_lang gl ON g.id_group = gl.id_group
		WHERE o.valid = 1
		AND o.date_add BETWEEN '.ModuleGraph::getDateBetween().'
		GROUP BY g.id_group',false);

		$dataTable = array();
		while ($row = $db->nextRow($result))
			$dataTable[$row['id_group']] = $row;

		$this->_html .= '<div style="float:left;width:550px">
		<fieldset><legend><img src="../modules/'.$this->name.'/logo.gif" /> '.$this->displayName.'</legend>
			<p style="float:left">'.$this->l('All amounts are without taxes.').'</p>
			<div class="clear">&nbsp;</div>
			<table class="table" border="0" cellspacing="0" cellspacing="0">
				<tr>
					<th style="width:70px;text-align:center"></th>
					<th style="text-align:center">'.$this->l('Visits').'</th>
					<th style="text-align:center">'.$this->l('Reg.').'</th>
					<th style="text-align:center">'.$this->l('% Reg.').'</th>
					<th style="text-align:center">'.$this->l('Orders').'</th>
					<th style="text-align:center">'.$this->l('% Orders').'</th>
					<th style="width:100px;text-align:center">'.$this->l('Revenue').'</th>
					<th style="width:100px;text-align:center">'.$this->l('Average Orders').'</th>
				</tr>';

		$visitArray = array();
		$visits = Db::getInstance()->ExecuteS('
			SELECT COUNT(DISTINCT cg.id_customer) as visits, cg.id_group
			FROM '._DB_PREFIX_.'connections c
			LEFT JOIN '._DB_PREFIX_.'guest g ON c.id_guest = g.id_guest
			LEFT JOIN '._DB_PREFIX_.'customer_group cg ON g.id_customer = cg.id_customer
			WHERE c.date_add BETWEEN '.ModuleGraph::getDateBetween().' GROUP BY cg.id_group', false);

		while ($row = $db->nextRow($visits))
			$visitArray[$row['id_group']] = $row['visits'];

		foreach ($dataTable as $row) {
			$visitsToday = (int)(isset($visitArray[$row['id_group']]) ? $visitArray[$row['id_group']] : 0);

			$row['registrations'] = Db::getInstance()->getValue('
				SELECT COUNT(*)
				FROM '._DB_PREFIX_.'customer c
				LEFT JOIN '._DB_PREFIX_.'customer_group cg ON c.id_customer = cg.id_customer
				WHERE c.date_add BETWEEN '.ModuleGraph::getDateBetween().' AND cg.id_group = '.$row['id_group']);

			$this->_html .= '
			<tr>
				<td>'.$row['name'].'</td>
				<td align="center">' . $visitsToday . '</td>
				<td align="center">' . (int)($row['registrations']) . '</td>
				<td align="center">' . ($visitsToday ? round(100 * (int)($row['registrations']) / $visitsToday, 2) . ' %' : '-') . '</td>
				<td align="center">' . (int)($row['countOrders']) . '</td>
				<td align="center">' . ($visitsToday ? round(100 * (int)($row['countOrders']) / $visitsToday, 2) . ' %' : '-') . '</td>
				<td align="center">' . Tools::displayPrice((int)($row['revenue']), $currency) . '</td>
				<td align="center">' . round((int)($row['revenue'])/(int)($row['countOrders']),2) . '</td>
			</tr>';
		}

		$this->_html .= '
			</table>
		</fieldset>
		</div>';
		return $this->_html;
	}
}
?>

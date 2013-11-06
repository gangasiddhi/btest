<?php

/**
 * Description of loyaltycampaign
 *
 * @author gangadhar
 */
class CampaignManagement extends Module
{

	public function __construct()
	{
		$this->name = 'campaignmanagement';
		$this->tab = 'front_office';
		$this->version = 1.0;
		$this->author = 'Gangadhar';

		parent::__construct();

		$this->displayName = $this->l('Campaign Management');
		$this->description = $this->l('This module must be enabled if you want to use Campaign Management.');
	}

	public function install()
	{
		if (!parent::install() OR
				!$this->registerHook('footer') OR
				!$this->registerHook('authentication') OR
				!$this->registerHook('createAccount')) {
			return false;
		}

		return true;
	}

	public function uninstall()
	{
		return (parent::uninstall());
	}

	public function getContent()
	{
		$this->_html = '';
		$this->_postProcess();
		if (Tools::getValue('action') == 'campaignAdd') {
			$this->_displayForm(array('campaignAdd' => 1));
		} else if (Tools::getValue('action') == 'campaignEdit' AND Tools::getValue('campaignId')) {
			$this->_displayForm(array('campaignEdit' => 1,
				'campaignId' => Tools::getValue('campaignId')));
		} else if (Tools::getValue('action') == 'campaignUpload') {
			$this->_displayForm(array('campaignUpload' => 1,
				'campaignId' => Tools::getValue('campaignId')));
		} else {
			$this->_displayForm();
		}
		return $this->_html;
	}

	public function _postProcess()
	{
		global $currentIndex;
		$languages = Language::getLanguages(false);
		$defaultLanguage = (int) (Configuration::get('PS_LANG_DEFAULT'));
		$errors = array();
		$action = '';
		if (Tools::getValue('submitAddCampaign') || Tools::getValue('submitEditCampaign')) {
			if (Tools::getValue('submitEditCampaign') AND Tools::getValue('campaignId')) {
				$campaign = New Campaign((int) Tools::getValue('campaignId'));
				$action = 'edit';
			} else {
				$campaign = New Campaign();
				$action = 'add';
			}
			//Campaign Name.
			foreach ($languages AS $language)
				$campaign->name[$language['id_lang']] = Tools::getValue('name_' . $language['id_lang']);

			if (!$campaign->name)
				$errors[] = $this->l('Campaign name is missing.');

			//Campaign Priority.
			if (Tools::getValue('campaignPriority'))
				$campaign->priority = Tools::getValue('campaignPriority');
			else
				$errors[] = $this->l('Priority is missing.');

			//Campaign group
			if (Tools::getValue('campaignGroupId'))
				$campaign->id_group = Tools::getValue('campaignGroupId');
			else
				$errors[] = $this->l('Group/Cohort is missing.');

			//Campaign discount code
			if (Tools::getValue('campaignDiscountCode')) {
				$discount = Discount::getDiscountIdByname(trim(Tools::getValue('campaignDiscountCode')));
				if ($discount) {
					$campaign->id_discount = $discount['id_discount'];
				} else {
					$errors[] = $this->l('Enter the Correct Discount Code');
				}
			} else {
				$errors[] = $this->l('Discount Code is missing.');
			}

			//Campaign Page.
			foreach ($languages AS $language)
				$campaign->campaign_url_data[$language['id_lang']] = Tools::getValue('campaignUrlData_' . $language['id_lang']);

			//Campaign Page.
			foreach ($languages AS $language)
				$campaign->campaign_page[$language['id_lang']] = Tools::getValue('campaignPagename_' . $language['id_lang']);

			//Campaign start date.
			if (Tools::getValue('campaignDateFrom'))
				$campaign->date_from = Tools::getValue('campaignDateFrom');
			else
				$errors[] = $this->l('From is missing');

			//Campaign end date.
			if (Tools::getValue('campaignDateTo'))
				$campaign->date_to = Tools::getValue('campaignDateTo');
			else
				$errors[] = $this->l('To is missing');

			//Campaign Text.
			foreach ($languages AS $language)
				$campaign->campaign_text[$language['id_lang']] = Tools::getValue('campaignText_' . $language['id_lang']);


			if (!$campaign->campaign_text)
				$errors[] = $this->l('Campaign Text is missing.');


			//Campaign Expiry Text.
			foreach ($languages AS $language)
				$campaign->campaign_expiry_text[$language['id_lang']] = Tools::getValue('campaignExpiryText_' . $language['id_lang']);


			if (!$campaign->campaign_expiry_text)
				$errors[] = $this->l('Campaign Expiry Text is missing.');


			if (!sizeof($errors)) {
				if ($action == 'add') {
					if ($campaign->add()) {
						Tools::redirectAdmin($currentIndex . '&configure=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules') . '');
						$this->_html .= $this->l('Campaign ' . $campaign->name[$defaultLanguage] . ' Added Successfully.');
					}
				} else if ($action == 'edit') {
					if ($campaign->update()) {
						Tools::redirectAdmin($currentIndex . '&configure=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules') . '');
						$this->_html .= $this->l('Campaign ' . $campaign->name[$defaultLanguage] . ' Updated Successfully.');
					}
				}
			} else {
				$this->_html .= $this->displayError(implode('<br />', $errors));
				return false;
			}
		} else if (Tools::isSubmit('campaignSubmitFileUpload')) {
			$moveFilePath = dirname(__FILE__) . '/csv/' . $_FILES['file']['name'];
			//$fileDelimiter = Tools::getValue('campaignFileUploadDelimiter');
			$campaign = New Campaign((int) Tools::getValue('campaignId'));

			if (isset($_FILES['file']) AND !empty($_FILES['file']['error'])) {
				switch ($_FILES['file']['error']) {
					case UPLOAD_ERR_INI_SIZE:
						$this->_errors[] = Tools::displayError('The uploaded file exceeds the upload_max_filesize directive in php.ini. If your server configuration allows it, you may add a directive in your .htaccess, for example:')
								. '<br/><a href="?tab=AdminGenerator&amp;token='
								. Tools::getAdminTokenLite('AdminGenerator') . '" >'
								. '<code>php_value upload_max_filesize 20M</code> ' .
								Tools::displayError('(clic to open Generator tab)') . '</a>';
						break;
					case UPLOAD_ERR_FORM_SIZE:
						$this->_errors[] = Tools::displayError('The uploaded file exceeds the post_max_size directive in php.ini. If your server configuration allows it, you may add a directive in your .htaccess, for example:')
								. '<br/><a href="?tab=AdminGenerator&amp;token='
								. Tools::getAdminTokenLite('AdminGenerator') . '" >'
								. '<code>php_value post_max_size 20M</code> ' .
								Tools::displayError('(clic to open Generator tab)') . '</a>';
						break;
						break;
					case UPLOAD_ERR_PARTIAL:
						$this->_errors[] = Tools::displayError('The uploaded file was only partially uploaded.');
						break;
						break;
					case UPLOAD_ERR_NO_FILE:
						$this->_errors[] = Tools::displayError('No file was uploaded');
						break;
						break;
				}
			} else if (!file_exists($_FILES['file']['tmp_name']) OR !@move_uploaded_file($_FILES['file']['tmp_name'], $moveFilePath))
				$this->_errors[] = $this->l('an error occurred while uploading and copying file');

			if ($this->_errors) {
				$this->_html .= $this->displayError(implode('<br />', $this->_errors));
				return false;
			} else {
				$fileDelimiter = ',';
				$row = 1;
				$csv_file = $moveFilePath;
				$query = 'INSERT INTO `' . _DB_PREFIX_ . 'customer_group` (id_customer, id_group)
					  SELECT `id_customer`,' . $campaign->id_group . ' FROM `' . _DB_PREFIX_ . 'customer` WHERE `email`  IN (';
				$emails = '';
				if (($handle = fopen($csv_file, "r")) !== FALSE) {
					while (($data = fgetcsv($handle, 0, $fileDelimiter)) !== FALSE) {
						$num = count($data);
						for ($c = 0; $c < $num; $c++) {
							$emails .= $data[$c] . ',';
						}
					}
					fclose($handle);
				}
				$emails = rtrim($emails, ',');
				$query .= $emails . ')';
				if (Db::getInstance()->Execute($query)) {
					$this->_html .= 'Customers added to the group sucessfully';
					Tools::redirectAdmin($currentIndex . '&configure=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules') . '');
				} else {
					$this->_html .= $this->displayError('Error While adding the customers to the group.');
				}
			}
		} else if (Tools::getValue('campaignId') && Tools::getValue('action')) {
			//Enabling , Disabling & Editing the Campaign.
			if (Tools::getValue('action') == 'campaignEnable') {
				$campaign = New Campaign((int) Tools::getValue('campaignId'));
				$campaign->active = 1;
				if ($campaign->update()) {
					Tools::redirectAdmin($currentIndex . '&configure=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules') . '');
				}
			} else if (Tools::getValue('action') == 'campaignDisable') {
				$campaign = New Campaign((int) Tools::getValue('campaignId'));
				$campaign->active = 0;
				if ($campaign->update()) {
					Tools::redirectAdmin($currentIndex . '&configure=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules') . '');
				}
			} else if (Tools::getValue('action') == 'campaignDelete') {
				$campaign = New Campaign((int) Tools::getValue('campaignId'));
				if ($campaign->delete()) {
					Tools::redirectAdmin($currentIndex . '&configure=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules') . '');
				}
			}
		}
	}

	private function _displayUploadForm($params)
	{
		global $cookie, $currentIndex;
	}

	private function _displayForm($params)
	{
		global $cookie, $currentIndex;

		/* Get BO coookie language */
		$allowEmployeeFormLang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ? Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') : 0;
		if ($allowEmployeeFormLang && !$cookie->employee_form_lang)
			$cookie->employee_form_lang = (int) (Configuration::get('PS_LANG_DEFAULT'));
		$useLangFromCookie = false;
		$languages = Language::getLanguages(false);
		if ($allowEmployeeFormLang)
			foreach ($languages AS $lang)
				if ($cookie->employee_form_lang == $lang['id_lang'])
					$useLangFromCookie = true;
		if (!$useLangFromCookie)
			$defaultLanguage = (int) (Configuration::get('PS_LANG_DEFAULT'));
		else
			$defaultLanguage = (int) ($cookie->employee_form_lang);
		/* Get BO coookie language */

		/* Languages preliminaries */
		$languages = Language::getLanguages(false);
		$groups = Group::getGroups($defaultLanguage);
		$pages = Meta::getPages(false, false);
		//Javascript
		$this->_html .= '<script type="text/javascript">
				$(document).ready(function() {
					id_language = ' . $defaultLanguage . ';
				});
			</script>';

		$this->_html .= '
			<form method="post" action="' . $_SERVER['REQUEST_URI'] . '" enctype="multipart/form-data">
				<fieldset style="width: 900px;">
					<legend><img src="' . $this->_path . 'logo.gif" alt="" title="" /> ' . $this->displayName . '</legend>';
		if (!$params['campaignUpload'] AND !$params['campaignEdit']) {
			$this->_html .= '
					<img src="../img/admin/add.gif" border="0" /><a href="' . $currentIndex . '&configure=' . $this->name . '&action=campaignAdd&token=' . Tools::getAdminTokenLite('AdminModules') . '" id="showForm" style="color:#488E41"><b>' . $this->l('Add New Campaign') . '</b></a><div class="clear"></div>
					<div class="margin-form">
					</div>';
		}

		if ($params['campaignAdd'] || $params['campaignEdit']) {
			if ($params['campaignAdd']) {
				//Campaign Name.
				$this->_html .= '
						<label>' . $this->l('Campaign Name') . ':</label>
						<div class="margin-form">';
				foreach ($languages as $language)
					$this->_html .= '
							<div id="name_' . $language['id_lang'] . '" style="display: ' . ($language['id_lang'] == $defaultLanguage ? 'block' : 'none') . '; float: left;">
								<input type="text" name="name_' . $language['id_lang'] . '" value="" /><sup> *</sup>
								<span class="hint" name="help_box">' . $this->l('Invalid characters:') . ' <>;=#{}<span class="hint-pointer">&nbsp;</span></span>
							</div>';
				$this->_html .= $this->displayFlags($languages, $defaultLanguage, 'name¤campaignUrlData¤campaignPagename¤campaignText¤campaignExpiryText', 'name', true);

				$this->_html .= '<div class="clear" ></div>
						</div>';

				//Cohort Name.
				$this->_html .= '<label>' . $this->l('Group/Cohort Name') . ':</label>
						<div class="margin-form">
								<select name="campaignGroupId">';
				foreach ($groups as $group) {
					$this->_html .= '<option value="' . $group['id_group'] . '">' . $group["name"] . '</option>';
				}
				$this->_html .= '</select>&nbsp;&nbsp;<sup>*</sup>
						<p class="clear">' . $this->l('Select the group(cohort) to which the campaign is conducted.') . '</p>
						</div>';

				//Priority of the Campaign.
				$this->_html .= '<label>' . $this->l('Priority') . ':</label>
						<div class="margin-form">
							<input type="text" name="campaignPriority" value=""/>&nbsp;&nbsp;<sup>*</sup>
							<p class="clear">' . $this->l('Priority of the Campaign.') . '</p>
						</div>';

				//Discount Code.
				$this->_html .= '<label>' . $this->l('Discount Code') . ':</label>
						<div class="margin-form">
							<input type="text" name="campaignDiscountCode" value=""/>&nbsp;&nbsp;<sup>*</sup>
							<p class="clear">' . $this->l('Discount Code to used for this campaign.') . '</p>
						</div>';

				//Url data.
				$this->_html .= '
						<label>' . $this->l('Campaign Url Data') . ':</label>
						<div class="margin-form">';
				foreach ($languages as $language)
					$this->_html .= '
							<div id="campaignUrlData_' . $language['id_lang'] . '" style="display: ' . ($language['id_lang'] == $defaultLanguage ? 'block' : 'none') . '; float: left;">
								<input type="text" name="campaignUrlData_' . $language['id_lang'] . '" value="" />
								<span class="hint" name="help_box">' . $this->l('Invalid characters:') . ' <>;=#{}<span class="hint-pointer">&nbsp;</span></span>
							</div>';
				$this->_html .= $this->displayFlags($languages, $defaultLanguage, 'name¤campaignUrlData¤campaignPagename¤campaignText¤campaignExpiryText', 'campaignUrlData', true);
				$this->_html .= '<div class="clear">' . $this->l('To set the campaign for the customers from the mentione url data.') . '</div></div>';

				//Page Name.
				$this->_html .= '
						<label>' . $this->l('Page Name') . ':</label>
						<div class="margin-form">';
				foreach ($languages as $language)
					$this->_html .= '
							<div id="campaignPagename_' . $language['id_lang'] . '" style="display: ' . ($language['id_lang'] == $defaultLanguage ? 'block' : 'none') . '; float: left;">
								<input type="text" name="campaignPagename_' . $language['id_lang'] . '" value="" /><sup> *</sup>
								<span class="hint" name="help_box">' . $this->l('Invalid characters:') . ' <>;=#{}<span class="hint-pointer">&nbsp;</span></span>
							</div>';
				$this->_html .= $this->displayFlags($languages, $defaultLanguage, 'name¤campaignUrlData¤campaignPagename¤campaignText¤campaignExpiryText', 'campaignPagename', true);
				$this->_html .= '<div class="clear"></div></div>';

				//From date.
				includeDatepicker(array('date_from', 'date_to'), true);
				$this->_html .= '
						<label>' . $this->l('From') . ':</label>
						<div class="margin-form">
								<input type="text" value="" name="campaignDateFrom" id="date_from" size="20">&nbsp;&nbsp;<sup>*</sup>
								<p class="clear">' . $this->l('Start date/time from which voucher can be used') . '<br />' . $this->l('Format: YYYY-MM-DD HH:MM:SS') . '</p>
						</div>';
				//To date.
				$this->_html .= '<label>' . $this->l('To') . ':</label>
						<div class="margin-form">
							<input type="text" value="" name="campaignDateTo" id="date_to" size="20">&nbsp;&nbsp;<sup>*</sup>
							<p class="clear">' . $this->l('End date/time at which voucher is no longer valid') . '<br />' . $this->l('Format: YYYY-MM-DD HH:MM:SS') . '</p>
						</div>';

				//Discount Text.
				$this->_html .= '
						<label>' . $this->l('Campaign Text') . ':</label>
						<div class="margin-form">';
				foreach ($languages as $language)
					$this->_html .= '
							<div id="campaignText_' . $language['id_lang'] . '" style="display: ' . ($language['id_lang'] == $defaultLanguage ? 'block' : 'none') . '; float: left;">
								<input type="text" name="campaignText_' . $language['id_lang'] . '" value="" /><sup> *</sup>
								<span class="hint" name="help_box">' . $this->l('Invalid characters:') . ' <>;=#{}<span class="hint-pointer">&nbsp;</span></span>
							</div>';
				$this->_html .= $this->displayFlags($languages, $defaultLanguage, 'name¤campaignUrlData¤campaignPagename¤campaignText¤campaignExpiryText', 'campaignText', true);
				$this->_html .= '<div class="clear">' . $this->l('Campaign text to be displayed in popup.') . '</div></div>';

				//Campaign Expiry Text.
				$this->_html .= '
						<label>' . $this->l('Campaign Expiry Text') . ':</label>
						<div class="margin-form">';
				foreach ($languages as $language)
					$this->_html .= '
							<div id="campaignExpiryText_' . $language['id_lang'] . '" style="display: ' . ($language['id_lang'] == $defaultLanguage ? 'block' : 'none') . '; float: left;">
								<input type="text" name="campaignExpiryText_' . $language['id_lang'] . '" value="" /><sup> *</sup>
								<span class="hint" name="help_box">' . $this->l('Invalid characters:') . ' <>;=#{}<span class="hint-pointer">&nbsp;</span></span>
							</div>';
				$this->_html .= $this->displayFlags($languages, $defaultLanguage, 'name¤campaignUrlData¤campaignPagename¤campaignText¤campaignExpiryText', 'campaignExpiryText', true);
				$this->_html .= '<div class="clear" >' . $this->l('Expiry text to to displayed in popup.') . '</div></div>';

				//Save.
				$this->_html .= '<div class="margin-form clear">
							<input type="submit" name="submitAddCampaign" value="' . $this->l('Save') . '" class="button" />
							<a href="' . $currentIndex . '&configure=' . $this->name . '&action=campaignCancel&token=' . Tools::getAdminTokenLite('AdminModules') . '">
								<input type="button" name="submitCancelCampaign" value="' . $this->l('Cancel') . '" class="button" />
							</a>
						</div>';
			} else if ($params['campaignEdit']) {
				$campaign = New Campaign((int) $params['campaignId']);
				$discount = New Discount((int) $campaign->id_discount);

				//Campaign Name.
				$this->_html .= '
						<label>' . $this->l('Campaign Name') . ':</label>
						<div class="margin-form">';
				foreach ($languages as $language)
					$this->_html .= '
							<div id="name_' . $language['id_lang'] . '" style="display: ' . ($language['id_lang'] == $defaultLanguage ? 'block' : 'none') . '; float: left;">
								<input type="text" name="name_' . $language['id_lang'] . '" value="' . $campaign->name[$language['id_lang']] . '" /><sup> *</sup>
								<span class="hint" name="help_box">' . $this->l('Invalid characters:') . ' <>;=#{}<span class="hint-pointer">&nbsp;</span></span>
							</div>';
				$this->_html .= $this->displayFlags($languages, $defaultLanguage, 'name¤campaignUrlData¤campaignPagename¤campaignText¤campaignExpiryText', 'name', true);
				$this->_html .= '<div class="clear"></div></div>';

				//Cohort Name.
				$this->_html .= '<label>' . $this->l('Group/Cohort Name') . ':</label>
						<div class="margin-form">
								<select name="campaignGroupId">';
				foreach ($groups as $group) {
					$this->_html .= '<option ' . ($group['id_group'] == $campaign->id_group ? 'selected="selected"' : '') . ' value="' . $group['id_group'] . '">' . $group["name"] . '</option>';
				}
				$this->_html .= '</select>&nbsp;&nbsp;<sup>*</sup>
						<p class="clear">' . $this->l('Select the group(cohort) to which the campaign is conducted.') . '</p>
						</div>';

				//Priority of the Campaign.
				$this->_html .= '<label>' . $this->l('Priority') . ':</label>
						<div class="margin-form">
							<input type="text" name="campaignPriority" value="' . $campaign->priority . '"/>&nbsp;&nbsp;<sup>*</sup>
							<p class="clear">' . $this->l('Priority of the Campaign.') . '</p>
						</div>';

				//Discount Code.
				$this->_html .= '<label>' . $this->l('Discount Code') . ':</label>
						<div class="margin-form">
							<input type="text" name="campaignDiscountCode" value="' . $discount->name . '"/>&nbsp;&nbsp;<sup>*</sup>
							<p class="clear">' . $this->l('Discount Code to used for this campaign.') . '</p>
						</div>';

				//Url data.
				$this->_html .= '
						<label>' . $this->l('Campaign Url Data') . ':</label>
						<div class="margin-form">';
				foreach ($languages as $language)
					$this->_html .= '
							<div id="campaignUrlData_' . $language['id_lang'] . '" style="display: ' . ($language['id_lang'] == $defaultLanguage ? 'block' : 'none') . '; float: left;">
								<input type="text" name="campaignUrlData_' . $language['id_lang'] . '" value="' . $campaign->campaign_url_data[$language['id_lang']] . '" /><sup> *</sup>
								<span class="hint" name="help_box">' . $this->l('Invalid characters:') . ' <>;=#{}<span class="hint-pointer">&nbsp;</span></span>
							</div>';
				$this->_html .= $this->displayFlags($languages, $defaultLanguage, 'name¤campaignUrlData¤campaignPagename¤campaignText¤campaignExpiryText', 'campaignUrlData', true);
				$this->_html .= '<div class="clear">' . $this->l('To set the campaign for the customers from the mentione url data.') . '</div></div>';

				//Page Name.
				$this->_html .= '
						<label>' . $this->l('Page Name') . ':</label>
						<div class="margin-form">';
				foreach ($languages as $language)
					$this->_html .= '
							<div id="campaignPagename_' . $language['id_lang'] . '" style="display: ' . ($language['id_lang'] == $defaultLanguage ? 'block' : 'none') . '; float: left;">
								<input type="text" name="campaignPagename_' . $language['id_lang'] . '" value="' . $campaign->campaign_page[$language['id_lang']] . '" /><sup> *</sup>
								<span class="hint" name="help_box">' . $this->l('Invalid characters:') . ' <>;=#{}<span class="hint-pointer">&nbsp;</span></span>
							</div>';
				$this->_html .= $this->displayFlags($languages, $defaultLanguage, 'name¤campaignUrlData¤campaignPagename¤campaignText¤campaignExpiryText', 'campaignPagename', true);
				$this->_html .= '<div class="clear">' . $this->l('Page at which the popup is displayed.') . '</div></div>';

				//From date.
				includeDatepicker(array('date_from', 'date_to'), true);
				$this->_html .= '
						<label>' . $this->l('From') . ':</label>
						<div class="margin-form">
								<input type="text" value="' . $campaign->date_from . '" name="campaignDateFrom" id="date_from" size="20">&nbsp;&nbsp;<sup>*</sup>
								<p class="clear">' . $this->l('Start date/time from which voucher can be used') . '<br />' . $this->l('Format: YYYY-MM-DD HH:MM:SS') . '</p>
						</div>';
				//To date.
				$this->_html .= '<label>' . $this->l('To') . ':</label>
						<div class="margin-form">
							<input type="text" value="' . $campaign->date_to . '" name="campaignDateTo" id="date_to" size="20">&nbsp;&nbsp;<sup>*</sup>
							<p class="clear">' . $this->l('End date/time at which voucher is no longer valid') . '<br />' . $this->l('Format: YYYY-MM-DD HH:MM:SS') . '</p>
						</div>';

				//Discount Text.
				$this->_html .= '
						<label>' . $this->l('Campaign Text') . ':</label>
						<div class="margin-form">';
				foreach ($languages as $language)
					$this->_html .= '
							<div id="campaignText_' . $language['id_lang'] . '" style="display: ' . ($language['id_lang'] == $defaultLanguage ? 'block' : 'none') . '; float: left;">
								<input type="text" name="campaignText_' . $language['id_lang'] . '" value="' . $campaign->campaign_text[$language['id_lang']] . '" /><sup> *</sup>
								<span class="hint" name="help_box">' . $this->l('Invalid characters:') . ' <>;=#{}<span class="hint-pointer">&nbsp;</span></span>
							</div>';
				$this->_html .= $this->displayFlags($languages, $defaultLanguage, 'name¤campaignUrlData¤campaignPagename¤campaignText¤campaignExpiryText', 'campaignText', true);
				$this->_html .= '<div class="clear">' . $this->l('Campaign text to be displayed in popup.') . '</div></div>';

				//Campaign Expiry Text.
				$this->_html .= '
						<label>' . $this->l('Campaign Expiry Text') . ':</label>
						<div class="margin-form">';
				foreach ($languages as $language)
					$this->_html .= '
							<div id="campaignExpiryText_' . $language['id_lang'] . '" style="display: ' . ($language['id_lang'] == $defaultLanguage ? 'block' : 'none') . '; float: left;">
								<input type="text" name="campaignExpiryText_' . $language['id_lang'] . '" value="' . $campaign->campaign_expiry_text[$language['id_lang']] . '" /><sup> *</sup>
								<span class="hint" name="help_box">' . $this->l('Invalid characters:') . ' <>;=#{}<span class="hint-pointer">&nbsp;</span></span>
							</div>';
				$this->_html .= $this->displayFlags($languages, $defaultLanguage, 'name¤campaignUrlData¤campaignPagename¤campaignText¤campaignExpiryText', 'campaignExpiryText', true);
				$this->_html .= '<div class="clear" >' . $this->l('Expiry text to to displayed in popup.') . '</div></div>';


				//Save.
				$this->_html .= '<div class="margin-form clear">
							<input type="submit" name="submitEditCampaign" value="' . $this->l('Save') . '" class="button" />
							<a href="' . $currentIndex . '&configure=' . $this->name . '&action=campaignCancel&token=' . Tools::getAdminTokenLite('AdminModules') . '">
								<input type="button" name="submitCancelCampaign" value="' . $this->l('Cancel') . '" class="button" />
							</a>
						</div>';
			}
		} else if ($params['campaignUpload']) {
			$campaign = New Campaign((int) $params['campaignId']);
			$this->_html .= '<label>' . $this->l("Campaign Name: ") . '</label>
							<div class="margin-form"><h3>' . $campaign->name[$defaultLanguage] . '</h3><div class="clear"></div></div>';
			$this->_html .= '<label>' . $this->l('Select a file') . ' </label>
				<div class="margin-form">
					<input name="file" type="file" /><br />' . $this->l('Choose the CSV/Text Files only.') . '
				</div>
				<!--<label>' . $this->l("Delimiter: ") . '</label>
				<div class="margin-form">
					<input type="text" name="campaignFileUploadDelimiter" value=""/><br />' . $this->l('Delimieters Ex: comma(,), newline(\\n) or tab(\\t) etc..') . '
				</div> -->
				<div class="margin-form">
					<input type="submit" name="campaignSubmitFileUpload" value="' . $this->l('Upload') . '" class="button" />
					<a href="' . $currentIndex . '&configure=' . $this->name . '&action=campaignCancel&token=' . Tools::getAdminTokenLite('AdminModules') . '">
						<input type="button" name="submitCancelCampaign" value="' . $this->l('Back') . '" class="button" />
					</a>
				</div>
				<div class="margin-form">
					' . $this->l('Allowed files are only UTF-8 and iso-8859-1 encoded ones') . '
				</div>';
		}

		//Campaign List.
		$campaigns = Campaign::getCampaigns($defaultLanguage);
		if ($campaigns) {
			$this->_html .= '<hr>';
			$this->_html .= '<table cellspacing="0" cellpadding="0" class="table center" style="margin: 20px 0 40px 0;width:900px;">
							<caption style="margin: 0 0 10px 0"><b>' . $this->l('Campaigns List') . '</b></caption>
							<tbody>
							<tr>
								<th>Priority</th>
								<!--<th>Id</th> -->
								<th>Campaign Name</th>
								<th>Group</th>
								<th>Discount Code</th>
								<th>Url Data</th>
								<th>Popup Display</th>
								<th>Active</th>
								<th>Customers</th>
								<th>Edit</th>
								<th>Delete</th>
								<th>Upload CSV</th>
							</tr>';
			foreach ($campaigns AS $campaign) {
				$discount = New Discount((int) $campaign['id_discount']);
				$group = New Group((int) $campaign["id_group"]);

				$this->_html .= '<tr>
								<td>' . $campaign["priority"] . '</td>
								<!-- <td>' . $campaign["id_campaign"] . '</td> -->
								<td>' . $campaign["name"] . '</td>
								<td>' . Group::getGroupNameById($campaign["id_group"], $defaultLanguage) . '</td>
								<td>' . $discount->name . '</td>
								<td>' . $campaign['campaign_url_data'] . '</td>
								<td>' . $campaign['campaign_page'] . '</td>	';
				$this->_html .= '<td>';
				if ($campaign['active']) {
					$this->_html .= '<a href="' . $currentIndex . '&configure=' . $this->name . '&campaignId=' . $campaign["id_campaign"] . '&action=campaignDisable&token=' . Tools::getAdminTokenLite('AdminModules') . '">
																<img src="../img/admin/enabled.gif" alt="' . $this->l('Enabled') . '" title="' . $this->l('Enabled') . '" style="padding:0px 5px 0px 5px;" />
															</a>
								<br class="clear" />';
				} else {
					$this->_html .= '<a href="' . $currentIndex . '&configure=' . $this->name . '&campaignId=' . $campaign["id_campaign"] . '&action=campaignEnable&token=' . Tools::getAdminTokenLite('AdminModules') . '">
																<img src="../img/admin/disabled.gif" alt="' . $this->l('Disabled') . '" title="' . $this->l('Disabled') . '" style="padding:0px 5px 0px 5px" />
															</a>
														</td>';
				}
				$this->_html .= '<td>' . $group->getCustomers(true) . '</td>';
				$this->_html .= '<td>
									<a href="' . $currentIndex . '&configure=' . $this->name . '&campaignId=' . $campaign["id_campaign"] . '&action=campaignEdit&token=' . Tools::getAdminTokenLite('AdminModules') . '">
										<img src="../img/admin/edit.gif" border="0" alt="' . $this->l('Edit') . '" title="' . $this->l('Edit') . '" />
									</a>
								</td>
								<td><a href="' . $currentIndex . '&configure=' . $this->name . '&campaignId=' . $campaign["id_campaign"] . '&action=campaignDelete&token=' . Tools::getAdminTokenLite('AdminModules') . '"
											onclick="return confirm(\'' . $this->l('Delete Campaign', $campaign["name"], true, false) . ' : ' . $campaign["name"] . '?\');">
										<img src="../img/admin/delete.gif" border="0" alt="' . $this->l('Delete') . '" title="' . $this->l('Delete') . '" />
									</a>
								</td>
								<td style="height:40px;">
									<a href="' . $currentIndex . '&configure=' . $this->name . '&campaignId=' . $campaign["id_campaign"] . '&action=campaignUpload&token=' . Tools::getAdminTokenLite('AdminModules') . '">
										<input type="button" value="' . $this->l('upload') . '"/>
									</a>
								</td>
								</tr>';
			}
			$this->_html .= '</tbody></table>';
		}
		$this->_html .= '</fieldset>
			</form>';
	}

	/* Dynamically add the newly registered customer to the group(cohorts) */

	public function hookCreateAccount($params)
	{
		if ($params['cookie']->logged && $params['cookie']->id_customer) {
			$customer = new Customer((int) $params['cookie']->id_customer);
			$campaigns = Campaign::getCampaigns($params['cookie']->id_lang);
			$newRegisterationCampaignGroupId = $this->getGroupIdByName('CampaignNewReg', (int) $params['cookie']->id_lang);
			foreach ($campaigns AS $campaign) {
				if ($campaign['active'] AND $campaign['id_group'] == $newRegisterationCampaignGroupId) {
					$customer->addGroups(array($newRegisterationCampaignGroupId));
				}
			}
			return;
		}
	}

	/* Dynamically add the customers to the group, if the campaign cookie is set. */

	public function hookAuthentication($params)
	{
		if ($params['cookie']->logged && $params['cookie']->id_customer) {
			$campaigns = Campaign::getCampaigns($params['cookie']->id_lang);
			foreach ($campaigns AS $campaign) {
				if ($campaign['active'] AND isset($_COOKIE[$campaign['campaign_url_data']]) AND $_COOKIE[$campaign['campaign_url_data']] == 1) {
					$customer = new Customer((int) $params['cookie']->id_customer);
					if (!$customer->isMemberOfGroup($campaign['id_group'])) {
						$customer->addGroups(array($campaign['id_group']));
						setcookie($campaign['campaign_url_data'], 1, time() - 1, '/');
						break;
					}
				}
			}
			return;
		}
	}

	/* Display the discount/voucher popups */

	public function hookFooter($params)
	{
		global $smarty;
		$campaigns = Campaign::getCampaigns($params['cookie']->id_lang);

		//Setting the campaign cookie.
		$setCampaign = Tools::getValue('butigo_campaign');
		if (isset($setCampaign) AND $setCampaign) {
			foreach ($campaigns AS $campaign) {
				if ($campaign['campaign_url_data'] != NULL AND $campaign['active'] AND $setCampaign == $campaign['campaign_url_data']) {
					setcookie($campaign['campaign_url_data'], 1, 0, '/');
				}
			}
		}

		//displaying the popup.
		if ($params['cookie']->logged AND $params['cookie']->id_customer) {
			$customer = new Customer((int) $params['cookie']->id_customer);
			//To add the customer to the group if the campaign cookie is set
			foreach ($campaigns AS $campaign) {
				if ($campaign['active'] AND isset($_COOKIE[$campaign['campaign_url_data']]) AND $_COOKIE[$campaign['campaign_url_data']] == 1) {
					if (!$customer->isMemberOfGroup($campaign['id_group'])) {
						$customer->addGroups(array($campaign['id_group']));
						setcookie($campaign['campaign_url_data'], 1, time() - 1, '/');
						break;
					}
				}
			}

			//Display the popup.
			$isCustomerUsedTheCamapignVoucher = 0;
			foreach ($campaigns AS $campaign) {
				if ($campaign['active'] AND !strpos($_SERVER['REQUEST_URI'], $campaign['campaign_page']) === false) {
					if ($customer->isMemberOfGroup($campaign['id_group'])) {
						$isCustomerUsedTheCamapignVoucher = Discount::checkCustomerAsUsedDiscount($campaign['id_discount'], $params['cookie']->id_customer);
						$discount = New Discount((int) $campaign['id_discount']);
						$campaignExpiry = strtotime($campaign['date_to']) - time();
						$discountExpiry = strtotime($discount->date_to) - time();
						if ($campaignExpiry > 0 AND !$isCustomerUsedTheCamapignVoucher AND $discount->quantity >= 0 AND $discountExpiry > 0) {
							$smarty->assign(array('campaignImage' => $this->_path . 'img/Discount-Coupon.png',
								'couponCode' => /* Discount::getDiscountNameById($campaign['id_discount']) */$discount->name,
								'couponCodeText' => $campaign['campaign_text'],
								'discountExpiry' => $campaign['campaign_expiry_text'])
							);
							return $this->display(__FILE__, 'campaignmanagement.tpl');
						}
					}
				}
			}
		}

		return;
	}

	/* Returns the  group name
	 * @param string $group_name, group name
	 * @param integer $id_lang , Language id
	 */

	public function getGroupIdByName($group_name, $id_lang)
	{
		$row = Db::getInstance()->getRow('
    		SELECT gl.`id_group`
    		FROM ' . _DB_PREFIX_ . 'group_lang gl
    		WHERE gl.`name` LIKE "%' . ($group_name) . '%" AND gl.`id_lang` =' . $id_lang
		);

		return $row['id_group'];
	}

}

?>

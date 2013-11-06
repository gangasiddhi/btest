<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
if (!defined('_CAN_LOAD_FILES_'))
    exit;

class TaggingAndFiltering extends Module {

    public function __construct() {
        $this->name = 'taggingandfiltering';
        $this->tab = 'front office features';
        $this->version = 1.4;
        $this->author = 'PrestaShop';

        parent::__construct();

        $this->displayName = $this->l('Tagging and Filtering');
        $this->description = $this->l('This module must be enabled if you want to use Tagging and Filtering.');

        $this->hookHeader();
    }

    public function install() {
        if (!parent::install()
                OR !$this->registerHook('header')
                OR !$this->registerHook('producttags')
                OR !$this->registerHook('filterTags')
                OR !$this->registerHook('productFilter'))
            return false;

        return true;
    }

	public function uninstall() 
	{
		return (parent::uninstall());
	}
	
    public function hookHeader() {
        if (strpos($_SERVER['PHP_SELF'], 'category') !== false) {
            Tools::addCSS(_THEME_CSS_DIR_ . "modules/taggingandfiltering/productfilter.css");
            Tools::addJS(array($this->_path . "assets/productFilter.js",
                _PS_JS_DIR_ . 'jquery/jquery.lazyloader.js',
            ));
        }
    }

    public function hookproducttags($params) {
        global $cookie, $smarty;

        $defaultLanguage = (int) (Configuration::get('PS_LANG_DEFAULT'));

        if (!$params['content']) {
            $countTags = Tag::getEachProductTags($params['id_product'], $defaultLanguage) ? sizeof(Tag::getEachProductTags($params['id_product'], $defaultLanguage)) : 0;
            $smarty->assign(array(
                'countTags' => $countTags,
                'idTab' => $params['idTab']));
            return $this->display(__FILE__, 'product-tag-head.tpl');
        } else {
            $product = $params['product'];
            if (!Validate::isLoadedObject($product))
                die(Tools::displayError('Some parameters are missing.'));
            $tags = Tag::getAvailableTagsForProduct($product->id, $defaultLanguage);
            $each_product_tags = Tag::getEachProductTags($product->id, $defaultLanguage);
            $smarty->assign(array(
                'tags' => $tags,
                'each_product_tags' => $each_product_tags,
                'languages' => $params['languages'],
                'defaultLanguage' => $params['defaultLanguage'],
                'path' => $this->_path
            ));

            return $this->display(__FILE__, 'product-tag-content.tpl');
        }
    }

    public function hookfilterTags($params) {
        global $cookie, $smarty;
        global $cookie, $smarty;

        /* Get the Shoe sizes and Available colors of the products */
        $productAttributes = Attribute::getAttributes($cookie->id_lang);

        $shoeSizes = array();
        $colors = array();

        $customerSelectedShoeSizes = array();
        $customerSelectedColors = array();
        if (isset($_COOKIE['shoeSize'])) {
            $customerSelectedShoeSizes = explode(',', $_COOKIE['shoeSize']);
        }/* else{
          $customerSelectedShoeSizes = Customer::getShoeSize($cookie->id_customer);
          } */

        if (isset($_COOKIE['color'])) {
            $customerSelectedColors = explode(',', $_COOKIE['color']);
        }

        foreach ($productAttributes as $attribure) {
            if (intval($attribure['id_attribute_group']) === 4 && intval($attribure['is_color_group']) === 0) {
                $shoeSizes[] = $attribure['name'];
            } else if (intval($attribure['id_attribute_group']) === 2 && intval($attribure['is_color_group']) === 1) {
                $colors[] = $attribure['name'];
            }
        }

        $smarty->assign(array('shoeSizes' => $shoeSizes,
            'colors' => $colors,
            'customerSelectedShoeSizes' => $customerSelectedShoeSizes,
            'customerSelectedColors' => $customerSelectedColors
        ));


        $filters = Filter::getFiltersAndTags($cookie->id_lang);
        if ($filters) {
            $filter_selections = array();
            foreach ($filters as $filter) {
                $filter_selections = Tools::getValue($filter['filter_name']);
            }
            $smarty->assign(array('filters' => $filters));
        }
        return $this->display(__FILE__, 'displayFilterTags.tpl');
    }

    private function _postProcess() {
        global $currentIndex;
        $languages = Language::getLanguages(false);
        $errors = array();


        if (Tools::isSubmit('submitFilterTags') || Tools::isSubmit('updateFilterTags')) {
            $active = 1;
            $update = false;
            $mode = 1;
            if (Tools::getValue('id_filter') && Tools::isSubmit('updateFilterTags')) {
                $filter = new Filter(Tools::getValue('id_filter'));
                $active = Tools::getValue('statusFilter');
                $mode = Tools::getValue('filterMode');
                $update = true;
            }
            else
                $filter = new Filter();
            foreach ($languages AS $language) {
                $filter->name[$language['id_lang']] = Tools::getValue('name_' . $language['id_lang']);
                $filter->description[$language['id_lang']] = Tools::getValue('description_' . $language['id_lang']);
            }
            $filter->active = $active;
            $filter->mode = $mode;
            if ($update) {
                if (!$filter->update(true))
                    $errors[] = $this->l('Filter Could not be updated');
                $param = '&filterUpdated';
            }
            else {
                if (!$filter->add(true))
                    $errors[] = $this->l('Filter Could not be added');
                $param = '&filterAdded';
            }
            if (!sizeof($errors)) {
                if (!$filter->deleteTagsAttachedToFilter())
                    return false;
                foreach ($languages AS $language) {
                    if (Tools::getValue('tags' . $language['id_lang'] . '')) {
                        $filter->addTagsToFilter(Tools::getValue('tags' . $language['id_lang'] . ''), $language['id_lang']);
                    }
                }
            } else {
                $this->_html .= $this->displayError(implode('<br />', $errors));
                return false;
            }
            Tools::redirectAdmin($currentIndex . '&configure=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules') . '' . $param . '');
        } elseif (Tools::isSubmit('deletefilter')) {
            if (Tools::getValue('id_filter') && Tools::isSubmit('deletefilter')) {
                $filter = new Filter(Tools::getValue('id_filter'));
                $filter->delete();
            }
        } elseif (Tools::isSubmit('changePosition')) {
            if (Tools::getValue('id_filter') && Tools::getValue('position')) {
                $filterObj = new Filter(Tools::getValue('id_filter'));
                if (Validate::isLoadedObject($filterObj)) {
                    if (!$filterObj->updatePosition(Tools::getValue('way'), Tools::getValue('position')))
                        $this->_html .= $this->displayError('Filter\'s position cannot be updated');
                    else
                        Tools::redirectAdmin($currentIndex . '&configure=' . $this->name . '&token=' . Tools::getAdminTokenLite('AdminModules') . '');
                }
                else
                    $this->_html .= $this->displayError('Invalid Filter Object');
            }
            else {
                $this->_html .= $this->displayError('Missing Parameters');
            }
        } elseif (Tools::isSubmit('filterUpdated'))
            $this->_html .= $this->displayConfirmation($this->l(' Filter Successfully Updated'));
        elseif (Tools::isSubmit('filterAdded'))
            $this->_html .= $this->displayConfirmation($this->l(' Filter Successfully Added'));
    }

    public function getContent() {
        $this->_html = '';
        $languages = Language::getLanguages(false);
        $this->_postProcess();
        $this->_html .= '<h2>' . $this->l('Tagging and Filtering Module Customization') . '</h2>';
        if (Tools::isSubmit('updatefilter')) {
            if ($id_filter = Tools::getValue('id_filter'))
                $this->displayFilterEditForm($id_filter);
        }
        else
            $this->_displayForm();
        return ($this->_html);
    }

    private function _displayForm() {
        global $cookie, $currentIndex;
        $languages = Language::getLanguages(false);

        /* Get coookie language */
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
        /* Get coookie language */

        $tags = Tag::getAllTags();
        $filters = Filter::getAllFilters($defaultLanguage);
        //echo "<pre>";print_r(  $tags);exit;
        //$each_product_tags = Tag::getEachProductTags($obj->id,$cookie->id_lang);
        $this->_html .= '
            <script type="text/javascript">
				$(document).ready(function() {
					id_language = ' . $defaultLanguage . ';
                                            });
			</script><fieldset style="width: 800px;">
                <legend><img src="' . $this->_path . 'logo.gif" alt="" title="" /> ' . $this->displayName . '</legend><form method="post" id="filter" action="' . $_SERVER['REQUEST_URI'] . '">
            <script src="' . $this->_path . 'assets/filterTags.js" type="text/javascript"></script>';

        $this->_html .= '<img src="../img/admin/add.gif" border="0" /><a href="#" id="showForm"><b>' . $this->l('Add New Filter') . '</b></a><div class="clear"></div>';
        $this->_html .= '<div class="addNewFilter" style="display:none;">
                    <label>' . $this->l('Name:') . ' </label>
				<div class="margin-form">';
        foreach ($languages as $language)
            $this->_html .= '
					<div id="name_' . $language['id_lang'] . '" style="display: ' . ($language['id_lang'] == $defaultLanguage ? 'block' : 'none') . '; float: left;">
						<input size="33" type="text" name="name_' . $language['id_lang'] . '" value="" /><sup> *</sup>
						<span class="hint" name="help_box">' . $this->l('Invalid characters:') . ' <>;=#{}<span class="hint-pointer">&nbsp;</span></span>
					</div>';
        $this->_html .= $this->displayFlags($languages, $defaultLanguage, 'name¤description¤filterTags¤filterTagsAttach', 'name', true);
        $this->_html .= '
					<div class="clear"></div>
				</div>
				<label>' . $this->l('Description:') . ' </label>
				<div class="margin-form">';
        foreach ($languages as $language)
            $this->_html .= '
					<div id="description_' . $language['id_lang'] . '" style="display: ' . ($language['id_lang'] == $defaultLanguage ? 'block' : 'none') . '; float: left;">
                                                <textarea cols="25" rows="3" id="description_' . $language['id_lang'] . '" name="description_' . $language['id_lang'] . '"></textarea>
						<span class="hint" name="help_box">' . $this->l('Invalid characters:') . ' <>;=#{}<span class="hint-pointer">&nbsp;</span></span>
						<p style="clear: both">' . $this->l('Term or phrase displayed to the customer') . '</p>
					</div>';
        $this->_html .= $this->displayFlags($languages, $defaultLanguage, 'name¤description¤filterTags¤filterTagsAttach', 'description', true);
        $this->_html .= '
					<div class="clear"></div>
				</div>
				<label>' . $this->l('Status:') . ' </label>
				<div class="margin-form">
					<input type="radio" name="statusFilter" id="enabled" value="1" checked="checked"/>
					<label class="t" for="filter_enabled"><img src="../img/admin/enabled.gif" alt="' . $this->l('Enabled') . '" title="' . $this->l('Yes') . '" />' . $this->l('Enabled') . '</label><br/>
					<input type="radio" name="statusFilter" id="disabled" value="0" />
					<label class="t" for="filter_disabled"><img src="../img/admin/disabled.gif" alt="' . $this->l('Disabled') . '" title="' . $this->l('No') . '" />' . $this->l('Disabled') . '</label>
				</div>

                                <label>' . $this->l('Mode:') . ' </label>
				<div class="margin-form">
                                    <input type="radio" name="filterMode" id="enabled" value="1" checked="checked"/>
                                    <label class="t" for="filter_enabled"><img src="../img/admin/enabled.gif" alt="' . $this->l('Enabled') . '" title="' . $this->l('Yes') . '" />' . $this->l('Single') . '</label><br/>
                                    <input type="radio" name="filterMode" id="disabled" value="0" />
                                    <label class="t" for="filter_disabled"><img src="../img/admin/disabled.gif" alt="' . $this->l('Disabled') . '" title="' . $this->l('No') . '" />' . $this->l('Multiple') . '</label>
				</div>
				<div class="small"><sup>*</sup> ' . $this->l('Required field') . '</div>
                    <div class="clear"></div>';

        $this->_html .= '<div class="clear">&nbsp;</div>
                    <table>
                        <tr>
                            <td>
                                <p>' . $this->l('Available Tags:') . '</p>
                                        ';
        if ($tags) {
            foreach ($languages as $language) {
                $this->_html .= '<div id="filterTags_' . $language['id_lang'] . '" style="display: ' . ($language['id_lang'] == $defaultLanguage ? 'block' : 'none') . ';"><select multiple id="selectFilterTags_' . $language['id_lang'] . '" class="selectFilterTags" style="width:300px;height:160px;">';
                if (isset($tags[$language['id_lang']]))
                    foreach ($tags[$language['id_lang']] as $key1 => $tag)
                        $this->_html .= ' <option value="' . $key1 . '">' . $tag . '</option>';
                $this->_html .= ' </select>';
                $this->_html .= '<br/><br/><a href="#" class="addTag" langId="' . $language['id_lang'] . '"
                                                        style="text-align:center;display:block;border:1px solid #aaa;text-decoration:none;background-color:#fafafa;color:#123456;margin:2px;padding:2px">
                                                            ' . $this->l('Attach') . ' &gt;&gt;
                                                    </a></div>';
            }
        }
        $this->_html .= '</td>
                                    <td style="padding-left:20px;">
                                        <p>' . $this->l('Attached Tags for this filter:') . '</p>';
        foreach ($languages as $language) {
            $this->_html .= '<div id="filterTagsAttach_' . $language['id_lang'] . '" style="display: ' . ($language['id_lang'] == $defaultLanguage ? 'block' : 'none') . ';"> <select multiple class="selectedFilterTags" id="selectedFilterTags_' . $language['id_lang'] . '"  name="tags' . $language['id_lang'] . '[]" style="width:300px;height:160px;">
                                                </select><br/><br/>
                                            <a href="#" class="removeTag" langId="' . $language['id_lang'] . '"
                                            style="display:block;text-align:center;border:1px solid #aaa;text-decoration:none;background-color:#fafafa;color:#123456;margin:2px;padding:2px">
                                                    &lt;&lt; ' . $this->l('Remove') . '
                                            </a></div>';
        }
        $this->_html .= '</td>
                                    <td style="float: left;">' . $this->displayFlags($languages, $defaultLanguage, 'name¤description¤filterTags¤filterTagsAttach', 'filterTags', true) . '
                                        <div style="display:none;">' . $this->displayFlags($languages, $defaultLanguage, 'name¤description¤filterTags¤filterTagsAttach', 'filterTagsAttach', true) . '</div></td>
                                </tr>
                        </table>
                        <div class="clear">&nbsp;</div>
                        <div class="margin-form">
                            <input type="submit" value="' . $this->l('Save') . '" name="submitFilterTags" class="button" />
                        </div></div>';
        $this->_html .= '</form>';
        /* show Filter Display List */
        if ($filters) {
            $this->_html .= '<table style="margin: 20px 0 40px 0;" class="table" cellpadding="0" cellspacing="0">
						<tr>
							<th><input type="checkbox" name="checkme" class="noborder" /></th>
                                                        <th style="width: 50px;">' . $this->l('ID') . '</th>
                                                        <th style="width: 150px;">' . $this->l('Name') . '</th>
                                                        <th style="width: 300px;">' . $this->l('Description') . '</th>
                                                        <th>' . $this->l('Position') . '</th>
                                                        <th>' . $this->l('Displayed') . '</th>
							<th>' . $this->l('Actions') . '</th>
						</tr>';
//                echo "<pre>";print_R($filters);echo "</pre>";
            $no_of_filters = sizeof($filters);
            foreach ($filters AS $key => $filter) {
                $down = ($key + 1) + 1;
                $up = ($key + 1) - 1;
                $this->_html .= '
						<tr>
                                                    <td class="center"><input type="checkbox" name="filter" value="' . $filter['id_filter'] . '" class="noborder" /></td>
                                                    <td>' . $filter['id_filter'] . '</td>
                                                    <td>' . $filter['name'] . '</td>
                                                    <td>' . $filter['description'] . '</td>
                                                    <td class="center">
                                                        <a ' . ($key == 0 || $no_of_filters == 1 ? 'style="display: none;"' : '') . ' href="' . $currentIndex . '&configure=' . $this->name . '&changePosition&way=0&id_filter=' . $filter['id_filter'] . '&position=' . $up . '&token=' . Tools::getAdminTokenLite('AdminModules') . '">
                                                            <img src="../img/admin/up.gif"
                                                            alt="' . $this->l('Up') . '" title="' . $this->l('Up') . '" />
                                                        </a>
                                                        <a ' . ($no_of_filters == 1 || $no_of_filters == $key + 1 ? 'style="display: none;"' : '') . ' href="' . $currentIndex . '&configure=' . $this->name . '&changePosition&way=1&id_filter=' . $filter['id_filter'] . '&position=' . $down . '&token=' . Tools::getAdminTokenLite('AdminModules') . '">
                                                            <img src="../img/admin/down.gif"
                                                            alt="' . $this->l('Down') . '" title="' . $this->l('Down') . '" />
                                                        </a>
                                                    </td>';
                $this->_html .= '<td class="center">';
                if ($filter['active']) {
                    $this->_html .= '<a href="' . $currentIndex . '&configure=' . $this->name . '&id_filter=' . $filter['id_filter'] . '&enablefilter&token=' . Tools::getAdminTokenLite('AdminModules') . '">
                                                            <img src="../img/admin/enabled.gif" alt="' . $this->l('Enabled') . '" title="' . $this->l('Enabled') . '" style="padding:0px 5px 0px 5px;" />
                                                        </a>
							<br class="clear" />';
                } else {
                    $this->_html .= '<a href="' . $currentIndex . '&configure=' . $this->name . '&id_filter=' . $filter['id_filter'] . '&disablefilter&token=' . Tools::getAdminTokenLite('AdminModules') . '">
                                                            <img src="../img/admin/disabled.gif" alt="' . $this->l('Disabled') . '" title="' . $this->l('Disabled') . '" style="padding:0px 5px 0px 5px" />
                                                        </a>
                                                    </td>';
                }
                $this->_html .= ' <td class="center">
                                                        <a href="' . $currentIndex . '&configure=' . $this->name . '&id_filter=' . $filter['id_filter'] . '&updatefilter&token=' . Tools::getAdminTokenLite('AdminModules') . '">
                                                        <img src="../img/admin/edit.gif" border="0" alt="' . $this->l('Edit') . '" title="' . $this->l('Edit') . '" /></a>&nbsp;
                                                        <a href="' . $currentIndex . '&configure=' . $this->name . '&id_filter=' . $filter['id_filter'] . '&deletefilter&token=' . Tools::getAdminTokenLite('AdminModules') . '"
                                                        onclick="return confirm(\'' . $this->l('Delete attribute', __CLASS__, true, false) . ' : ' . $filter['name'] . '?\');">
                                                        <img src="../img/admin/delete.gif" border="0" alt="' . $this->l('Delete') . '" title="' . $this->l('Delete') . '" /></a>
                                                    </td>
						</tr>';
            }
            $this->_html .= '</table>';
        }
        $this->_html .= '</fieldset>';
    }

    function displayFilterEditForm($id_filter) {
        global $cookie, $currentIndex;
        $languages = Language::getLanguages(false);
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
        $filterObj = new Filter($id_filter);
        $filterTags = $filterObj->getTagsAttachedToFilter();
        $availabletags = $filterObj->getTagsAvailableForFilter();

//        print_r($filterTags );
        $this->_html .= ' <script type="text/javascript">
				$(document).ready(function() {
					id_language = ' . $defaultLanguage . ';
                                            });
			</script>
            <form method="post" id="filter" action="' . $_SERVER['REQUEST_URI'] . '">
            <fieldset style="width: 800px;">
            <script src="' . $this->_path . 'assets/filterTags.js" type="text/javascript"></script>
            <input type="hidden" value="' . $filterObj->id . '" name="id_filter"/>
                <legend><img src="' . $this->_path . 'logo.gif" alt="" title="" /> ' . $this->displayName . '</legend>';
        $this->_html .= '
                    <label>' . $this->l('Name:') . ' </label>
				<div class="margin-form">';
        foreach ($languages as $language)
            $this->_html .= '
					<div id="name_' . $language['id_lang'] . '" style="display: ' . ($language['id_lang'] == $defaultLanguage ? 'block' : 'none') . '; float: left;">
						<input size="33" type="text" name="name_' . $language['id_lang'] . '" value="' . $filterObj->name[$language['id_lang']] . '" /><sup> *</sup>
						<span class="hint" name="help_box">' . $this->l('Invalid characters:') . ' <>;=#{}<span class="hint-pointer">&nbsp;</span></span>
					</div>';
        $this->_html .= $this->displayFlags($languages, $defaultLanguage, 'name¤description¤filterTags¤filterTagsAttach', 'name', true);
        $this->_html .= '
					<div class="clear"></div>
				</div>
				<label>' . $this->l('Description:') . ' </label>
				<div class="margin-form">';
        foreach ($languages as $language)
            $this->_html .= '
					<div id="description_' . $language['id_lang'] . '" style="display: ' . ($language['id_lang'] == $defaultLanguage ? 'block' : 'none') . '; float: left;">
                                                <textarea cols="25" rows="3" id="description_' . $language['id_lang'] . '" name="description_' . $language['id_lang'] . '" value="">' . $filterObj->description[$language['id_lang']] . '</textarea>
						<span class="hint" name="help_box">' . $this->l('Invalid characters:') . ' <>;=#{}<span class="hint-pointer">&nbsp;</span></span>
						<p style="clear: both">' . $this->l('Term or phrase displayed to the customer') . '</p>
					</div>';
        $this->_html .= $this->displayFlags($languages, $defaultLanguage, 'name¤description¤filterTags¤filterTagsAttach', 'description', true);
        $this->_html .= '
					<div class="clear"></div>
				</div>

                                <label>' . $this->l('Mode:') . ' </label>
				<div class="margin-form">
                                    <input type="radio" name="filterMode" id="enabled" value="1"  ' . ($filterObj->mode ? 'checked="checked"' : '') . '/>
                                    <label class="t" for="filter_enabled"><img src="../img/admin/enabled.gif" alt="' . $this->l('Enabled') . '" title="' . $this->l('Yes') . '" />' . $this->l('Single') . '</label><br/>
                                    <input type="radio" name="filterMode" id="disabled" value="0" ' . (!$filterObj->mode ? 'checked="checked"' : '') . '/>
                                    <label class="t" for="filter_disabled"><img src="../img/admin/disabled.gif" alt="' . $this->l('Disabled') . '" title="' . $this->l('No') . '" />' . $this->l('Multiple') . '</label>
				</div>

				<label>' . $this->l('Status:') . ' </label>
				<div class="margin-form">
					<input type="radio" name="statusFilter" id="enabled" value="1" ' . ($filterObj->active ? 'checked="checked"' : '') . '/>
					<label class="t" for="filter_enabled"><img src="../img/admin/enabled.gif" alt="' . $this->l('Enabled') . '" title="' . $this->l('Yes') . '" />' . $this->l('Enabled') . '</label><br/>
					<input type="radio" name="statusFilter" id="disabled" value="0" ' . (!$filterObj->active ? 'checked="checked"' : '') . '/>
					<label class="t" for="filter_disabled"><img src="../img/admin/disabled.gif" alt="' . $this->l('Disabled') . '" title="' . $this->l('No') . '" />' . $this->l('Disabled') . '</label>
				</div>



				<div class="small"><sup>*</sup> ' . $this->l('Required field') . '</div>
                    <div class="clear"></div>';

        $this->_html .= '<div class="clear">&nbsp;</div>
                    <table>
                        <tr>
                            <td>
                                <p>' . $this->l('Available Tags:') . '</p>';
        if ($availabletags) {
            foreach ($languages as $language) {
                $this->_html .= '<div id="filterTags_' . $language['id_lang'] . '" style="display: ' . ($language['id_lang'] == $defaultLanguage ? 'block' : 'none') . ';"><select multiple id="selectFilterTags_' . $language['id_lang'] . '" class="selectFilterTags" style="width:300px;height:160px;">';
                if (isset($availabletags[$language['id_lang']]))
                    foreach ($availabletags[$language['id_lang']] as $key1 => $tag)
                        $this->_html .= ' <option value="' . $key1 . '">' . $tag . '</option>';
                $this->_html .= ' </select>';
                $this->_html .= '<br/><br/><a href="#" class="addTag" langId="' . $language['id_lang'] . '"
                                                    style="text-align:center;display:block;border:1px solid #aaa;text-decoration:none;background-color:#fafafa;color:#123456;margin:2px;padding:2px">
                                                        ' . $this->l('Attach') . ' &gt;&gt;
                                                </a></div>';
            }
        }
        $this->_html .= '
                                    </td>
                                    <td style="padding-left:20px;">
                                    <p>' . $this->l('Attached Tags for this filter:') . '</p>';
        if ($filterTags) {
            foreach ($languages as $language) {
                $this->_html .= '<div id="filterTagsAttach_' . $language['id_lang'] . '" style="display: ' . ($language['id_lang'] == $defaultLanguage ? 'block' : 'none') . ';"><select multiple id="selectedFilterTags_' . $language['id_lang'] . '" name="tags' . $language['id_lang'] . '[]" class="selectedFilterTags" style="width:300px;height:160px;">';
                if (isset($filterTags[$language['id_lang']]))
                    foreach ($filterTags[$language['id_lang']] as $tag) {
                        $this->_html .= ' <option value="' . $tag['id_tag'] . '">' . $tag['name'] . '</option>';
                    }
                $this->_html .= ' </select>';
                $this->_html .= '<br/><br/><a href="#" class="removeTag" langId="' . $language['id_lang'] . '"
                                            style="text-align:center;display:block;border:1px solid #aaa;text-decoration:none;background-color:#fafafa;color:#123456;margin:2px;padding:2px">
                                                ' . $this->l('Remove') . ' &gt;&gt;
                                        </a></div>';
            }
        }
        $this->_html .= '</td><td style="float: left;">' . $this->displayFlags($languages, $defaultLanguage, 'name¤description¤filterTags¤filterTagsAttach', 'filterTags', true) . '
                                        <div style="display:none;">' . $this->displayFlags($languages, $defaultLanguage, 'name¤description¤filterTags¤filterTagsAttach', 'filterTagsAttach', true) . '</div></td>
                                </tr>
                        </table>
                        <div class="clear">&nbsp;</div>
                        <div class="margin-form">
                            <input type="submit" value="' . $this->l('Save') . '" name="updateFilterTags" class="button" />
                        </div></fieldset> ';
    }

    public function displaySizeTagFilterProducts($categoryId, $tagFilterList = NULL, $sizeFilterList = NULL) {
        global $cookie, $smarty;

        $log = false;

        if ($categoryId) {
            $category = new Category($categoryId);
            /* Number of products to be displayed per page */
            $numberOfProductsPerPage = (int) (Configuration::get('PS_PRODUCTS_PER_PAGE'));

            $products = $category->getSizeTagFilterProducts((int) ($cookie->id_lang), 1, $numberOfProductsPerPage, 'position', 'ASC', false, true, false, 1, true, false, $tagFilterList, $sizeFilterList);

            if ($log) {
                $myFile = _PS_LOG_DIR_ . "/productFilterdata.txt";
                $fh = fopen($myFile, 'a') or die("can't open file");
                fwrite($fh, "\nCategory:" . print_r($category, true) . "\nProducts:" . print_r($products, true));
                fclose($fh);
            }

            /* Favourite Button */
            $is_my_fav_active = Configuration::get('PS_MY_FAV_ACTIVE');
            $product_ids = array();
            $ipas = array();
            $favourite_products = array();
            if ($is_my_fav_active == 1) {
                $smarty->assign('is_my_fav_active', $is_my_fav_active);

                $favourite_products = Customer::getFavouriteProductsByIdCustomer($cookie->id_customer);
                if ($favourite_products) {
                    foreach ($favourite_products as $product) {
                        $product_ids[] = $product['id_product'];
                        $ipas[] = $product['id_product_attribute'];
                    }

                    $smarty->assign(array(
                        'my_fav_ids' => $product_ids,
                        'my_fav_ipa' => $ipas
                    ));
                }
            }
            /* Favourite Button */

			/*ShowRoom Disappear Start*/
			if (intval($cookie->id_customer)) {
				$customer = new Customer(intval($cookie->id_customer));
				if (Configuration::get('PRODUCT_DISAPPEAR_FROM_BOUTIQUE') AND $products) {
					$products = $customer->disappearDiscountedProducts($products);
				}
			}
			/*ShowRoom Disappear End*/

            $smarty->assign(array(
                'products' => (isset($products) AND $products) ? $products : NULL,
                'id_category' => (int) ($categoryId),
                'tpl_dir' => _PS_THEME_DIR_,
                'last_qties' => intval(Configuration::get('PS_LAST_QTIES')),
                'prodsmallSize' => Image::getSize('prodsmall'),
                'img_ps_dir' => _PS_IMG_,
                'add_prod_display' => Configuration::get('PS_ATTRIBUTE_CATEGORY_DISPLAY'),
                'categorySize' => Image::getSize('category'),
                'col_img_dir' => _PS_COL_IMG_DIR_,
                'mediumSize' => Image::getSize('medium'),
                'thumbSceneSize' => Image::getSize('thumb_scene'),
                'homeSize' => Image::getSize('home')
            ));
            return $this->display(__FILE__, 'filteredProducts.tpl');
        }
        return false;
    }

}

?>

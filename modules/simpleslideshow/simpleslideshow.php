<?php

/*
 * Copyright 2010 Mathieu Civel
 */


class SimpleSlideshow extends Module {

	private $_postErrors = array();

	function __construct() {
		$this->name = 'simpleslideshow';
		parent::__construct();

		$this->tab = 'front_office_features';
		$this->version = '0.6';
		$this->displayName = $this->l('Simple Lightweight Slideshow');
		$this->description = $this->l('Display a javascript slideshow with fading transition on the homepage or in a column.');
	}

	public function install()
	{
		if (!parent::install() OR !$this->registerHook('slideShow') OR !$this->registerHook('header'))
			return false;

		$images = $this->_parseImageDir();
		if (!$images)
			return false;

        $langs = '';
        for($i = 0; $i < count($images); $i++)
            $langs .= 'all;';

		if (!Configuration::updateValue($this->name.'_timeout'	, 6000) 	OR
			!Configuration::updateValue($this->name.'_speed'	, 1000) 	OR
			!Configuration::updateValue($this->name.'_height'	, 'auto') 	OR
			!Configuration::updateValue($this->name.'_width'	, 'auto')   OR
            !Configuration::updateValue($this->name.'_margin'	, '10') 	OR
			!Configuration::updateValue($this->name.'_sync'		, 'true') 	OR
			!Configuration::updateValue($this->name.'_fit'		, 'false') 	OR
			!Configuration::updateValue($this->name.'_pause'	, 'true') 	OR
			!Configuration::updateValue($this->name.'_delay'	, 0) 		OR
			!Configuration::updateValue($this->name.'_links'	, '')		OR
			!Configuration::updateValue($this->name.'_langs'	, $langs)	OR
			!Configuration::updateValue($this->name.'_images'	, implode(";", $images)))
			return false;

		return true;
	}

	public function uninstall() 
	{
		return (parent::uninstall());
	}
	
	private function _postValidation()
	{
		if (!Validate::isUnsignedInt(Tools::getValue('timeout')))
			$this->_postErrors[] = $this->l('Timeout : invalid value, must be a unsigned number.');
		if (!Validate::isUnsignedInt(Tools::getValue('speed')))
			$this->_postErrors[] = $this->l('Transition speed : invalid value, must be a unsigned number.');
		if (!Validate::isUnsignedInt(Tools::getValue('height')) && (Tools::getValue('height') != 'auto'))
			$this->_postErrors[] = $this->l('Container height : invalid value, must be a unsigned number.');
        if (!Validate::isUnsignedInt(Tools::getValue('width')) && (Tools::getValue('width') != '') && (Tools::getValue('width') != 'auto'))
			$this->_postErrors[] = $this->l('Container width : invalid value, must be a unsigned number.');
		if (!Validate::isInt(Tools::getValue('delay')))
			$this->_postErrors[] = $this->l('Initial delay : invalid value, must be a number.');
	}

	private function _postProcess()
	{
		Configuration::updateValue($this->name.'_timeout'	,  Tools::getValue('timeout'));
		Configuration::updateValue($this->name.'_speed'		,  Tools::getValue('speed'));
		Configuration::updateValue($this->name.'_height'	,  Tools::getValue('height'));
		Configuration::updateValue($this->name.'_width'	    ,  Tools::getValue('width'));
		Configuration::updateValue($this->name.'_margin'	,  Tools::getValue('margin'));
		Configuration::updateValue($this->name.'_delay'		,  Tools::getValue('delay'));
		Configuration::updateValue($this->name.'_images'	,  Tools::getValue('image_data'));
		Configuration::updateValue($this->name.'_links'		,  Tools::getValue('link_data'));
		Configuration::updateValue($this->name.'_langs'		,  Tools::getValue('lang_data'));
		Configuration::updateValue($this->name.'_sync'		, (Tools::getValue('sync') 	? 'true' : 'false'));
		Configuration::updateValue($this->name.'_fit'		, (Tools::getValue('fit') 	? 'true' : 'false'));
		Configuration::updateValue($this->name.'_pause'		, (Tools::getValue('pause') ? 'true' : 'false'));

		$this->_html .= '<div class="conf confirm">'.$this->l('Settings updated').'</div>';
	}

	public function getContent() {
		$this->_html .= '<h2>'.$this->displayName.'</h2>';

		if (Tools::isSubmit('submit'))
		{
			$this->_postValidation();

			if (!sizeof($this->_postErrors))
				$this->_postProcess();
			else
			{
				foreach ($this->_postErrors as $err)
				{
					$this->_html .= '<div class="alert error">'.$err.'</div>';
				}
			}
		}

		$this->_displayForm();
	}

	private function _displayForm() {
		$dirImages = $this->_parseImageDir();
		$confImages = $this->_getImageArray();
		$nbDirImages = count($dirImages);
		$nbConfImages = count($confImages);

		echo $this->_html;

		echo '
			<script type="text/javascript" src="../js/jquery/jquery.tablednd_0_5.js"></script>
			<script type="text/javascript" src="../modules/'.$this->name.'/ajaxupload.js"></script>

			<table id="hidden-row" style="display:none">' . $this->_getTableRowHTML(0, 2, '') . '</table>

			<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
				<fieldset>
				<legend><img src="../img/admin/cog.gif" alt="" class="middle" />'.$this->l('Settings').'</legend>

					<label>'.$this->l('Timeout').'</label>
					<div class="margin-form">
						<input type="text" name="timeout" size="6" value="' .
							Tools::getValue('timeout', Configuration::get($this->name.'_timeout')).'"/>
						<p class="clear">'.$this->l('Time in milliseconds between slide transitions (0 to disable auto advance).').'</p>
					</div>

					<label>'.$this->l('Transition speed').'</label>
					<div class="margin-form">
						<input type="text" name="speed" size="6" value="' .
							Tools::getValue('speed', Configuration::get($this->name.'_speed')).'"/>
						<p class="clear">'.$this->l('Speed of the transition in milliseconds.').'</p>
					</div>

					<label>'.$this->l('Container height').'</label>
					<div class="margin-form">
						<input type="text" name="height" size="6" value="' .
							Tools::getValue('height', Configuration::get($this->name.'_height')).'"/>
						<p class="clear">'.$this->l('Set container height in pixel (ex : \'300\').').'</p>
					</div>

					<label>'.$this->l('Container width').'</label>
					<div class="margin-form">
						<input type="text" name="width" size="6" value="' .
							Tools::getValue('width', Configuration::get($this->name.'_width')).'"/>
						<p class="clear">'.$this->l('Set container width or leave empty for auto width.').'</p>
					</div>

					<label>'.$this->l('Top & bottom margins').'</label>
					<div class="margin-form">
						<input type="text" name="margin" size="6" value="' .
							Tools::getValue('margin', Configuration::get($this->name.'_margin')).'"/>
						<p class="clear">'.$this->l('Add top and bottom space, right and left margin are set automatically').'</p>
					</div>

					<label>'.$this->l('Initial delay').'</label>
					<div class="margin-form">
						<input type="text" name="delay" size="6" value="' .
							Tools::getValue('delay', Configuration::get($this->name.'_delay')).'"/>
						<p class="clear">'.$this->l('Set an additional delay (in ms) for first transition (can be negative).').'</p>
					</div>

					<label>'.$this->l('Synchronous fade').'</label>
					<div class="margin-form">
						<input type="checkbox" name="sync" value="' .
							(Tools::getValue('sync', Configuration::get($this->name.'_sync')) ? "true" : "false").'"' .
							(Tools::getValue('sync', Configuration::get($this->name.'_sync')) == "true" ? ' checked="checked"' : '') . ' />
						<p class="clear">'.$this->l('If checked in/out transitions occur simultaneously.').'</p>
					</div>

					<label>'.$this->l('Fit images').'</label>
					<div class="margin-form">
						<input type="checkbox" name="fit" value="' .
							(Tools::getValue('fit', Configuration::get($this->name.'_fit')) ? "true" : "false").'"' .
							(Tools::getValue('fit', Configuration::get($this->name.'_fit')) == "true" ? ' checked="checked"' : '') . ' />
						<p class="clear">'.$this->l('Force slides to fit container.').'</p>
					</div>

					<label>'.$this->l('Pause on hover').'</label>
					<div class="margin-form">
						<input type="checkbox" name="pause" value="' .
							(Tools::getValue('pause', Configuration::get($this->name.'_pause')) ? "true" : "false").'"' .
							(Tools::getValue('pause', Configuration::get($this->name.'_pause')) == "true" ? ' checked="checked"' : '') . ' />
						<p class="clear">'.$this->l('Prevent slide to change when the mouse pointer is hovering it.').'</p>
					</div>

					<br />

					<input type="hidden" id="hidden_image_data" name="image_data" value="' . Configuration::get($this->name.'_images') . '"/>
					<input type="hidden" id="hidden_link_data" name="link_data" value="'   . Configuration::get($this->name.'_links') . '"/>
					<input type="hidden" id="hidden_lang_data" name="lang_data" value="'   . Configuration::get($this->name.'_langs') . '"/>


					<table cellpadding="0" cellspacing="0" class="table space'.($nbDirImages >= 2 ? ' tableDnD' : '' ).'" id="table_images" style="margin-left: 30px; width: 825px;">
					<caption style="font-weight: bold; margin-bottom: 1em;">' . $this->l('Images') . '</caption>
					<tr class="nodrag nodrop">
						<th width="60" colspan="2">' . $this->l('Position') . '</th>

						<th style="padding-left: 10px;">'. $this->l('Image') .' </th>
						<th width="270">'. $this->l('Link') .' </th>
						<th width="80">'. $this->l('Language') .' </th>
						<th width="40">'. $this->l('Enabled') .' </th>
						<th width="40">'. $this->l('Delete') .' </th>
					</tr>';

				if ($nbDirImages) {
					$i = 1;

					foreach ($confImages AS $confImage) {
						if (in_array($confImage['name'], $dirImages)) {
							echo $this->_getTableRowHTML($i, $nbDirImages, $confImage['name'], $confImage['link'], true);
							$i++;
						}
					}

					if ($nbDirImages > $nbConfImages) {
						foreach ($dirImages AS $dirImage) {
							if (!$this->_isImageInArray($dirImage, $confImages)) {
								echo $this->_getTableRowHTML($i, $nbDirImages, $dirImage);
								$i++;
							}
						}
					}
				}
				else {
					echo '<tr><td colspan="4">'.$this->l('No image in module directory').'</td></tr>';
				}

			echo '	</table>

			        <br />

			        <a href="#" id="uploadImage" style="margin-left:30px">
			             <img src="../img/admin/add.gif" alt="upload image" />' . $this->l('Add an image') . '
                    </a>
                    <img id="loading_gif" src="'. _MODULE_DIR_ . $this->name . '/ajax-loader.gif" alt="uploading..." style="position:relative; top:2px; display:none;"/>

					<br /><br />
					<center>
					<input type="submit" name="submit" value="'.$this->l('Update').'" class="button" style="font-size:1.1em; padding:5px 60px 5px 60px;" />
				    </center>
				</fieldset>
			</form>';
	}

	function hookSlideShow($params) {
		global $smarty;

		$width = Configuration::get($this->name.'_width');
		$width = ($width == '' || $width == 'auto') ? '100%' : $width . 'px';

		$smarty->assign(array('images'  => $this->_getImageArray(true),
                                        'timeout' => Configuration::get($this->name.'_timeout'),
                                        'speed'	=> Configuration::get($this->name.'_speed'),
                                        'height' 	=> Configuration::get($this->name.'_height'),
                                        'width' 	=> $width,
                                        'margin' 	=> Configuration::get($this->name.'_margin'),
                                        'delay' 	=> Configuration::get($this->name.'_delay'),
                                        'sync' 	=> Configuration::get($this->name.'_sync'),
                                        'fit' 	=> Configuration::get($this->name.'_fit'),
                                        'pause' 	=> Configuration::get($this->name.'_pause'),
                                        'this_path' => $this->_path
                    ));

		return $this->display(__FILE__, 'simpleslideshow.tpl');

	}

	public function hookHeader($params)
	{
		$id_cms = Tools::getValue('id_cms');

		if($this->_getImageArray(true) && $id_cms == 15)
		{
			Tools::addJS($this->_path.'assets/jquery.jcarousel.min.js');
			Tools::addJS($this->_path.'assets/jquery.jcarousel_start.js');
			Tools::addCSS($this->_path.'assets/jquery.jcarousel.css', 'all');
			Tools::addCSS($this->_path.'assets/jquery.jcarousel.skin.css', 'all');
		}
	}


	private function _getImageArray($lang_filter = false) {
	    global $cookie;

		$images = explode(";", Configuration::get($this->name.'_images'));
		$links 	= explode(";", Configuration::get($this->name.'_links'));
		$langs 	= $lang_filter ? explode(";", Configuration::get($this->name.'_langs')) : false;

		$tab_images = array();

		for($i = 0, $length = sizeof($images); $i < $length; $i++) {
			if ($images[$i] != "") {
				if ($lang_filter && isset($langs[$i]) && $langs[$i] != 'all' && $langs[$i] != $cookie->id_lang)
				    continue;

				$tab_images[] = array('name' 	=> $images[$i],
								      'link' 	=> isset($links[$i]) ? $links[$i] : '');
			}
		}

		return $tab_images;
	}

	private function _isImageInArray($name, $array) {
		if (!is_array($array))
			return false;

		foreach ($array as $image) {
			if (isset($image['name'])) {
				if ($image['name'] == $name)
					return true;
			}
		}

		return false;
	}

	private function _parseImageDir() {
	    $dir = _PS_MODULE_DIR_ . $this->name . '/slides/';
	    $imgs = array();
		$imgmarkup = '';

	    if ($dh = opendir($dir)) {
	        while (($file = readdir($dh)) !== false) {
	            if (!is_dir($file) && preg_match("/^[^.].*?\.(jpe?g|gif|png)$/i", $file)) {
	                array_push($imgs, $file);
	            }
	        }
	        closedir($dh);
	    } else {
	        echo 'can\'t open slide directory';
	        return false;
	    }

		return $imgs;
	}

	private function _getTableRowHTML($i, $nbImages, $imagename, $imagelink = '', $checked = false) {
	   return '<tr id="tr_image_'. $i . '"' . ($i % 2 ? ' class="alt_row"' : '').' style="height: 42px;">
				<td class="positions" width="30" style="padding-left: 20px;">' . $i . '</td>
				<td'.($nbImages >= 2 ? ' class="dragHandle"' : '') . ' id="td_image_'. $i . '" width="30">
					<a' .($i == 1 ? ' style="display: none;"' : '' ).' href="#" class="move-up"><img src="../img/admin/up.gif" alt="'.$this->l('Up').'" title="'.$this->l('Up').'" /></a><br />
					<a '.($i == $nbImages ? ' style="display: none;"' : '' ).'href="#" class="move-down"><img src="../img/admin/down.gif" alt="'.$this->l('Down').'" title="'.$this->l('Down').'" /></a>
				</td>
				<td class="imagename" style="padding-left: 10px;">'. $imagename .'</td>
				<td class="imagelink">
					<input type="text" style="width: 250px" name="image_link_' . $i . '" value="' . $imagelink .'" />
				</td>
				<td class="imagelang">' . $this->_getLanguageSelectHTML($i) . '</td>
				<td class="checkbox_image_enabled" style="padding-left: 25px;" width="40">
					<input type="checkbox" name="image_enable_' . $i . '"' . ($checked ? ' checked="checked"' : '') . ' />
				</td>
				<td class="delete_image" style="padding-left: 25px;" width="40">
					<img src="../img/admin/delete.gif" alt="'.$this->l('Delete').'" title="'.$this->l('Delete').'" style="cursor:pointer;" />
				</td>
			</tr>';
	}

	private function _getLanguageSelectHTML($i) {
        $languages = Language::getLanguages();
        $langs 	   = explode(";", Configuration::get($this->name.'_langs'));

        $html =  '<select name="language_' . $i . '" style="width:55px">';
        $html .= '<option value="all">ALL</option>';

		foreach ($languages as $language)
		{
			 $html .= '<option value="' . $language['id_lang'] . '"' . (isset($langs[$i-1]) && $langs[$i-1] == $language['id_lang'] ? 'selected="selected"' : '') . '>' . strtoupper($language['iso_code']) . '</option>';
		     //style="background:url(' . _PS_IMG_.'l/'.$language['id_lang'] . '.jpg) no-repeat 0 0 scroll white; width:16px; height:11px;"
		}

        return $html .= '</select>';
	}
}

?>

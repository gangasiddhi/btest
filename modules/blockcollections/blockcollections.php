<?php
/** Module Collections block **/
class blockCollections extends Module
{
	// protected vars with default values
 	protected $_maxImageSize = 307200;
	protected $_xml;
	protected $_colCount = 6;

	function __construct()
 	{
 	 	$this->name = 'blockcollections';
		$this->tab = 'front_office_features';
 	 	$this->version = '1.2';
		/* The parent construct is required for translations */
	 	parent::__construct();
		$this->page = basename(__FILE__, '.php');
	 	$this->displayName = $this->l('Collections Block');
	 	$this->description = $this->l('Displays a list of collections each of which open up the complete under each collection');

		// Initialize values
		$this->_xml = $this->_getXml(1);
		$this->_colCount = Configuration::get('COLLECTIONS_COUNT');
 	}

    function install()
	{
		if (!parent::install() OR !$this->registerHook('showroomTop') OR !$this->registerHook('showroomPopup') OR !$this->registerHook('header') OR !$this->registerHook('productFooter'))
			return false;
		return true;
	}

	public function uninstall() {
		return (parent::uninstall());
	}
	
	function putContent($xml_data, $key, $field)
	{
		$field = htmlspecialchars($field);
		if (!$field)
			return 0;
		return ("\n".'		<'.$key.'>'.$field.'</'.$key.'>');
	}

	function getContent()
	{
		global $output;

		// Display module name
		$this->_html = '<h2>'.$this->displayName.' '.$this->version.'</h2>';

		if (Tools::isSubmit('submitCollections'))
		{
			for ($count = 1; $count <= $this->_colCount; $count++)
			{
				// update the configuration xml, on submit
				if (Tools::getValue('update_collection'.$count))
				{
					if(!Tools::getValue('collection_order'.$count))
						$this->_html .= $this->displayError($this->l('Collection '.$count.': Display order is mandatory'));

					// Generate new XML data
					$newXml = '<?xml version=\'1.0\' encoding=\'utf-8\' ?>'."\n";
					$newXml .= '<collection>';
					$newXml .= $this->putContent($newXml, 'collection_order', $_POST['collection_order'.$count]);
					if(Tools::getValue('collection_name'.$count))
						$newXml .= $this->putContent($newXml, 'collection_name', $_POST['collection_name'.$count]);
					$newXml .= "\n";
	//				echo $_POST['collection_name'.$count];
	//				echo $newXml;

					$i = 0;
					foreach ($_POST['item'.$count] as $item)
					{
						$newXml .= '	<item>';
						foreach ($item AS $key => $field)
						{
							if ($line = $this->putContent($newXml, $key, $field))
								$newXml .= $line;
						}

						// Upload the image
						if (isset($_FILES['item'.$count.'_'.$i.'_img']) AND isset($_FILES['item'.$count.'_'.$i.'_img']['tmp_name']) AND !empty($_FILES['item'.$count.'_'.$i.'_img']['tmp_name']))
						{
							Configuration::set('PS_IMAGE_GENERATION_METHOD', 1);
							if ($error = checkImage($_FILES['item'.$count.'_'.$i.'_img'], $this->_maxImageSize))
							{
								$this->_html .= 'Collection '.$count.': '. $error;
							}
							elseif (!move_uploaded_file($_FILES['item'.$count.'_'.$i.'_img']['tmp_name'], dirname(__FILE__).'/slides'.$count.'/slide_0'.$i.'.jpg'))
							{
								// Display configuration form and exit
								$this->_html .= $this->displayError($this->l('Collection '.$count.': Unable to upload the image.'));
								$this->_displayForm();
								return $this->_html;
							}
							/*elseif (!$tmpName = tempnam(_PS_TMP_IMG_DIR_, 'PS') OR !move_uploaded_file($_FILES['item'.$count.'_'.$i.'_img']['tmp_name'], $tmpName))
								return false;
							elseif (!imageResize($tmpName, dirname(__FILE__).'/slides'.$count.'/slide_0'.$i.'.jpg'))
								$this->_html .= $this->displayError($this->l('An error occurred during the image upload.'));
							unlink($tmpName);*/
						}

						if($i==0)
						{
							if ($line = $this->putContent($newXml, 'thumbnail', 'slides'.$count.'/slide_0'.$i.'.jpg'))
								$newXml .= $line;
						}
						else
						{
							if ($line = $this->putContent($newXml, 'image', 'slides'.$count.'/slide_0'.$i.'.jpg'))
								$newXml .= $line;
						}
						$newXml .= "\n".'	</item>'."\n";
						$i++;
					}
					$newXml .= '</collection>'."\n";

					/* write it into the configuration xml file */
					if ($fd = @fopen(dirname(__FILE__).'/collection'.$count.'.xml', 'w'))
					{
						if (!@fwrite($fd, $newXml))
							$this->_html .= $this->displayError($this->l('Collection '.$count.': Unable to write to the editor file.'));
						elseif (!@fclose($fd))
							$this->_html .= $this->displayError($this->l('Collection '.$count.': Can\'t close the editor file.'));
						else
							$this->_html .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="'.$this->l('Confirmation').'" />'.$this->l('Collection '.$count.' Updated').'</div>';
					}
					else
						$this->_html .= $this->displayError($this->l('Collection '.$count.': Unable to update the editor file.<br />Please check the editor file\'s writing permissions.'));
				}
			}
		}
		elseif(Tools::isSubmit('submitCollectionsCount') AND ($collections_count = Tools::getValue('collections_count')))
		{
			Configuration::updateValue('COLLECTIONS_COUNT', $collections_count);
			$this->_html .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="" title="" />'.$this->l('Collection Count updated').'</div>';
		}

		// Display the configuration form
		$this->_displayForm();
		return $this->_html;
	}

	private function _getXml($count)
	{
		if (file_exists(dirname(__FILE__).'/collection'.$count.'.xml'))
		{
			if ($xml = @simplexml_load_file(dirname(__FILE__).'/collection'.$count.'.xml'))
				return $xml;
		}
		return false;
	}

	public function _getFormItem($id, $count, $last)
	{
		//echo "count=".$count;
		$i = $id;
		$this->_xml = $this->_getXml($count);

		$output = '
			<div class="item'.$count.'" id="item'.$count.$i.'">
				<h3>'.$this->l('Item #').$count.$i.'</h3>
				<input type="hidden" name="item_'.$count.$i.'_item" value="" />';
		$output .= '
				<label>'.$this->l('Slide image').'</label>
				<div class="margin-form">
					<div><img src="'.$this->_mediaServerPath.$this->_path.'slides'.$count.'/slide_0'.$i.'.jpg" alt="" title="" style="width:250px; height:auto;" /></div>
					<input type="file" name="item'.$count.'_'.$i.'_img" />
					<p style="clear: both"></p>
				</div>';
		$output .= '
				<label>'.$this->l('Slide URL').'</label>
				<div class="margin-form" style="padding-left:0">
					<input type="text" name="item'.$count.'['.$i.'][url]" size="64" value="'.$this->_xml->item[$i]->url.'" />
					<p style="clear: both"></p>
				</div>';
		$output .= '
				<label style="display:none">'.$this->l('Slide description').'</label>
				<div class="margin-form" style="padding-left:0; display:none">
					<input type="text" name="item'.$count.'['.$i.'][desc]" size="64" value="'.$this->_xml->item[$i]->desc.'" />
					<p style="clear: both"></p>
				</div>';
		$output .= '
				<div class="clear pspace"></div>
				'.($i >= 0 ? '<a href="javascript:{}" onclick="removeDiv(\'item'.$count.$i.'\')" style="color:#EA2E30"><img src="'._PS_ADMIN_IMG_.'delete.gif" alt="'.$this->l('delete').'" />'.$this->l('Delete this item').'</a>' : '').'
			<hr/></div>';
		return $output;
	}

	private function _displayForm()
	{
		//$xml = false;

		$this->_html .= '
		<script type="text/javascript">
		function removeDiv(id)
		{
			$("#"+id).fadeOut("slow");
			$("#"+id).remove();
		}
		function cloneIt(cloneId, count, id) {
			var currentDiv = $(".item"+count+":last");
			var count = parseInt(count) ;
			var nextId = parseInt(id) ;
			$.get("'._MODULE_DIR_.$this->name.'/ajax.php?id="+nextId+"&count="+count, function(data) {
				$("#items"+count).append(data);
			});
			$("#"+cloneId).remove();
		}
		</script>';

		// Collections count setting form
		$this->_html .= '
		<form action="'.$_SERVER['REQUEST_URI'].'" method="post">
			<fieldset>
				<legend><img src="../img/admin/cog.gif" alt="" class="middle" />'.$this->l('Settings').'</legend>
				<label>'.$this->l('Total number of Collections (Not more than 6)').'</label>
				<div class="margin-form">
					<input type="text" name="collections_count" value="'.Tools::getValue('collections_count', Configuration::get('COLLECTIONS_COUNT')).'" />
					<input type="submit" name="submitCollectionsCount" value="'.$this->l('Update Total').'" class="button" />
				</div>
			</fieldset>
			<div class="margin-form clear">
				<div class="clear pspace"></div>
			</div>
		</form>';

		// Collections details form
		$this->_html .= '<form method="post" action="'.$_SERVER['REQUEST_URI'].'" enctype="multipart/form-data">';

		for ($count = 1; $count <= $this->_colCount; $count++)
		{
			/* xml loading */
			$xml_vars[$count] = "xml_".$count;
			if ((${$xml_vars[$count]} = $this->_getXml($count)) == false)
			{
				$this->_html .= $this->displayError($this->l('Your collection'.$count.' file is empty.'));
				continue;
			}

			$this->_html .= '<fieldset class="width6">
				<legend><img src="'.$this->_mediaServerPath.$this->_path.'logo.gif" alt="" title="" /> '.$this->displayName.' '.$count.'</legend>

				<div style=" margin: 0 0 20px;">
					<label>'.$this->l('Display order').'</label>
					<input type="text" name="collection_order'.$count.'" value="'.${$xml_vars[$count]}->collection_order.'"/>
				</div>
				<div>
					<label>'.$this->l('Collection Name').'</label>
					<input type="text" name="collection_name'.$count.'" size="45" value="'.${$xml_vars[$count]}->collection_name.'" />
				</div>
				<div id="items'.$count.'">';

			$i = 0;
			foreach (${$xml_vars[$count]}->item as $item)
			{
				if($i==0)
				{
					$last = ($i == (count(${$xml_vars[$count]}->thumbnail)-1) ? true : false);
				}
				else
				{
					$last = ($i == (count(${$xml_vars[$count]}->image)-1) ? true : false);
				}
				$this->_html .= $this->_getFormItem($i, $count, $last);
				$i++;
			}

			$this->_html .= '
				</div>
				<input type="hidden" name="update_collection'.$count.'" value="1"/>
				<a id="clone'.$count.$i.'" href="javascript:cloneIt(\'clone'.$count.$i.'\','.$count.','.$i.')" style="color:#488E41"><img src="'._PS_ADMIN_IMG_.'add.gif" alt="'.$this->l('add').'" /><b>'.$this->l('Add a new item').'</b></a>
			</fieldset>
			<div class="margin-form clear">
				<div class="clear pspace"></div>
			</div>';
		}

		$this->_html .= '
			<div class="margin-form clear">
				<div class="clear pspace"></div>
				<div class="margin-form">
					<input type="submit" name="submitCollections" value="'.$this->l('Save').'" class="button" />
				</div>
			</div>
		</form>';
	}

	public function hookHeader($params)
	{
		if(strpos($_SERVER['PHP_SELF'], 'lookbook') !== false)
		{
			Tools::addCSS($this->_path.'css/blockcollections.css');
			Tools::addJS(array(
				$this->_path.'js/blockcollections.js'
			));
		}
		if(strpos($_SERVER['PHP_SELF'], 'product') !== false)
		{
			Tools::addCSS($this->_path.'css/blockcollectionsproduct.css');
		}
	}

	function hookShowroomTop($params)
	{
		global $smarty;
		$xml_thumbnail = array();
		$xmlDetails = array();
		$collectionDetail = array();
		for($count = 1; $count <= $this->_colCount; $count++)
		{
			if (file_exists(dirname(__FILE__).'/collection'.$count.'.xml'))
			{
				$xmlfile = dirname(__FILE__).'/collection'.$count.'.xml';
				$xmlparser = xml_parser_create();
				$fp = fopen($xmlfile, 'r');
				$xmldata = fread($fp, filesize($xmlfile));
				xml_parse_into_struct($xmlparser,$xmldata,$values[$count]);
				xml_parser_free($xmlparser);
			}
			$xmlDetails[$count] = $this->_getXml($count);
		}
		//echo "<pre>";print_r($values);echo "</pre>";

		foreach($values as $key => $val)
		{
			foreach($val as $value)
			{
				if($value['type'] == 'complete')
				{
					if($value['tag'] == 'COLLECTION_ORDER')
					{
						$display_order = $value['value'];
						$xml_thumbnail[$display_order]['collection_count'] = $key;
						$xml_thumbnail[$display_order]['collection_order'] = $display_order;
					}
					if(isset($display_order))
					{
						if($value['tag'] == 'COLLECTION_NAME')
							$xml_thumbnail[$display_order]['collection_name'] =  $value['value'];
						elseif($value['tag'] == 'THUMBNAIL')
							$xml_thumbnail[$display_order]['thumbnail'] =  $value['value'];
					}
				}
			}
		}
		ksort($xml_thumbnail);
		//echo "<br/>";
		#echo "<pre>";print_r($xml_thumbnail);echo "</pre>";
		#echo "<pre>";print_r(($xmlDetails));echo "</pre>";

		$smarty->assign(array(
			'xml' => $xml_thumbnail,
			'xmlDetails' => $xmlDetails,
			'this_path' => $this->_path
		));
		return $this->display(__FILE__, 'blockcollections.tpl');
	}

	function hookProductFooter($params)
	{
		global $smarty;
		$xml_thumbnail = array();

		for($count = 1; $count <= $this->_colCount; $count++)
		{
			if (file_exists(dirname(__FILE__).'/collection'.$count.'.xml'))
			{
				$xmlfile = dirname(__FILE__).'/collection'.$count.'.xml';
				$xmlparser = xml_parser_create();
				$fp = fopen($xmlfile, 'r');
				$xmldata = fread($fp, filesize($xmlfile));
				xml_parse_into_struct($xmlparser,$xmldata,$values[$count]);
				xml_parser_free($xmlparser);
			}
		}
		//echo "<pre>";print_r($values);echo "</pre>";

		foreach($values as $key => $val)
		{
			foreach($val as $value)
			{
				if($value['type'] == 'complete')
				{
					if($value['tag'] == 'COLLECTION_ORDER')
					{
						$display_order = $value['value'];
						$xml_thumbnail[$display_order]['collection_count'] = $key;
					}
					if(isset($display_order))
					{
						if($value['tag'] == 'COLLECTION_NAME')
							$xml_thumbnail[$display_order]['collection_name'] =  $value['value'];
						elseif($value['tag'] == 'THUMBNAIL')
							$xml_thumbnail[$display_order]['thumbnail'] =  $value['value'];
					}
				}
			}
		}
		ksort($xml_thumbnail);
		//echo "<br/>";
		//echo "<pre>";print_r($xml_thumbnail);echo "</pre>";

		$smarty->assign(array(
			'xml' => $xml_thumbnail,
			'this_path' => $this->_path
		));
		return $this->display(__FILE__, 'blockcollectionsproduct.tpl');
	}

	function hookShowroomPopup($countvalue)
	{
		if (!$this->active)
			return;

		if ($xml = $this->_getXml($countvalue))
		{
			global $smarty;
			$smarty->assign(array(
				'xml' => $xml,
				'this_path' => $this->_path,
				'base_dir' => 'http://'.Tools::getHttpHost(false, true).__PS_BASE_URI__
			));
			return $this->display(__FILE__, 'slides.tpl');
		}
	}
}
?>

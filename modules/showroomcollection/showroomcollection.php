<?php
/** Module JQuery Pisces slider **/
class showroomCollection extends Module
{
	/** @var max image size */
 	protected $maxImageSize = 307200;
	protected $_xml;
 	function __construct()
 	{
 	 	$this->name = 'showroomcollection';
		$this->tab = 'front_office_features';
 	 	$this->version = '1.2';
		/* The parent construct is required for translations */
	 	parent::__construct();
		$this->page = basename(__FILE__, '.php');
	 	$this->displayName = $this->l('Showroom Collection');
	 	$this->description = $this->l('Slide the images with choosen languages');
		$this->_xml = $this->_getXml(2);
 	}
    function install()
    {
        if (!parent::install() OR !$this->registerHook('showroomTop') OR !$this->registerHook('showroomPopup') OR !$this->registerHook('header'))
            return false;
        return true;
    }
	
	public function uninstall() 
	{
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
        global $cookie,$output;
        /* Languages preliminaries */
        $defaultLanguage = intval(Configuration::get('PS_LANG_DEFAULT'));
        $languages = Language::getLanguages();
        $iso = Language::getIsoById($defaultLanguage);
       // $isoUser = Language::getIsoById(intval($cookie->id_lang));

 	 	/* display the module name */
 	 	$this->_html = '<h2>'.$this->displayName.' '.$this->version.'</h2>';
 	 	/* update the editorial xml */
 	 	if (isset($_POST['submitUpdate1']) || isset($_POST['submitUpdate2']) || isset($_POST['submitUpdate3']) || isset($_POST['submitUpdate4']) || isset($_POST['submitUpdate5']) || isset($_POST['submitUpdate6']))
 	 	{

			if (isset($_POST['submitUpdate1']) )
			{$count = 1; if(!Tools::getValue("ordercollection1")) $this->_html .= $this->displayError($this->l('Display order for collection 1 is mandatory'));}
			elseif (isset($_POST['submitUpdate2']) )
			{$count = 2; if(!Tools::getValue("ordercollection2")) $this->_html .= $this->displayError($this->l('Display order for collection 2 is mandatory'));}
			elseif (isset($_POST['submitUpdate3']) )
			{$count = 3; if(!Tools::getValue("ordercollection3")) $this->_html .= $this->displayError($this->l('Display order for collection 3 is mandatory'));}
			elseif (isset($_POST['submitUpdate4']) )
			{$count = 4; if(!Tools::getValue("ordercollection4")) $this->_html .= $this->displayError($this->l('Display order for collection 4 is mandatory'));}
			elseif (isset($_POST['submitUpdate5']) )
			{$count = 5; if(!Tools::getValue("ordercollection5")) $this->_html .= $this->displayError($this->l('Display order for collection 5 is mandatory'));}
			elseif (isset($_POST['submitUpdate6']) )
			{$count = 6; if(!Tools::getValue("ordercollection6")) $this->_html .= $this->displayError($this->l('Display order for collection 6 is mandatory'));}

			// Generate new XML data
 	 	 	$newXml = '<?xml version=\'1.0\' encoding=\'utf-8\' ?>'."\n";
			$newXml .= '<links>'."\n";
			$i = 0;
			if(isset($_POST['ordercollection'.$count]))
				$newXml .= $this->putContent($newXml,'ordercollection',$_POST['ordercollection'.$count]);
			$newXml .= "\n";
			if(isset($_POST['col_name'.$count]))
				$newXml .= $this->putContent($newXml,'collection_name',$_POST['col_name'.$count]);
			$newXml .= "\n";
//			echo $_POST['col_name'.$count];
//			ECHO $newXml;EXIT;
			foreach ($_POST['link'.$count] as $link)
			{
				/*if($i==0){
					$newXml .= '	<thumb>';
				}
				else{
					$newXml .= '	<pop>';
				}*/
				$newXml .= '	<link>';
				foreach ($link AS $key => $field)
				{
					if ($line = $this->putContent($newXml, $key, $field))
						$newXml .= $line;
				}
				/* upload the image */
				if (isset($_FILES['link'.$count.'_'.$i.'_img']) AND isset($_FILES['link'.$count.'_'.$i.'_img']['tmp_name']) AND !empty($_FILES['link'.$count.'_'.$i.'_img']['tmp_name']))
				{
					Configuration::set('PS_IMAGE_GENERATION_METHOD', 1);
					if ($error = checkImage($_FILES['link'.$count.'_'.$i.'_img'], $this->maxImageSize))
						$this->_html .= $error;
					elseif (!move_uploaded_file($_FILES['link'.$count.'_'.$i.'_img']['tmp_name'], dirname(__FILE__).'/slides'.$count.'/slide_0'.$i.'.jpg'))
						return false;
					/*elseif (!$tmpName = tempnam(_PS_TMP_IMG_DIR_, 'PS') OR !move_uploaded_file($_FILES['link'.$count.'_'.$i.'_img']['tmp_name'], $tmpName))
						return false;
					elseif (!imageResize($tmpName, dirname(__FILE__).'/slides'.$count.'/slide_0'.$i.'.jpg'))
						$this->_html .= $this->displayError($this->l('An error occurred during the image upload.'));
					unlink($tmpName);*/
				}
				if($i==0){
					if ($line = $this->putContent($newXml, 'thumbnail', 'slides'.$count.'/slide_0'.$i.'.jpg'))
						$newXml .= $line;
				}
				else {
					if ($line = $this->putContent($newXml, 'popup', 'slides'.$count.'/slide_0'.$i.'.jpg'))
						$newXml .= $line;
				}
				/*if($i==0){
					$newXml .= "\n".'	</thumb>'."\n";
				}
				else{
					$newXml .= "\n".'	</pop>'."\n";
				}*/
				$newXml .= "\n".'	</link>'."\n";
				$i++;
			}
			$newXml .= '</links>'."\n";

			/* write it into the editorial xml file */
			if ($fd = @fopen(dirname(__FILE__).'/links'.$count.'.xml', 'w'))
			{
				if (!@fwrite($fd, $newXml))
					$this->_html .= $this->displayError($this->l('Unable to write to the editor file.'));
				if (!@fclose($fd))
					$this->_html .= $this->displayError($this->l('Can\'t close the editor file.'));
			}
			else
				$this->_html .= $this->displayError($this->l('Unable to update the editor file.<br />Please check the editor file\'s writing permissions.'));


			/* forming order.xml */
			if (file_exists(dirname(__FILE__).'/orders.xml'))
		  	if (!$orders = @simplexml_load_file(dirname(__FILE__).'/orders.xml'))
		  		$this->_html .= $this->displayError($this->l('Your orders file is empty.'));
			else{
				$xml = simplexml_load_file(dirname(__FILE__).'/orders.xml');
				$orderxml = '<?xml version=\'1.0\' encoding=\'utf-8\' ?>'."\n";
				$orderxml .= "<orders>"."\n";
				foreach($xml->children() as $child)
				{
				  // echo $child->getName() . ": " . $child . "<br />";
					if($child->getName() == ("ordercollection".$count))
					{
						$child = Tools::getValue("ordercollection".$count);//echo $child;
						$orderxml .= "<ordercollection".$count.">".$child."</ordercollection".$count.">"."\n";
					}
					else {
						$orderxml .="<".$child->getName().">".$child."</".$child->getName().">"."\n";
					}
				}
				$orderxml .= "</orders>";
				//echo $orderxml;
				if ($fd = @fopen(dirname(__FILE__).'/orders.xml', 'w'))
				{
					if (!@fwrite($fd, $orderxml))
						$this->_html .= $this->displayError($this->l('Unable to write to the editor file.'));
					if (!@fclose($fd))
						$this->_html .= $this->displayError($this->l('Can\'t close the editor file.'));
				}
				else
					$this->_html .= $this->displayError($this->l('Unable to update the editor file.<br />Please check the editor file\'s writing permissions.'));
			}

			/*$orderXml = '<?xml version=\'1.0\' encoding=\'utf-8\' ?>'."\n";
			$orderXml .= '<orders>'."\n";
			$i = 0;
			foreach ($_POST['ordercollection'.$count] as $orderkey => $ordervalue)
			{
				$orderXml .= '<ordercollection'.$count.'>'.$ordervalue.'</ordercollection'.$count.'>'."\n";
			}
			$orderXml .= '</orders>'."\n";
			/* write it into the editorial xml file */ /*

			if ($fd = @fopen(dirname(__FILE__).'/orders'.$count.'.xml', 'w'))
			{
				if (!@fwrite($fd, $orderXml))
					$this->_html .= $this->displayError($this->l('Unable to write to the editor file.'));
				if (!@fclose($fd))
					$this->_html .= $this->displayError($this->l('Can\'t close the editor file.'));
			}
			else
				$this->_html .= $this->displayError($this->l('Unable to update the editor file.<br />Please check the editor file\'s writing permissions.'));*/
 	 	}
		if (Tools::isSubmit('submitUpdate'))
		{
				$output .= '<div class="conf confirm"><img src="../img/admin/ok.gif" alt="'.$this->l('Confirmation').'" />'.$this->l('Settings updated').'</div>';
		}
 		/* display the editorial's form */
 	 	$this->_displayForm();
 	 	return $this->_html;
 	}
	static private function getXmlFilename($count)
	{
		return 'links'.$count.'.xml';
	}
	private function _getXml($count)
	{
		if (file_exists(dirname(__FILE__).'/'.$this->getXmlFilename($count)))
		{
			if ($xml = @simplexml_load_file(dirname(__FILE__).'/'.$this->getXmlFilename($count)))
				return $xml;
		}
		return false;
	}
	public function _getFormItem($id, $count, $last)
	{
		//$count = intval($count_with_id[0]);
		//$i = intval($count_with_id[1]);
		//echo "count=".$count;
		$i = $id;
		global $cookie;
		$this->_xml = $this->_getXml($count);

		//echo $order->_xml->orders->ordercollection1;
		//$isoUser = Language::getIsoById(intval($cookie->id_lang));

		$output = '
			<div class="item'.$count.'" id="item'.$count.$i.'">
				<h3>'.$this->l('Item #').$count.$i.'</h3>
				<input type="hidden" name="item_'.$count.$i.'_item" value="" />';
		$output .= '
				<label>'.$this->l('Slide image').'</label>
				<div class="margin-form">
					<div><img src="'.$this->_mediaServerPath.$this->_path.'slides'.$count.'/slide_0'.$i.'.jpg" alt="" title="" style="width:250px; height:auto;" /></div>
					<input type="file" name="link'.$count.'_'.$i.'_img" />
					<p style="clear: both"></p>
				</div>';
		$output .= '
				<label>'.$this->l('Slide URL').'</label>
				<div class="margin-form" style="padding-left:0">
					<input type="text" name="link'.$count.'['.$i.'][url]" size="64" value="'.$this->_xml->link[$i]->url.'" />
					<p style="clear: both"></p>
				</div>';
		$output .= '
				<label>'.$this->l('Slide description').'</label>
				<div class="margin-form" style="padding-left:0">
					<input type="text" name="link'.$count.'['.$i.'][desc]" size="64" value="'.$this->_xml->link[$i]->desc.'" />
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
        global $cookie;
        /* Languages preliminaries */
        $defaultLanguage = intval(Configuration::get('PS_LANG_DEFAULT'));
        $languages = Language::getLanguages();
        $iso = Language::getIsoById($defaultLanguage);
        //$isoUser = Language::getIsoById(intval($cookie->id_lang));
 	 	/* xml loading */
 	 	$xml = false;
 	 	if (file_exists(dirname(__FILE__).'/links1.xml'))
		  	if (!$xml1 = @simplexml_load_file(dirname(__FILE__).'/links1.xml'))
		  		$this->_html .= $this->displayError($this->l('Your links1 file is empty.'));
		if (file_exists(dirname(__FILE__).'/links2.xml'))
			if (!$xml2 = @simplexml_load_file(dirname(__FILE__).'/links2.xml'))
				$this->_html .= $this->displayError($this->l('Your links2 file is empty.'));
		if (file_exists(dirname(__FILE__).'/links3.xml'))
		  	if (!$xml3 = @simplexml_load_file(dirname(__FILE__).'/links3.xml'))
		  		$this->_html .= $this->displayError($this->l('Your links3 file is empty.'));
		if (file_exists(dirname(__FILE__).'/links4.xml'))
		  	if (!$xml4 = @simplexml_load_file(dirname(__FILE__).'/links4.xml'))
		  		$this->_html .= $this->displayError($this->l('Your links4 file is empty.'));
		if (file_exists(dirname(__FILE__).'/links5.xml'))
		  	if (!$xml5 = @simplexml_load_file(dirname(__FILE__).'/links5.xml'))
		  		$this->_html .= $this->displayError($this->l('Your links5 file is empty.'));
		if (file_exists(dirname(__FILE__).'/links6.xml'))
		  	if (!$xml6 = @simplexml_load_file(dirname(__FILE__).'/links6.xml'))
		  		$this->_html .= $this->displayError($this->l('Your links6 file is empty.'));

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
		</script>
		<form method="post" action="'.$_SERVER['REQUEST_URI'].'" enctype="multipart/form-data">';
			$orders = @simplexml_load_file(dirname(__FILE__).'/orders.xml');
			$count = 1;
			$this->_html .= '<fieldset style="width: 800px;">
        		<legend><img src="'.$this->_path.'logo.gif" alt="" title="" /> '.$this->displayName.' '.$count.'</legend>

				<div style=" margin: 0 0 20px;"><label>'.$this->l('Display order').'</label>
				<input type="text" name="ordercollection'.$count.'" value="'.$xml1->ordercollection.'"/></div>

				<div><label>'.$this->l('Collection Name').'</label>
				<input type="text" name="col_name'.$count.'" size="45" value="'.$xml1->collection_name.'" /></div>';
				$this->_html .= '<div id="items'.$count.'">';
						$i = 0;
						//print_r($xml);
						foreach ($xml1->link as $link)
						{
							if($i==0){
							$last = ($i == (count($xml1->thumbnail)-1) ? true : false);
							}
							else{
								$last = ($i == (count($xml1->popup)-1) ? true : false);
							}
							$this->_html .= $this->_getFormItem($i, $count, $last);
							$i++;
						}
							/*if($i==0){
								foreach ($xml->thumb as $link)
								{
									$last = ($i == (count($xml->thumb)-1) ? true : false);
									$this->_html .= $this->_getFormItem($i, $last);
									$i++;
								}
							}
							else
							{
								foreach ($xml->pop as $link)
								{
									$last = ($i == (count($xml->pop)-1) ? true : false);
									$this->_html .= $this->_getFormItem($i, $last);
									$i++;
								}
							}*/

						$this->_html .= '
				</div>
				<a id="clone'.$count.$i.'" href="javascript:cloneIt(\'clone'.$count.$i.'\','.$count.','.$i.')" style="color:#488E41"><img src="'._PS_ADMIN_IMG_.'add.gif" alt="'.$this->l('add').'" /><b>'.$this->l('Add a new item').'</b></a>';
		$this->_html .= '
				</fieldset>
				<div class="margin-form clear">
					<div class="clear pspace"></div>
					<div class="margin-form">
						 <input type="submit" name="submitUpdate'.$count.'" value="'.$this->l('Save').'" class="button" />
					</div>
				</div>';


				$count = 2;
				$this->_html .= '<fieldset style="width: 800px;">
        		<legend><img src="'.$this->_path.'logo.gif" alt="" title="" /> '.$this->displayName.' '.$count.'</legend>

				<div style=" margin: 0 0 20px;"><label>'.$this->l('Display order').'</label>
				<input type="text" name="ordercollection'.$count.'" value="'.$xml2->ordercollection.'"/></div>

				<div><label>'.$this->l('Collection Name').'</label>
				<input type="text" name="col_name'.$count.'" size="45" value="'.$xml2->collection_name.'" /></div>';
				$this->_html .= '<div id="items'.$count.'">';
						$i = 0;
						//print_r($xml);
						foreach ($xml2->link as $link)
						{
							if($i==0){
							$last = ($i == (count($xml2->thumbnail)-1) ? true : false);
							}
							else{
								$last = ($i == (count($xml2->popup)-1) ? true : false);
							}
							$this->_html .= $this->_getFormItem($i,$count, $last);
							$i++;
						}

				$this->_html .= '
				</div>
				<a id="clone'.$count.$i.'" href="javascript:cloneIt(\'clone'.$count.$i.'\','.$count.','.$i.')" style="color:#488E41"><img src="'._PS_ADMIN_IMG_.'add.gif" alt="'.$this->l('add').'" /><b>'.$this->l('Add a new item').'</b></a>';
		$this->_html .= '
				</fieldset>
				<div class="margin-form clear">
					<div class="clear pspace"></div>
					<div class="margin-form">
						 <input type="submit" name="submitUpdate'.$count.'" value="'.$this->l('Save').'" class="button" />
					</div>
				</div>';


				$count = 3;
				$this->_html .= '<fieldset style="width: 800px;">
        		<legend><img src="'.$this->_path.'logo.gif" alt="" title="" /> '.$this->displayName.' '.$count.'</legend>

				<div style=" margin: 0 0 20px;"><label>'.$this->l('Display order').'</label>
				<input type="text" name="ordercollection'.$count.'" value="'.$xml3->ordercollection.'"/></div>

				<div><label>'.$this->l('Collection Name').'</label>
				<input type="text" name="col_name'.$count.'" size="45" value="'.$xml3->collection_name.'" /></div>';
				$this->_html .= '<div id="items'.$count.'">';
						$i = 0;
						//print_r($xml);
						foreach ($xml3->link as $link)
						{
							if($i==0){
							$last = ($i == (count($xml3->thumbnail)-1) ? true : false);
							}
							else{
								$last = ($i == (count($xml3->popup)-1) ? true : false);
							}
							$this->_html .= $this->_getFormItem($i,$count, $last);
							$i++;
						}

						$this->_html .= '
				</div>
				<a id="clone'.$count.$i.'" href="javascript:cloneIt(\'clone'.$count.$i.'\','.$count.','.$i.')" style="color:#488E41"><img src="'._PS_ADMIN_IMG_.'add.gif" alt="'.$this->l('add').'" /><b>'.$this->l('Add a new item').'</b></a>';
		$this->_html .= '
				</fieldset>
				<div class="margin-form clear">
					<div class="clear pspace"></div>
					<div class="margin-form">
						 <input type="submit" name="submitUpdate'.$count.'" value="'.$this->l('Save').'" class="button" />
					</div>
				</div>';


				$count = 4;
				$this->_html .= '<fieldset style="width: 800px;">
        		<legend><img src="'.$this->_path.'logo.gif" alt="" title="" /> '.$this->displayName.' '.$count.'</legend>

				<div style=" margin: 0 0 20px;"><label>'.$this->l('Display order').'</label>
				<input type="text" name="ordercollection'.$count.'" value="'.$xml4->ordercollection.'"/></div>

				<div><label>'.$this->l('Collection Name').'</label>
				<input type="text" name="col_name'.$count.'" size="45" value="'.$xml4->collection_name.'" /></div>';
				$this->_html .= '<div id="items'.$count.'">';
						$i = 0;
						//print_r($xml);
						foreach ($xml4->link as $link)
						{
							if($i==0){
							$last = ($i == (count($xml4->thumbnail)-1) ? true : false);
							}
							else{
								$last = ($i == (count($xml4->popup)-1) ? true : false);
							}
							$this->_html .= $this->_getFormItem($i,$count, $last);
							$i++;
						}

						$this->_html .= '
				</div>
				<a id="clone'.$count.$i.'" href="javascript:cloneIt(\'clone'.$count.$i.'\','.$count.','.$i.')" style="color:#488E41"><img src="'._PS_ADMIN_IMG_.'add.gif" alt="'.$this->l('add').'" /><b>'.$this->l('Add a new item').'</b></a>';
		$this->_html .= '
				</fieldset>
				<div class="margin-form clear">
					<div class="clear pspace"></div>
					<div class="margin-form">
						 <input type="submit" name="submitUpdate'.$count.'" value="'.$this->l('Save').'" class="button" />
					</div>
				</div>';


				$count = 5;
				$this->_html .= '<fieldset style="width: 800px;">
        		<legend><img src="'.$this->_path.'logo.gif" alt="" title="" /> '.$this->displayName.' '.$count.'</legend>

				<div style=" margin: 0 0 20px;"><label>'.$this->l('Display order').'</label>
				<input type="text" name="ordercollection'.$count.'" value="'.$xml5->ordercollection.'"/></div>

				<div><label>'.$this->l('Collection Name').'</label>
				<input type="text" name="col_name'.$count.'" size="45" value="'.$xml5->collection_name.'" /></div>';
				$this->_html .= '<div id="items'.$count.'">';
						$i = 0;
						//print_r($xml);
						foreach ($xml5->link as $link)
						{
							if($i==0){
							$last = ($i == (count($xml5->thumbnail)-1) ? true : false);
							}
							else{
								$last = ($i == (count($xml5->popup)-1) ? true : false);
							}
							$this->_html .= $this->_getFormItem($i,$count, $last);
							$i++;
						}

						$this->_html .= '
				</div>
				<a id="clone'.$count.$i.'" href="javascript:cloneIt(\'clone'.$count.$i.'\','.$count.','.$i.')" style="color:#488E41"><img src="'._PS_ADMIN_IMG_.'add.gif" alt="'.$this->l('add').'" /><b>'.$this->l('Add a new item').'</b></a>';
		$this->_html .= '
				</fieldset>
				<div class="margin-form clear">
					<div class="clear pspace"></div>
					<div class="margin-form">
						 <input type="submit" name="submitUpdate'.$count.'" value="'.$this->l('Save').'" class="button" />
					</div>
				</div>';


				$count = 6;
				$this->_html .= '<fieldset style="width: 800px;">
        		<legend><img src="'.$this->_path.'logo.gif" alt="" title="" /> '.$this->displayName.' '.$count.'</legend>

				<div style=" margin: 0 0 20px;"><label>'.$this->l('Display order').'</label>
				<input type="text" name="ordercollection'.$count.'" value="'.$xml6->ordercollection.'"/></div>

				<div><label>'.$this->l('Collection Name').'</label>
				<input type="text" name="col_name'.$count.'" size="45" value="'.$xml6->collection_name.'" /></div>';
				$this->_html .= '<div id="items'.$count.'">';
						$i = 0;
						//print_r($xml);
						foreach ($xml6->link as $link)
						{
							if($i==0){
							$last = ($i == (count($xml6->thumbnail)-1) ? true : false);
							}
							else{
								$last = ($i == (count($xml6->popup)-1) ? true : false);
							}
							$this->_html .= $this->_getFormItem($i,$count, $last);
							$i++;
						}

						$this->_html .= '
				</div>
				<a id="clone'.$count.$i.'" href="javascript:cloneIt(\'clone'.$count.$i.'\','.$count.','.$i.')" style="color:#488E41"><img src="'._PS_ADMIN_IMG_.'add.gif" alt="'.$this->l('add').'" /><b>'.$this->l('Add a new item').'</b></a>';
		$this->_html .= '
				</fieldset>
				<div class="margin-form clear">
					<div class="clear pspace"></div>
					<div class="margin-form">
						 <input type="submit" name="submitUpdate'.$count.'" value="'.$this->l('Save').'" class="button" />
					</div>
				</div>';


			$this->_html .= '</form>';
 	}

//	function hookTop($params)
// 	{
//        global $cookie;
//        /* Languages preliminaries */
//        $defaultLanguage = intval(Configuration::get('PS_LANG_DEFAULT'));
//        $languages = Language::getLanguages();
//        $iso = Language::getIsoById($defaultLanguage);
//        $isoUser = Language::getIsoById(intval($cookie->id_lang));
// 	 	if (file_exists(dirname(__FILE__).'/links.xml'))
// 	 		if ($xml = simplexml_load_file(dirname(__FILE__).'/links.xml'))
// 	 		{
// 	 		 	global $cookie, $smarty;
//				$smarty->assign(array(
//					'xml' => $xml,
//					'this_path' => $this->_path
//				));
//				return $this->display(__FILE__, 'piscesslider.tpl');
//			}
//		return false;
// 	}

	public function hookHeader($params)
	{
		if(strpos($_SERVER['PHP_SELF'], 'lookbook') !== false)
		{
			Tools::addCSS($this->_path.'css/collections.css');
			Tools::addCSS($this->_path.'css/jquery.jcarousel.css');
			Tools::addCSS($this->_path.'css/jquery.jcarousel.collection.css');
			Tools::addJS(array(
				$this->_path.'js/jquery.jcarousel.collection.js'
				,$this->_path.'js/jquery.jcarousel_startcollection.js'
			));
		}
	}

	function hookShowroomTop($params)
 	{
		global $cookie, $smarty;

        /* Languages preliminaries */
		$xml_thumbnail = array();
        $defaultLanguage = intval(Configuration::get('PS_LANG_DEFAULT'));
        $languages = Language::getLanguages();
        $iso = Language::getIsoById($defaultLanguage);
        //$isoUser = Language::getIsoById(intval($cookie->id_lang));

		for($count=1; $count<=2; $count++)
		{
			if (file_exists(dirname(__FILE__).'/links'.$count.'.xml'))
			{
				$xmlfile = dirname(__FILE__).'/links'.$count.'.xml';
				$xmlparser = xml_parser_create();
				$fp = fopen($xmlfile, 'r');
				$xmldata = fread($fp, filesize($xmlfile));
				xml_parse_into_struct($xmlparser,$xmldata,$values[$count]);
				xml_parser_free($xmlparser);
			}
		}
		//echo "<pre>";print_r($values); echo "</pre>";exit;

		foreach($values as $key => $val)
		{
			foreach($val as $value)
			if($value['type'] == 'complete')
			{
				if($value['tag'] == 'ORDERCOLLECTION')
				{
					$display_order = $value['value'];
					$xml_thumbnail[$display_order]['load_file'] = $key;
				}
				if(isset($display_order))
				{
					if($value['tag'] == 'COLLECTION_NAME')
					$xml_thumbnail[$display_order]['col_name'] =  $value['value'];
					if($value['tag'] == 'THUMBNAIL')
					$xml_thumbnail[$display_order]['thumb'] =  $value['value'];
				}
			}
		}
		ksort($xml_thumbnail);
		//echo "<br/>";
	    //echo "<pre>";print_r($xml_thumbnail);echo "</pre>";
		//exit;

		$smarty->assign(array(
			'xml' => $xml_thumbnail,
			'this_path' => $this->_path
			//'base_dir' => 'http://'.Tools::getHttpHost(false, true).__PS_BASE_URI__
			//'showroom_dir' => 'http://'.Tools::getHttpHost(false, true).__PS_BASE_URI__.'modules/showroomcollection/'
		));
		return $this->display(__FILE__, 'showroomcollection.tpl');
	}

	function hookShowroomPopup($countvalue)
    {
		if (!$this->active)
			return;

		if (file_exists(dirname(__FILE__).'/links'.$countvalue.'.xml'))
 	 		if ($xml = simplexml_load_file(dirname(__FILE__).'/links'.$countvalue.'.xml'))
 	 		{
 	 		 	global $cookie, $smarty;
				$smarty->assign(array(
					'xml' => $xml,
					'this_path' => $this->_path,
					'base_dir' => 'http://'.Tools::getHttpHost(false, true).__PS_BASE_URI__
					//'showroom_dir' => 'http://'.Tools::getHttpHost(false, true).__PS_BASE_URI__.'modules/showroomcollection/'
				));
				return $this->display(__FILE__, 'popup.tpl');
			}
    }
}
?>

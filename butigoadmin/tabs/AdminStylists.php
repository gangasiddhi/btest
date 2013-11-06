<?php

/**
  * Stylists tab for admin panel, AdminStylists.php
  * @category admin
  *
  * @author PrestaShop <support@prestashop.com>
  * @copyright PrestaShop
  * @license http://www.opensource.org/licenses/osl-3.0.php Open-source licence 3.0
  * @version 1.3
  *
  */

include_once(PS_ADMIN_DIR.'/../classes/AdminTab.php');

class AdminStylists extends AdminTab
{
	protected $maxFileSize  = 2000000;
	
	public function __construct()
	{
		global $cookie;
		
	 	$this->table = 'attachment';
	 	$this->className = 'Attachment';
	 	$this->lang = true;
	 	$this->edit = true;
	 	$this->delete = true;
		$this->status= false;
		
		$this->fieldsDisplay = array(
		'id_attachment' => array('title' => $this->l('ID'), 'align' => 'center', 'width' => 25),
		'name' => array('title' => $this->l('Name')),
		'file' => array('title' => $this->l('Recommended By')),
		'file1'=> array('title' => $this->l('Pop Up')),
		'file2'=> array('title' => $this->l('Thumbnail'))   );
	
		parent::__construct();
	}

	public function postProcess()
	{
		if (Tools::isSubmit('submitAdd'.$this->table))
		{
			 $recomend  = false;
		     $popup=  false;
			 $thumbnail=   false;
			if ($id = intval(Tools::getValue('id_attachment')) AND $a = new Attachment($id))
			{
				$_POST['file'] = $a->file;
				$_POST['file1'] = $a->file1;
				$_POST['file2'] = $a->file2;
				$_POST['mime'] = $a->mime;
			}
			if (!sizeof($this->_errors))
			{

				if (isset($_FILES['file']))
				{
					if(is_uploaded_file($_FILES['file']['tmp_name']))
				   {
						if ($_FILES['file']['size'] > $this->maxFileSize)
							$this->_errors[] = $this->l('File too large, maximum size allowed:').' '.($this->maxFileSize/1000).' '.$this->l('kb');
						else
						{
							$uploadDir = dirname(__FILE__).'/../../img/stylists/recomend/'. basename($_FILES['file']['name']);
							if (!copy($_FILES['file']['tmp_name'],$uploadDir))
								$this->_errors[] = $this->l('File copy failed');
							@unlink($_FILES['file']['tmp_name']);
							/*$recommended = new Attachment();
							$recommended->file = $_FILES['file']['name'];
							$recommended->mime = $_FILES['file']['type'];
							//$_POST['name_2'] .= '.'.pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);*/
							 $_POST['file'] = $_FILES['file']['name'];
							//$_POST['mime'] = $_FILES['file']['type'];
							$recomend = true;
						}
				   }
			    }
			   
			   if (isset($_FILES['popup_file']) )
				{
				     if(is_uploaded_file($_FILES['popup_file']['tmp_name']))
					 {
						if ($_FILES['popup_file']['size'] > $this->maxFileSize)
							$this->_errors[] = $this->l('File too large, maximum size allowed:').' '.($this->maxFileSize/1000).' '.$this->l('kb');
						else
						{
							$uploadDir = dirname(__FILE__).'/../../img/stylists/popup/'. basename($_FILES['popup_file']['name']);
							if (!copy($_FILES['popup_file']['tmp_name'],$uploadDir))
								$this->_errors[] = $this->l('File copy failed');
							@unlink($_FILES['popup_file']['tmp_name']);
							/*$popup = new Attachment();
							$popup->file = $_FILES['popup_file']['name'];
							$popup->mime = $_FILES['popup_file']['type'];*/
							$popup =true;
							/*$_POST['name_2'] .= '.'.pathinfo($_FILES['popup_file']['name'], PATHINFO_EXTENSION);*/
							$_POST['file1'] = $_FILES['popup_file']['name'];
							//$_POST['mime'] = $_FILES['popup_file']['type'];
						}
					 }
			   }
			   
			   if (isset($_FILES['thumbnail_file']))
			   {
				  if(is_uploaded_file($_FILES['thumbnail_file']['tmp_name']))
				  {
						if ($_FILES['thumbnail_file']['size'] > $this->maxFileSize)
							$this->_errors[] = $this->l('File too large, maximum size allowed:').' '.($this->maxFileSize/1000).' '.$this->l('kb');
						else
						{
							$uploadDir = dirname(__FILE__).'/../../img/stylists/thumbnail/'. basename($_FILES['thumbnail_file']['name']);
							if (!copy($_FILES['thumbnail_file']['tmp_name'],$uploadDir))
								$this->_errors[] = $this->l('File copy failed');
							@unlink($_FILES['thumbnail_file']['tmp_name']);
							/*$thumbnail_file = new Attachment();
							$thumbnail_file->file = $_FILES['thumbnail_file']['name'];
							$thumbnail_file->mime = $_FILES['thumbnail_file']['type'];*/
							$thumbnail = true;
							/*$_POST['name_2'] .= '.'.pathinfo($_FILES['thumbnail_file']['name'], PATHINFO_EXTENSION);*/
							$_POST['file2'] = $_FILES['thumbnail_file']['name'];
							//$_POST['mime'] = $_FILES['thumbnail_file']['type'];
						}
				    }
			    }

			   if($recomend = true || $popup = true || $thumbnail = true)
				{
					$_POST['mime'] = 'image/jpeg';
				}

		}
			$this->validateRules();
		}
		return parent::postProcess();
	}
	
	public function displayForm($isMainTab = true)
	{
		global $currentIndex, $cookie;
		parent::displayForm();
		
		$obj = $this->loadObject(true);
		
		echo '
		<form action="'.$currentIndex.'&submitAdd'.$this->table.'=1&token='.$this->token.'" method="post" enctype="multipart/form-data" class="width2">
		'.($obj->id ? '<input type="hidden" name="id_'.$this->table.'" value="'.$obj->id.'" />' : '').'
			<fieldset><legend><img src="../img/t/AdminStylists.gif" />'.$this->l('Add Stylists').'</legend>

        <label>'.$this->l('Stylists Name:').' </label>
				<div class="margin-form">';
		foreach ($this->_languages as $language)
			echo '	<div id="cname_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $this->_defaultFormLanguage ? 'block' : 'none').'; float: left;">
						<input size="33" type="text" name="name_'.$language['id_lang'].'" value="'.htmlentities($this->getFieldValue($obj, 'name', intval($language['id_lang'])), ENT_COMPAT, 'UTF-8').'" /><sup> *</sup>
					</div>';							
		$this->displayFlags($this->_languages, $this->_defaultFormLanguage, 'cname¤cdescription¤cquote', 'cname');

		echo '	</div>
				<div class="clear">&nbsp;</div>
				<label>'.$this->l('Quote:').' </label>
				<div class="margin-form">';
		foreach ($this->_languages as $language)
			echo '	<div id="cquote_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $this->_defaultFormLanguage ? 'block' : 'none').'; float: left;">
						<textarea name="quote_'.$language['id_lang'].'">'.htmlentities($this->getFieldValue($obj, 'quote', intval($language['id_lang'])), ENT_COMPAT, 'UTF-8').'</textarea>
					</div>';
			//$this->displayFlags($this->_languages, $this->_defaultFormLanguage, 'cname¤cdescription¤cquote', 'cquote');

			echo '	</div>
				<div class="clear">&nbsp;</div>
				<label>'.$this->l('Description:').' </label>
				<div class="margin-form">';
		foreach ($this->_languages as $language)
			echo '	<div id="cdescription_'.$language['id_lang'].'" style="display: '.($language['id_lang'] == $this->_defaultFormLanguage ? 'block' : 'none').'; float: left;">
						<textarea name="description_'.$language['id_lang'].'">'.htmlentities($this->getFieldValue($obj, 'description', intval($language['id_lang'])), ENT_COMPAT, 'UTF-8').'</textarea>
					</div>';							
		$this->displayFlags($this->_languages, $this->_defaultFormLanguage, 'cname¤cdescription¤cquote', 'cdescription');
		echo '	</div>
				<div class="clear">&nbsp;</div>
				<label>'.$this->l('Recommended By:').'</label>
				<div class="margin-form">
					<p><input type="file" name="file" /></p>
					<p>'.$this->l('Upload file from your computer').'</p>
				</div>
				<div class="clear">&nbsp;</div>
				<label>'.$this->l('Pop Up:').'</label>
				<div class="margin-form">
					<p><input type="file" name="popup_file" /></p>
					<p>'.$this->l('Upload file from your computer').'</p>
				</div>
					<div class="clear">&nbsp;</div>
				<label>'.$this->l('Thumbnail:').'</label>
				<div class="margin-form">
					<p><input type="file" name="thumbnail_file" /></p>
					<p>'.$this->l('Upload file from your computer').'</p>
				</div>
				<div class="clear">&nbsp;</div>

				<div class="margin-form">
					<input type="submit" value="'.$this->l('   Save   ').'" name="submitAdd'.$this->table.'" class="button" />
				</div>
				<div class="small"><sup>*</sup> '.$this->l('Required field').'</div>
			</fieldset>
		</form>';
	}
}

?>

<?php

if (!defined('_CAN_LOAD_FILES_'))
	exit;

class BlockFeaturedImage extends Module
{
	public $featured_img;
	public $featured_imgname;
	//public $featured_imgname1;
	//public $featured_img1;

	function __construct()
	{
		$this->name = 'blockfeaturedimage';
		$this->tab = 'front_office_features';
		$this->version = 0.1;

		parent::__construct();

		$this->displayName = $this->l('Customers sayings on Home page');
		$this->description = $this->l('Displays the customers say image on home page along with Facebook block');

		$this->featured_imgname = 'Featured_custom.jpg';
		//$this->featured_imgname1 = 'Featured_custom1.jpg';
		if (!file_exists(dirname(__FILE__).'/'.$this->featured_imgname))
			$this->featured_img = _MODULE_DIR_.$this->name.'/featured.jpg';
		else
			$this->featured_img = _MODULE_DIR_.$this->name.'/'.$this->featured_imgname;

		/*if (!file_exists(dirname(__FILE__).'/'.$this->featured_imgname1))
			$this->featured_img1 = _MODULE_DIR_.$this->name.'/featured1.jpg';
		else
			$this->featured_img1 = _MODULE_DIR_.$this->name.'/'.$this->featured_imgname1;*/
	}


	function install()
	{
		if (!parent::install())
			return false;
		if (!$this->registerHook('home') OR !$this->registerHook('header') OR !$this->registerHook('landing'))
			return false;
		return true;
	}
	
	public function uninstall() {
		return (parent::uninstall());
	}

	public function postProcess()
	{
		global $currentIndex;

		$errors = false;
		if (Tools::isSubmit('submitFeaConf'))
		{
			$file = false;
			if (isset($_FILES['featured_img']) AND isset($_FILES['featured_img']['tmp_name']) AND !empty($_FILES['featured_img']['tmp_name']))
			{
				if ($error = checkImage($_FILES['featured_img'], 4000000))
					$errors .= $error;
				elseif (!move_uploaded_file($_FILES['featured_img']['tmp_name'], dirname(__FILE__).'/'.$this->featured_imgname))
					$errors .= $this->l('Error move uploaded file');

				$this->featured_img = _MODULE_DIR_.$this->name.'/'.$this->featured_imgname;
			}

		}
		/*if (Tools::isSubmit('submitFeaConff'))
		{
			$file = false;
			if (isset($_FILES['featured_img1']) AND isset($_FILES['featured_img1']['tmp_name']) AND !empty($_FILES['featured_img1']['tmp_name']))
			{
				if ($error = checkImage($_FILES['featured_img1'], 4000000))
					$errors .= $error;
				elseif (!move_uploaded_file($_FILES['featured_img1']['tmp_name'], dirname(__FILE__).'/'.$this->featured_imgname1))
					$errors .= $this->l('Error move uploaded file');

				$this->featured_img1 = _MODULE_DIR_.$this->name.'/'.$this->featured_imgname1;
			}

		}*/
		if ($errors)
			echo $this->displayError($errors);
	}

	public function getContent()
	{
		$this->postProcess();
		echo '
                 <form action="'.$_SERVER['REQUEST_URI'].'" method="post" enctype="multipart/form-data">
                 <fieldset><legend>'.$this->l('Featured Image block configuration').'</legend>
';
		if ($this->featured_img)
			echo '<img src="'.$this->_mediaServerPath.$this->featured_img.'" alt="'.$this->l('Home Featured  Image').'"  width="600px"/>';
		else
			echo $this->l('no image');
		echo '
</a>
<br/>
<br/>
<label for="featured_img">'.$this->l('Change image').'&nbsp;&nbsp;</label><input id="featured_img" type="file" name="featured_img" />
<br/>
<br class="clear"/>
<br/>
<input class="button" type="submit" name="submitFeaConf" value="'.$this->l('update').'" style="margin-left: 200px;"/>
</fieldset>
</form>
';

		/*echo '
                 <form action="'.$_SERVER['REQUEST_URI'].'" method="post" enctype="multipart/form-data">
                 <fieldset><legend>'.$this->l('Featured Image block configuration when user is logged').'</legend>
';
		if ($this->featured_img1)
			echo '<img src="'.$this->featured_img1.'" alt="'.$this->l('Home Featured  Image').'"  width="600px"/>';
		else
			echo $this->l('no image');
		echo '
</a>
<br/>
<br/>
<label for="featured_img">'.$this->l('Change image').'&nbsp;&nbsp;</label><input id="featured_img1" type="file" name="featured_img1" />
<br/>
<br class="clear"/>
<br/>
<input class="button" type="submit" name="submitFeaConff" value="'.$this->l('update').'" style="margin-left: 200px;"/>
</fieldset>
</form>
';*/
	}

	/**
	* Returns module content
	*
	* @param array $params Parameters
	* @return string Content
	*/
	function hookHome($params)
	{
		global $smarty, $cookie;

		if (! $cookie->isLogged()) {
			return '';
		}

		$smarty->assign('image', $this->featured_img);

		return $this->display(__FILE__, 'blockfeaturedimage.tpl');
	}

	public function hookLanding($params)
	{
		return $this->hookHome($params);
	}

	public function hookHeader($params)
	{
		if(strpos($_SERVER['PHP_SELF'], 'index') !== false || strpos($_SERVER['PHP_SELF'], 'landing') !== false)
			Tools::addCSS(($this->_path).'blockfeaturedimage.css', 'all');
	}
}

?>

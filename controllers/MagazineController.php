<?php

class MagazineControllerCore extends FrontController
{
    public $ssl = false;
    public $php_self = 'magazine.php';

//        public function preProcess()
//        {
//            parent::preProcess();
//
//            self::$smarty->assign(array('HOOK_STYLIST_SLIDESHOW' => Module::hookExec('stylistSlideShow')));
//        }

	public function setMedia() {
		parent::setMedia();

		Tools::addCSS(_THEME_CSS_DIR_.'magazine-new.css');

        Tools::addJS(array(
            _THEME_JS_DIR_ . 'jquery.bxslider.js',
            _THEME_JS_DIR_ . 'magazine-new.js'
		));
	}

    public function displayContent()
    {
        parent::displayContent();
		$mag = Tools::getValue('mag');
		$archive = Tools::getValue('view');

        $bigImages = array();
		if($mag == 1){
			for($i = 1; $i <= 23; $i++) {
				$bigImages[$i] = 'big/bm'.$i.'.jpg';
			}
		}
		elseif($mag == 2){
		  for($i = 1; $i <= 32; $i++) {
				$bigImages[$i] = 'big-new/bm'.$i.'.jpg';
			}
		}
		elseif($mag == 3){
			for($i = 1; $i <= 29; $i++) {
				$bigImages[$i] = 'mag-big/bm'.$i.'.jpg';
			}
		}else{
			for($i = 1; $i <= 31; $i++) {
				$bigImages[$i] = 'mag-big4/'.$i.'.jpg';
			}
		}

        //thumbnails has different mappings so we have to set them seprately
		if($mag == 1){
			$thumbnails = array(
				"1" =>  'thumb/bm1.jpg',
				"2" =>  'thumb/bm2.jpg',
				"3" =>  'thumb/bm3.jpg',
				"4" =>	'thumb/bm4.jpg',
				"10" => 'thumb/bm10.jpg',
				"11" => 'thumb/bm11.jpg',
				"12" => 'thumb/bm12.jpg',
				"13" => 'thumb/bm13.jpg',
				"14" => 'thumb/bm14.jpg',
				"19" => 'thumb/bm19.jpg',
				"22" => 'thumb/bm22.jpg',
				"23" => 'thumb/bm23.jpg'
			);
			$issue = 'SAYI 1';
			$date = '19 Nisan 2013';
		}
		elseif($mag == 2){
			$thumbnails = array(
				"1" =>  'thumb-new/1.jpg',
				"2" =>  'thumb-new/2.jpg',
				"3" =>  'thumb-new/3.jpg',
				"4" => 'thumb-new/4.jpg',
				"11" => 'thumb-new/5.jpg',
				"12" => 'thumb-new/6.jpg',
				"14" => 'thumb-new/7.jpg',
				"24" => 'thumb-new/8.jpg',
				"26" => 'thumb-new/9.jpg',
				"28" => 'thumb-new/10.jpg',
				"31" => 'thumb-new/11.jpg',
				"32" => 'thumb-new/12.jpg'
			);
			$issue = 'SAYI 2';
			$date = '31 Mayıs 2013';
		}
		elseif($mag == 3){
			$thumbnails = array(
				"1" =>  'mag-thumb/1.jpg',
				"2" =>  'mag-thumb/2.jpg',
				"4" =>  'mag-thumb/3.jpg',
				"10" => 'mag-thumb/4.jpg',
				"11" => 'mag-thumb/5.jpg',
				"12" => 'mag-thumb/6.jpg',
				"17" => 'mag-thumb/7.jpg',
				"20" => 'mag-thumb/8.jpg',
				"23" => 'mag-thumb/9.jpg',
				"27" => 'mag-thumb/10.jpg',
				"28" => 'mag-thumb/11.jpg',
				"29" => 'mag-thumb/12.jpg'
			);
			$issue = 'SAYI 3';
			$date = '22 Temmuz 2013';
		}
		else{
			$thumbnails = array(
				"1" =>  'mag-thumb4/1.jpg',
				"2" =>  'mag-thumb4/2.jpg',
				"3" =>  'mag-thumb4/3.jpg',
				"4" =>	'mag-thumb4/4.jpg',
				"10" => 'mag-thumb4/5.jpg',
				"11" => 'mag-thumb4/6.jpg',
				"15" => 'mag-thumb4/7.jpg',
				"21" => 'mag-thumb4/8.jpg',
				"25" => 'mag-thumb4/9.jpg',
				"29" => 'mag-thumb4/10.jpg',
				"30" => 'mag-thumb4/11.jpg',
				"31" => 'mag-thumb4/12.jpg'
			);
			$issue = 'SAYI 4';
			$date = '31 Ekim 2013';
		}

		$archivethumbnails = array(
			"1" => 'cover-thumb/cover-thumb-1.jpg',
			"2" => 'cover-thumb/cover-thumb-2.jpg',
			"3" => 'cover-thumb/cover-thumb-3.jpg',
			"4" => 'cover-images/cover-4.jpg'
		);




        self::$smarty->assign("bigImages", $bigImages);
        self::$smarty->assign("thumbnails", $thumbnails);
		self::$smarty->assign("archivethumbnails", $archivethumbnails);
		self::$smarty->assign("archiveView", $archive);
		self::$smarty->assign("issue", $issue);
		self::$smarty->assign("date", $date);

        self::$smarty->display(_PS_THEME_DIR_.'magazine.tpl');
    }
}
?>

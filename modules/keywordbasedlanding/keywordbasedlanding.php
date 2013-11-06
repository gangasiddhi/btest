<?php

if (! defined('_CAN_LOAD_FILES_')) {
    exit;
}

class KeywordBasedLanding extends Module {
    private $data = array(
        'shoes' => array(
            'default' => array(
                'headline' => 'IVANA SERT İMZALI AYAKKABI MODELLERİ',
                'subheadline' => 'SON TREND AYAKKABI MODELLERİ, SANA ÖZEL STİL DANIŞMANLIĞI İLE SADECE BUTİGO\'DA.',
                'image' => 'keywordbasedlanding/ayakkabi.jpg'
            ),
            'heel-shoes' => array(
                'headline' => 'IVANA SERT İMZALI TOPUKLU AYAKKABI MODELLERİ',
                'subheadline' => 'SON TREND TOPUKLU AYAKKABI MODELLERİ, SANA ÖZEL STİL DANIŞMANLIĞI İLE SADECE BUTİGO\'DA.',
                'image' => 'keywordbasedlanding/topuklu.jpg'
            ),
            'high-heel-shoes' => array(
                'headline' => 'IVANA SERT İMZALI YÜKSEK TOPUKLU AYAKKABI MODELLERİ',
                'subheadline' => 'SON TREND YÜKSEK TOPUKLU AYAKKABILAR, SANA ÖZEL STİL DANIŞMANLIĞI İLE SADECE BUTİGO\'DA.',
                'image' => 'keywordbasedlanding/yuksek-topuk.jpg'
            ),
            'platform-heel-shoes' => array(
                'headline' => 'IVANA SERT İMZALI PLATFORM TOPUKLU AYAKKABI MODELLERİ',
                'subheadline' => 'SON TREND PLATFORM TOPUKLU AYAKKABILAR, SANA ÖZEL STİL DANIŞMANLIĞI İLE SADECE BUTİGO\'DA.',
                'image' => 'keywordbasedlanding/topuklu.jpg'
            ),
            'platform-wedge-shoes' => array(
                'headline' => 'IVANA SERT İMZALI DOLGU TOPUKLU AYAKKABI MODELLERİ',
                'subheadline' => 'SON TREND DOLGU TOPUKLU AYAKKABILAR, SANA ÖZEL STİL DANIŞMANLIĞI İLE SADECE BUTİGO\'DA.',
                'image' => 'keywordbasedlanding/dolgu-topuk.jpg'
            ),
            'pointy-toe-shoes' => array(
                'headline' => 'IVANA SERT İMZALI SİVRİ BURUN AYAKKABI MODELLERİ',
                'subheadline' => 'SON TREND SİVRİ BURUN AYAKKABILAR, SANA ÖZEL STİL DANIŞMANLIĞI İLE SADECE BUTİGO\'DA.',
                'image' => 'keywordbasedlanding/sivri-burun.jpg'
            ),
            'round-toe-shoes' => array(
                'headline' => 'IVANA SERT İMZALI YUVARLAK BURUN AYAKKABI MODELLERİ',
                'subheadline' => 'SON TREND YUVARLAK BURUN AYAKKABILAR, SANA ÖZEL STİL DANIŞMANLIĞI İLE SADECE BUTİGO\'DA.',
                'image' => 'keywordbasedlanding/yuvarlak-burun.jpg'
            ),
            'leather-shoes' => array(
                'headline' => 'IVANA SERT DERİ AYAKKABI MODELLERİ',
                'subheadline' => 'SON TREND DERİ AYAKKABILAR, SANA ÖZEL STİL DANIŞMANLIĞI İLE SADECE BUTİGO\'DA.',
                'image' => 'keywordbasedlanding/ayakkabi.jpg'
            ),
            'suede-shoes' => array(
                'headline' => 'IVANA SERT İMZALI SÜET AYAKKABI MODELLERİ',
                'subheadline' => 'SON TREND SÜET AYAKKABILAR, SANA ÖZEL STİL DANIŞMANLIĞI İLE SADECE BUTİGO\'DA.',
                'image' => 'keywordbasedlanding/ayakkabi.jpg'
            ),
            'patent-leather-shoes' => array(
                'headline' => 'IVANA SERT İMZALI RUGAN AYAKKABI MODELLERİ',
                'subheadline' => 'SON TREND RUGAN AYAKKABILAR, SANA ÖZEL STİL DANIŞMANLIĞI İLE SADECE BUTİGO\'DA.',
                'image' => 'keywordbasedlanding/ayakkabi.jpg'
            )
        )
    );

    public function __construct() {
        $this->name = 'keywordbasedlanding';
        $this->tab = 'front_office_features';
        $this->version = 0.1;
        $this->author = 'Alper Kanat';
        $this->need_instance = 0;

        parent::__construct();

        $this->displayName = $this->l('Keyword Based Landing');
        $this->description = $this->l('Shows a landing page according to the predefined keywords.');
    }

    public function install() {
        if (parent::install() == false OR
            ! $this->registerHook('header') OR
            ! $this->registerHook('googleLanding') OR
            ! $this->registerHook('googleLandingTop')) {
            return false;
        }

        return true;
    }

	public function uninstall() 
	{
		return (parent::uninstall());
	}
	
    public function hookGoogleLanding($params) {
        global $smarty;

        $type = Tools::getValue('type');
        $model = Tools::getValue('model', 'default');

        if (! ($type AND array_key_exists($type, $this->data))) {
            Tools::redirect('/');
        }

        if (! array_key_exists($model, $this->data[$type])) {
            Tools::redirect('/');
        }

        $pageData = $this->data[$type][$model];

        $smarty->assign('headline', $pageData['headline']);
        $smarty->assign('subheadline', $pageData['subheadline']);
        $smarty->assign('image', $pageData['image']);

        return $this->display(__FILE__, 'keywordbasedlanding_default.tpl');
    }

    public function hookGoogleLandingTop($params) {
        return $this->display(__FILE__, 'keywordbasedlanding_top_default.tpl');
    }

    public function hookHeader($params) {
        if (strpos($_SERVER['PHP_SELF'], 'google_landing') !== false) {
            Tools::addCSS(($this->_path) . 'keywordbasedlanding.css', 'all');
        }
    }
}

?>

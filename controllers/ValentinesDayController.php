<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class ValentinesDayControllerCore extends FrontController {

    public function setMedia() {
        parent::setMedia();
        Tools::addCSS(_THEME_CSS_DIR_ . 'stylesurvey.css');
        if ($this->step_count != 3)
            Tools::addJS(array(_THEME_JS_DIR_ . 'survey.js', _PS_JS_DIR_ . 'jquery/coda-slider.pack.js', _PS_JS_DIR_ . 'main.js', _PS_JS_DIR_ . 'jquery/jquery.easing.compatibility.1.2.pack.js', _PS_JS_DIR_ . 'jquery/jquery.easing.1.2.pack.js'));
    }

    public function displayContent() {
        self::$smarty->display(_PS_THEME_DIR_ . 'valentines-day.tpl');
    }

    public function preProcess() {
        parent::preProcess();

        if (Tools::isSubmit('submitAccount')) {
            $customerName = urlencode(Tools::getValue('customer_name'));
            $partnerEmail = urlencode(trim(Tools::getValue('boyFriendOrPartnerEmail')));
            $partnerName = urlencode(Tools::getValue('boyFriendOrPartnerName'));
            $emailSubject = urlencode(Tools::getValue('emailSubject'));
            $giftUrl = urlencode(Tools::getValue('giftUrl'));

            if (empty($giftUrl) || empty($partnerEmail) || empty($partnerName)) {
                self::$smarty->assign('error', 'Eksik bilgiler girdiniz!');
            } else {
                $emarsys_url = "https://mailinfo.butigo.com/u/register.php?CID=119092141&f=7279&p=2&a=r&SID=&el=&llid=&counted=&c=&interest[]=[Interessen]&inp_3="
                    . $partnerEmail . "&inp_1=" . $partnerName . "&inp_32479=" . $giftUrl . "&inp_32455=" . $customerName
                    . "&inp_32458=" . $emailSubject . "&inp_32478=yes&key_id=3";

                $response = $this->curl_get($emarsys_url, 1, 0);

                if ($response) {
                    self::$smarty->assign('status','succesful');
                }
            }
        }
    }
    private function curl_get($url, $follow, $debug)
	{
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, true);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $follow);
		curl_setopt($ch, CURLOPT_TIMEOUT, 90);

		$result = curl_exec($ch);
		curl_close($ch);
		if($debug == 1) {
			//echo "<textarea rows=30 cols=120>".$result."</textarea>";
		}
		if($debug == 2) {
			//echo "<textarea rows=30 cols=120>".$result."</textarea>";
			//echo $result;
		}
		return $result;
	}

    public function run() {
        $this->init();
        $this->setMedia();
        $this->preProcess();
        $this->displayHeader();
        $this->displayContent();
        $this->displayFooter();
    }

}

?>

<?php

/**
 * Custom style survey that displays when the customer comes via
 * Google Adsense and registers without the survey.
 *
 * Since the showroom, My Boutique, has a waiting interval
 * (~ 10 hours) we need to know when the survey has been filled.
 *
 * Therefore it's necessary to run the following SQL command on
 * test & production servers:
 *
 * ALTER TABLE `bu_customer_stylesurvey` ADD COLUMN `date_add` DATETIME NULL;
 */

class GoogleAdsStyleSurveyControllerCore extends FrontController {
    // public $ssl = true;
    public $php_self = 'gads-stylesurvey.php';

    public function init() {
        $this->step_count = Tools::getValue('stp', 0);

        parent::init();
    }

    /**
     * At this step the user should already be logged in. So there's
     * no need for registering the user. Hence redirecting to showroom
     * once the survey is completely filled.
     */
    public function preProcess() {
        parent::preProcess();

        if ($this->step_count == 0) {
            Tools::redirect(self::$link->getPageLink('index.php', false), '');
        }

        if (! self::$cookie->isLogged()) {
            Tools::redirect(self::$link->getPageLink('authentication.php', false), '');
        }

        if ($this->step_count == 2) {
            $ques_ans = array(
                0 => array('YAR', 'FEM', 'MOD'),
                1 => array('KLA', 'TRE', 'YAR'),
                2 => array('KLA', 'TRE', 'MOD'),
                3 => array('KLA', 'TRE', 'YAR'),
                4 => array('FEM', 'KLA', 'MOD'),
                5 => array('KLA', 'YAR', 'FEM'),
                6 => array('MOD', 'FEM', 'YAR'),
                7 => array('MOD', 'FEM', 'TRE'),
                8 => array('KLA', 'MOD', 'TRE'),
                9 => array('bej', 'siyah', 'kahve', 'mavi', 'gri', 'yesiller', 'turuncu', 'pembe', 'mor', 'kirmizi', 'sari', 'beyaz'),
                10 => array('18-23', '24-29', '30-35', '36-45', '46+'),
                11 => array('35', '36', '37', '38', '39', '40', '41', '42'),
                12 => array('0-4', '6-8', '10-12', '14-16', '18+')
            );

            $customer = new Customer(self::$cookie->id_customer);

            $customer->age = $ques_ans[10][Tools::getValue('qqa_1010') - 1];
            $customer->shoe_size = $ques_ans[11][Tools::getValue('qqa_1011') - 1];
            $customer->dress_size = $ques_ans[12][Tools::getValue('qqa_1012') - 1];
            $this->errors = $customer->validateControler();

            if (! sizeof($this->errors)) {
                $style_answers = array();

                for ($index = 0; $index < 9; $index++) {
                    $style_answers[] = $ques_ans[$index][(Tools::getValue('qqa_100' . $index)) - 1];
                    echo Tools::getValue('qqa_100' . $index);
                }

                $styles_prioritized = array_count_values($style_answers);
                arsort($styles_prioritized, SORT_NUMERIC);
                $final_style = key( $styles_prioritized );
                $customer->category_name = trim($final_style);

                if (! $customer->update()) {
                    $this->errors[] = Tools::displayError('an error occurred while updating your style');
                } else {
                    /*Style Survey Details*/
                    $styleSurvey = new CustomerStyleSurvey();
                    $styleSurvey->id_customer = $customer->id;

                    for ($i = 1; $i <= 9; $i++) {
                        $question = 'question' . $i;
                        $question_number = Tools::getValue('qqa_100' . ($i - 1));
                        $styleSurvey->$question = $question_number . "-" . $ques_ans[$i - 1][$question_number - 1];
                    }

                    $question10 = '';

                    for ($i = 1; $i <= 12; $i++) {
                        if (Tools::getValue('qqa_1009_' . $i) == 1) {
                            $question10 .= $i . "-" . $ques_ans[9][$i - 1] . ",";
                        }
                    }

                    $styleSurvey->question10 = rtrim($question10,',');
                    $styleSurvey->add(true, true);
                    $color_answer = array();

                    for ($i = 1; $i <= 12; $i++) {
                        if (Tools::getValue('qqa_1008_' . $i) == 1) {
                            $color_answer[] = $ques_ans[9][$i - 1];
                        }
                    }

                    $color_tags = array();

                    foreach ($color_answer as $color) {
                        $color_tag = new Tag(NULL, $color, $cookie->id_lang);
                        $color_tags[] = $color_tag->id;
                    }

                    $customer->addTags(intval($customer->id), $color_tags, 'color', 0, false);

                    Tools::redirect(self::$link->getPageLink('gads-stylesurvey.php', false) . '?stp=3&res=' . $final_style, '');
                }
            } else {
                $this->step_count = 1;
            }
        }

        if ($this->step_count == 3) {
            if (self::$cookie->takeSurvey) {
                unset(self::$cookie->takeSurvey);
            }

            $customer = new Customer(self::$cookie->id_customer);
            $customer_join_month = substr($customer->date_add, 5, 2);
            $customer_join_year = substr($customer->date_add, 0, 4);

            $styleSurvey = CustomerStyleSurvey::getByCustomerId($customer->id);
            $completion_time = strtotime($styleSurvey['date_add']);
            $waiting_time = $completion_time + (10 * 60 * 60); // Made as 10 hours
            $now = time();

            if ($now < $waiting_time) {
                $waiting_room = true;
            } else {
                $waiting_room = false;
            }

            self::$smarty->assign(array(
                'customer_join_month' => $customer_join_month,
                'customer_join_year' => $customer_join_year,
//                'style_result' => Tools::getValue('res'),
                'waiting_room' => $waiting_room
            ));


			$styleSurveyResult = Tools::getValue('res');
            $styleHeadline = '';
            if(strtolower($styleSurveyResult) == 'kla'){
                $styleHeadline = 'Klasik Yalın Özgüvenli';
            }elseif(strtolower($styleSurveyResult) == 'mod'){
                $styleHeadline = 'Modern Rafine Minimalist';
            }elseif(strtolower($styleSurveyResult) == 'tre'){
                $styleHeadline = 'Trendy Casual Yaratıcı';
            }elseif(strtolower($styleSurveyResult) == 'yar'){
                $styleHeadline = 'Romantik Elegan Unique';
            }elseif(strtolower($styleSurveyResult) == 'fem'){
                $styleHeadline = 'Feminen Cesur Kozmopolit';
            }
			self::$smarty->assign(array(
				'style_result' => $styleSurveyResult,
                'styleHeadline' => $styleHeadline
            ));

        }

        self::$smarty->assign('step_count', $this->step_count);
    }

    public function setMedia() {
        parent::setMedia();

        Tools::addCSS(_THEME_CSS_DIR_ . 'stylesurvey.css');

        if (! in_array($this->step_count, array(2, 3))) {
            Tools::addJS(array(
                _THEME_JS_DIR_ . 'survey.js',
                _PS_JS_DIR_ . 'jquery/coda-slider.pack.js',
                _PS_JS_DIR_ . 'main.js',
                _PS_JS_DIR_ . 'jquery/jquery.easing.compatibility.1.2.pack.js',
                _PS_JS_DIR_ . 'jquery/jquery.easing.1.2.pack.js'
            ));
        }
    }

    public function displayContent() {
        parent::displayContent();

        if ($this->step_count == 1) {
            self::$smarty->display(_PS_THEME_DIR_ . 'gads-stylesurvey.tpl');
        } elseif ($this->step_count == 3) {
            self::$smarty->display(_PS_THEME_DIR_ . 'stylesurvey_complete.tpl');
        }
    }
}

?>

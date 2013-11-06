<?php

class PaymentAgreementsControllerCore extends FrontController {

    public $php_self = 'agreements.php';

    public function setMedia() {
        parent::setMedia();
        Tools::addCSS(_THEME_CSS_DIR_ . 'global.css');
        /*Tools::addCSS(_THEME_CSS_DIR_.'my-acc-sidebar.css');
        Tools::addJS(_THEME_JS_DIR_.'tools.js');*/
    }

    public function process() {
        parent::process();

        $id_cms = (int) (Tools::getValue('id_cms'));

        if ($id_cms == 20 || $id_cms == 21 || $id_cms == 22) {
            if (isset(self::$cart->id_address_delivery)) {
                $deliveryAddress = new Address((int) (self::$cart->id_address_delivery));

                if (Validate::isLoadedObject($deliveryAddress)){
                    /*self::$smarty->assign('deliveryAddress',$deliveryAddress);*/
                    $agreement_dynamic_content1 = array(
                        '[a1]' => $deliveryAddress->address1,
                        '[city]' => Province::getProvinceNameById($deliveryAddress->id_province),
                        '[postcode]' => $deliveryAddress->postcode,
                        '[state]' => State::getNameById($deliveryAddress->id_state),
			/*'[province]' => Province::getProvinceNameById($deliveryAddress->id_province),*/
                        '[country]' => $deliveryAddress->country,
                        '[phone]' => $deliveryAddress->phone);
                }
            }
            if (isset(self::$cart->id_customer)) {
                $customer = new Customer((int) (self::$cart->id_customer));

                if (Validate::isLoadedObject($customer)) {
                    $firstname = strval($customer->firstname);
                    $lastname = strval($customer->lastname);
                    $is_member = Customer::memberOfGroup((int) (self::$cart->id_customer));
                    self::$smarty->assign(array('is_member' => $is_member
                            /*'firstname' => $firstname,
                            'lastname' => $lastname
                             'email' => $customer->email*/
                    ));
                    $agreement_dynamic_content2 = array('[firstname]' => $firstname,
                        '[lastname]' => $lastname,
                        '[email]' => $customer->email);
                }
            }
            if ($id_cms == 20) {
                $pre_sales_agreement = new CMS(20, (int) (self::$cookie->id_lang));
                $pre_sales_agreement_content = $pre_sales_agreement->content;
            }else if ($id_cms == 21) {
                $non_member_sales_agreement = new CMS(21, (int) (self::$cookie->id_lang));
                $non_member_sales_agreement_content = $non_member_sales_agreement->content;
            }else if ($id_cms == 22) {
                $member_sales_agreement = new CMS(22, (int) (self::$cookie->id_lang));
                $member_sales_agreement_content = $member_sales_agreement->content;
            }

            $summary = self::$cart->getSummaryDetails();
            $customizedDatas = Product::getAllCustomizedDatas((int) (self::$cart->id));

            Product::addCustomizationPrice($summary['products'], $customizedDatas);

            $priceDisplay = Product::getTaxCalculationMethod();
            $total_with_intrst = Tools::getValue('total_amount');

            if (Tools::getValue('instalments')) {
                $no_of_installments = Tools::getValue('instalments');
                $each_installment = floatval(number_format($total_with_intrst / $no_of_installments, 2, '.', ''));
            } else {
                $no_of_installments = 1;
            }

            $product_data = '<table class = "consumer_info history_list">
                            <thead>
                                    <th>Hizmet Detayı</th>
                                    <th>Adet</th>
                                    <th>Peşin Fiyat</th>
                                    <th>Taksit Sayısı</th>
                                    <th>Vadeli Fiyat</th>
                                    <th>Ara Toplam(KDV dahil)</th>
                            </thead>
                            <tbody>';

            foreach ($summary['products'] as $product) {
                $product_data .= '<tr><td>' . $product['name'] . '<br/>' . $product['attributes'] . '</td><td>' . $product['quantity'] . '</td>';

                if (!$priceDisplay){
                    $product_data .= '<td> ' . Tools::displayPrice($product['price_wt']) . '</td>';
                }else{
                    $product_data .= '<td> ' . Tools::displayPrice($product['price']) . '</td>';
                }

                $product_data .= '</td><td>&nbsp;</td><td>&nbsp;</td>';

                if (isset($customizedDatas['productId']['productAttributeId']) AND $quantityDisplayed == 0) {
                    if (!$priceDisplay)
                        $product_data .= '<td>' . Tools::displayPrice($product['total_customization_wt']) . '</td>';
                    else
                        $product_data .= '<td>' . Tools::displayPrice($product['total_customization']) . '</td>';
                }
                else {
                    if (!$priceDisplay)
                        $product_data .= '<td>' . Tools::displayPrice($product['total_wt']) . '</td>';
                    else
                        $product_data .= '<td>' . Tools::displayPrice($product['total']) . '</td>';
                }

                $product_data .= '</td></tr>';
            }

            /* $shipping_cost = $summary['total_shipping'] > 0 && Tools::getIsset('cod') && Tools::getValue('cod') == 1 ? Tools::displayPrice($summary['total_shipping']-3.00) : Tools::displayPrice($summary['total_shipping']); */
            $product_data .= '<tr>
                                <td>Kargo Bedeli</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td><span>' . Tools::displayPrice($summary['total_shipping']) . '</span></td></tr>';

            /* Extra Shipping Charge for the Cash on Delivery */
            $deal_category_id=Configuration::get('DAILYDEAL_CATEGORY_ID');
            $cart_products_for_dailydeal_chk = self::$cart->getProducts();
            $prodIdExistsInCategory=Product::pIdBelongToCategoryId($cart_products_for_dailydeal_chk, $deal_category_id);
			if($prodIdExistsInCategory ==''){
                $shippingChargeForCashOnDelivery = Delivery::getShippingChargeForCashOnDelivery(self::$cookie->id_customer, self::$cookie->id_lang);
             }else{
                 $shippingChargeForCashOnDelivery='';
             }
             
            $cod = 0;
            if (Tools::getIsset('cod') && Tools::getValue('cod') == 1) {
                $cod = (int) Tools::getValue('cod');
                $product_data .= '<tr>
                                <td>Teslim nakit için ekstra kargo ücreti</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td><span>' . Tools::displayPrice($shippingChargeForCashOnDelivery) . '</span></tr></td>';
            }

            /*Discounts*/
            foreach ($summary['discounts'] as $disconts) {
                $product_data .= '<tr>
                                <td>İndirimler / Kuponlar</td>
                                <td>' . $disconts['name'] . '</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td><span>-' . Tools::displayPrice($disconts['value_real']) . '</span></td></tr>';
            }
            /*Total Price*/
            $product_data .= '<tr>
                            <td>Toplam<br/>(KDV dahil)</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td>&nbsp;</td>
                            <td><span>' . ( $cod != 1 ? Tools::displayPrice($summary['total_price']) : Tools::displayPrice($summary['total_price'] + $shippingChargeForCashOnDelivery)) . '</span></td></tr>';

            /*Total Price with Installments*/
            if ($no_of_installments > 1) {
                $product_data .= '<tr>
                                <td>Taksit Sayısı</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>' . $no_of_installments . '</td></tr>';

                $product_data .= '<tr>
                                <td>Taksit</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>' . Tools::displayPrice($each_installment) . '</td></tr>';

                $product_data .= '<tr>
                                <td>Taksitli Toplam</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>' . Tools::displayPrice($total_with_intrst) . '</td>
                                </tr>';
            }

            $product_data .= '</tbody></table>';

            $agreement_dynamic_content3 = array('[product_data]' => $product_data);

            /* $agreement_dynamic_content = array (
              '[a1]' => $deliveryAddress->address1,
              '[city]' => $deliveryAddress->city,
              '[postcode]' => $deliveryAddress->postcode,
              '[country]' => $deliveryAddress->country,
              '[email]' => $customer->email,
              '[phone]' => $deliveryAddress->phone,
              '[product_data]' => $product_data
              ); */

            $agreement_dynamic_content = array_merge($agreement_dynamic_content1, $agreement_dynamic_content2, $agreement_dynamic_content3);

            if ($id_cms == 20) {
                foreach ($agreement_dynamic_content as $key => $value) {
                    $pre_sales_agreement_content = str_replace($key, strval($value), $pre_sales_agreement_content);
                }
                self::$smarty->assign('pre_sales_agreement_content', $pre_sales_agreement_content);
            }elseif ($id_cms == 21) {
                foreach ($agreement_dynamic_content as $key => $value) {
                    $non_member_sales_agreement_content = str_replace($key, strval($value), $non_member_sales_agreement_content);
                }
                self::$smarty->assign('non_member_sales_agreement_content', $non_member_sales_agreement_content);
            }elseif ($id_cms == 22) {
                foreach ($agreement_dynamic_content as $key => $value) {
                    $member_sales_agreement_content = str_replace($key, strval($value), $member_sales_agreement_content);
                }
                self::$smarty->assign('member_sales_agreement_content', $member_sales_agreement_content);
            }

            self::$smarty->assign('id_cms', $id_cms);
        }

        //self::$smarty->assign('data2',$data2);
        //if(isset($css_files) AND !empty($css_files)) self::$smarty->assign('css_files', $css_files);
        //include(dirname(__FILE__).'/header.php');
    }

    public function run() {
        $this->init();
        //$this->setMedia();
        $this->preProcess();
        //$this->displayHeader();
        $this->process();
        $this->displayContent();
        //$this->displayFooter();
    }

    public function displayContent() {
        //parent::displayContent();
        //global $smarty;
        self::$smarty->display(_PS_THEME_DIR_ . 'agreements.tpl');
    }

}

?>

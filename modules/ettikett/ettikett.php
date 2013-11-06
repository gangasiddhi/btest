<?php

/**
 * Description of ETTIKETT Analytics
 * This module is implemented to give some discounts(5 TL) to the customers.
 * upon sharing the Butigo on Facebook and Twitter confirmation,
 * OR Visit and registerd to the site from the Facebook/Twitter shared link.
 *
 * @author Avish Websoft Pvt Ltd (Gangadhar K.M).
 */
if (!defined('_CAN_LOAD_FILES_'))
    exit;

class Ettikett extends Module {

    public function __construct() {
        $this->name = 'ettikett';
        $this->tab = 'analytics_stats';
        $this->version = 1.0;
        $this->author = 'PrestaShop';

        parent::__construct();

        $this->displayName = $this->l('Ettikett Analytics');
        $this->description = $this->l('This module must be enabled if you want to use Ettikett Analytics.');
    }

    public function install() {
        if (! parent::install()
            OR ! $this->registerHook('footer')
            OR ! $this->registerHook('header')
            OR ! $this->registerHook('shoppingCartDiscounts')
           // OR ! $this->registerHook('orderConfirmation')
            OR ! $this->registerHook('EttikettOrderConfirmation')
            OR ! $this->registerHook('OrderConfirmationReturn')
            OR ! $this->registerHook('landing')
            OR ! $this->registerHook('createAccount'))
            return false;

        return true;
    }
	
	public function uninstall() {
		return (parent::uninstall());
	}

    public function hookHeader($params) {
       // if (Tools::getValue('etkt_ref') == 1 OR Tools::getValue('etkt') == 1) {
            Tools::addCSS($this->_path . "assets/ettikett.css", "all");
        //}
    }

    public function hookFooter($params) {
        global $smarty, $link;

        if ($this->isEttikettDiscountValid($params) && strpos($_SERVER['PHP_SELF'], 'order-confirmation') !== false) {
            $redirect_url = $link->getPageLink('return.php', true) . "?key=" . Tools::getValue('key') . "&etkt=1";
            $smarty->assign('REDIRECT_URL', $redirect_url);
            return $this->display(__FILE__, 'ettikett.tpl');
        }

        return "";
    }

    /**
     * Link to the ettikett at the order-comfirmation page popup,
     * That means mapping the images of Facebook and Twitter popup
     */
//    public function hookOrderConfirmation($params) {
//        if ($this->isEttikettDiscountValid($params) && strpos($_SERVER['PHP_SELF'], 'order-confirmation') !== false) {
//            return $this->display(__FILE__, 'ettikett-btn.tpl');
//        }
//
//        return "";
//    }

     public function hookEttikettOrderConfirmation($params) {
        if ($this->isEttikettDiscountValid($params) && strpos($_SERVER['PHP_SELF'], 'order-confirmation') !== false) {
            return $this->display(__FILE__, 'ettikett-btn.tpl');
        }

        return "";
    }
    
    /**
     * Returns TRUE if the ettikett discount can be applied and FALSE otherwise
     *
     * Checks for following conditions:
     *
     *     - if logged in
     *     - if customer in specified groups (EttikettShared OR EttikettReferred)
     *     - if vouchers are created
     *     - if discounts are appliable (valid)
     */
    private function isEttikettDiscountValid($params) {
        if ($params['cookie']->logged) {
            $errors = array();
            $customer = new Customer($params['cookie']->id_customer);
            $customer_groups = $customer->getGroups();
            $etikett_shared_group_id = Group::getGroupIdByName("EttikettShared", $params['cookie']->id_lang);
            $etikett_referred_group_id = Group::getGroupIdByName("EttikettReferred", $params['cookie']->id_lang);

            $etikett_groups = array($etikett_shared_group_id, $etikett_referred_group_id);
            $cus_belongs_to_ettikett = array_intersect($etikett_groups, $customer_groups);

            if (! empty($cus_belongs_to_ettikett)) {
                if ($cus_belongs_to_ettikett[0] == $etikett_shared_group_id) {
                    $ettikett_discount = "ETTIKETTSHARED";
                } else if ($cus_belongs_to_ettikett[1] == $etikett_referred_group_id) {
                    $ettikett_discount = "ETTIKETTREFERRED";
                }

                if ($discounts = Discount::getIdByName($ettikett_discount)) {
                    $ettikett_discount = new Discount(intval($discounts));

                    if (is_object($ettikett_discount) AND $ettikett_discount) {
                        if ($tmpError = $params['cart']->checkDiscountValidity(
                            $ettikett_discount,
                            $params['cart']->getDiscounts(),
                            $params['cart']->getOrderTotal(),
                            $params['cart']->getProducts(),
                            true)) {

                            $errors[] = $tmpError;
                        }
                    }

                    if (! sizeof($errors)) {
                        if ($params['cart']->getDiscountsCustomer($ettikett_discount->id) <= 0) {
                            $params['cart']->addDiscount((int) $ettikett_discount->id);
                        }
                    } else {
                        // there are errors, so it's not valid
                        return false;
                    }
                }
            }

            return true;
        }

        return false;
    }

    /**
     * Adding the Voucher to the cart if the customer belongs to the EttikettShared OR EttikettReferred groups
     */
    public function hookShoppingCartDiscounts($params) {
        return $this->isEttikettDiscountValid($params);
    }

    /**
     * To add a customer to the EttikettShared group upon Butigo link sharing to Facebook/Twitter successfully.
     * Upon successfull sharing, the customer can see a banner , saying that she/he have some discounts.
     * Customer are addedd to the above group to give a 5TL discount.
     */
    public function hookOrderConfirmationReturn($params) {
        global $smarty;

        $strDiscountName = 'ETTIKETTSHARED';

        if (Tools::getValue('etkt') == 1) {
            $customer = new Customer($params['cookie']->id_customer);
            $customer_groups = $customer->getGroups();
            $etikett_shared_group[] = Group::getGroupIdByName("EttikettShared", $params['cookie']->id_lang);
            $cus_belongs_to_ettikett = array_intersect($etikett_shared_group, $customer_groups);
            $discountId = Discount::getIdByName($strDiscountName);
            $discount = $discountId ? new Discount(intval($discountId)) : null;

            if (empty($cus_belongs_to_ettikett)) {
                if (! $customer->addGroups($etikett_shared_group)) {
                    $this->errors[] = Tools::displayError('Cannot add to group');
                }
            }

            $smarty->assign(array(
                'discount' => $discount ? $discount->value : null,
                'etkt_shared' => 1
            ));

            return $this->display(__FILE__, 'ettikett-banner.tpl');
        }

        return "";
    }

    /**
     * To add a customer to the EttikettReferred group upon register to the site sucessfully from Facebook/Twitter sharing link.
     * Customer are added to the above group to give a 5TL discount.
     */
    public function hookCreateAccount($params = array()) {
        global $cookie;

        if (isset($_COOKIE['etkt']) AND $_COOKIE['etkt'] == 1) {
            $customer = new Customer($cookie->id_customer);
            $customer_groups = $customer->getGroups();
            $etikett_referred_group[] = Group::getGroupIdByName("EttikettReferred", $cookie->id_lang);
            $cus_belongs_to_ettikett = array_intersect($etikett_referred_group, $customer_groups);

            if (empty($cus_belongs_to_ettikett)) {
                if (! $customer->addGroups($etikett_referred_group)) {
                    $this->errors[] = Tools::displayError('Cannot add to group');
                }
            }
        }

        return true;
    }
}

?>

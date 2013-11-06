<?php
/*
* 2007-2011 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2011 PrestaShop SA
*  @version  Release: $Revision: 7690 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/


class FraudModerationDetail extends ObjectModel {
    public $id_moderation;
    public $id_customer;
    public $id_order;
    public $date_add;
    public $date_moderated;
    public $was_moderated;
    public $id_employee;


    /**
     * Using  on moderation_result table, OrderModeration/Product Moderation
     */
    const MODERATION_OBJECT_TYPE = 3;

    protected   $fieldsRequired = array('id_order', 'id_customer');

    protected   $fieldsValidate = array('id_order' => 'isUnsignedId' , 'id_customer' => 'isUnsignedId');

    protected $table = 'fraud_moderation';
    protected   $identifier = 'id_moderation';

    public function getFields() {
        parent::validateFields();

        $fields = array(
            'id_order' =>  (int) $this->id_order,
            'id_customer' => $this->id_customer,
            'was_moderated' => $this->was_moderated,
            'id_employee' => $this->id_employee,
            'date_add' => $this->date_add,
            'date_moderated' => $this->date_moderated,
        );

        return $fields;
    }

    public static function isExistByOrderId($orderId) {
        return self::getInstanceByOrderId($orderId)->id_moderation ? true :  false;
    }

    public static function getModerations($limit = 10, $page = 1, $getModerated = false) {
        $sql = 'Select SQL_CALC_FOUND_ROWS * from '._DB_PREFIX_.'fraud_moderation WHERE  was_moderated='. ($getModerated ? 1 : 0) ." ORDER BY id_moderation"
        .' LIMIT '.(((int)($page) - 1) * (int)($limit)).', '.(int)($limit);

        $result['objects'] = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        $result['totalItem'] = (int) Db::getInstance()->getValue('SELECT FOUND_ROWS() as rowCount');
        return $result;
    }


    public function approve($id_state) {
        global $cookie;

        $this->beginTransaction();
        try {
            if ($id_state == Configuration::get('PS_OS_FRAUD_ORDER')) {
                $this->markAsFraud();
            } else {

                $this->updateFraudOrderStates(_PS_OS_PREPARATION_); // This must be call before markCustomerAsVerified
                $this->markCustomerAsVerified($this->id_customer);

                $this->was_moderated = true;
                $this->id_employee = $cookie->id_employee;
                $this->date_moderated = date("Y-m-d H:i:s", time());

                $this->save();
            }

            $this->commitTransaction();
            return true;
        } catch(Exception $e) {
            $this->rollbackTransaction();
            throw Exception('Error'. $e->getMessage());
            return false;
        }
    }

    public function markCustomerAsVerified($id_customer) {
        global $cookie;

        $iCustomer = new Customer($id_customer);
        $iCustomer->placed_order = true;
        $iCustomer->save();

        $sql = "Update " . _DB_PREFIX_ ."fraud_moderation SET was_moderated=1, id_employee=".$cookie->id_employee;
        $sql .= ",date_moderated = '".date("Y-m-d H:i:s", time())."' WHERE id_customer=".$iCustomer->id." and was_moderated=0";
        DB::getInstance()->executeS($sql);

        return true;
    }

    public function updateFraudOrderStates($id_state){

        $sql = "Select id_order from " . _DB_PREFIX_ ."fraud_moderation  Where id_customer=".$this->id_customer." and was_moderated=0";
        $result = DB::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        foreach ($result as $item) {
            // Update all orders these waiting modeation. Set state  to _PS_OS_PREPARATION_.
            // Handan says all of the orders's status set to _PS_OS_PREPARATION_ if moderation approve.
            $iOrder = new Order($item['id_order']);
            $iOrder->setCurrentState($id_state);
        }

        return true;
    }

    public function markAsFraud() {
        global $cookie;

        $iOrder = new Order($this->id_order);
        $iOrder->setCurrentState(Configuration::get('PS_OS_FRAUD_ORDER'));

        $this->was_moderated = true;
        $this->id_employee = $cookie->id_employee;
        $this->date_moderated = date("Y-m-d H:i:s", time());

        return $this->save();
    }
}

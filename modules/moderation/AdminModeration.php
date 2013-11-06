<?php

require_once('moderation.php');
require_once('FraudModerationDetail.php');


class AdminModeration extends AdminTab {

    public function postProcess() {
        parent::postProcess();

        // Form submited with buttons
        $moderationType =  Tools::getValue('moderationType'); // product, order

        $approveSelected = Tools::getValue('approveSelected');
        $cancelSelected =  Tools::getValue('cancelSelected');
        $rejectSelected =  Tools::getValue('rejectSelected');
        $moderations = Tools::getValue('moderation');

        if ($approveSelected OR $cancelSelected OR $rejectSelected) {
            if ($approveSelected) {
                $action = 'approve';
            } elseif ($cancelSelected) {
                $action = 'cancel';
            } elseif ($rejectSelected) {
                $action = 'reject';
            }
        } elseif($moderations) {
            $action = Tools::getValue('moderation_action');
        }

        foreach ($moderations as $id_moderation => $moderation) {
            if (!$moderation['process']) continue;

            if ($moderationType == 'fraud') {
                $result = $this->fraudProcess(array( 'id_moderation' => $id_moderation, 'action' => $action, 'id_order_state' => $moderation['id_order_state'] ));
            } elseif ($moderationType == 'order') {
                $result = $this->orderProcess($id_moderation, $action);
            } elseif ($moderationType == 'product') {
                $result = $this->productProcess($id_moderation, $action);
            }

            if ($result !== true) {
                $this->_errors = array_merge($this->_errors, $result);
            }
        }

        if ($moderations AND !$this->_errors) {
            Tools::redirectAdmin('index.php?tab=AdminModeration&conf=28&token=' . Tools::getAdminTokenLite('AdminModeration'));
        }
    }

    private function fraudProcess($params) {
        $iModerationDetail = new FraudModerationDetail($params['id_moderation']);
        if ($params['action'] == 'approve') {
            return $result = $iModerationDetail->approve($params['id_order_state']);
        }

        return false;
    }

    private function orderProcess($id_moderation, $action) {
        $iModerationDetail = new OrderModerationDetail($id_moderation);

        if ($action == 'approve') {
            return $result = $iModerationDetail->approve();
        } elseif ($action == 'reject') {
            return $iModerationDetail->reject(Tools::getValue('moderation_reason_id'), Tools::getValue('moderation_message'));
        } elseif ($action == 'cancel') {
            return $iModerationDetail->cancel();
        }
    }


    private function productProcess($id_moderation, $action) {
        $iModerationDetail = new ProductModerationDetail($id_moderation);

        if ($action == 'approve') {
            return $result = $iModerationDetail->approve();
        } elseif ($action == 'cancel') {
            return $iModerationDetail->cancel();
        }
    }


    public function display($token) {
        if ($this->tabAccess['edit'] !== '1') {
            $this->_errors[] = Tools::displayError('You do not have permission to delete here.');
            return;
        }

        $this->displayOrderModerations();
        $this->displayProductModerations();
        $this->displayFraudOrders();
        echo $this->includeCSSFiles();
        echo $this->includeJSFiles();
    }


    public function displayOrderModerations() {


        global $currentIndex, $cookie;

        $pageNo = (Tools::getValue('orderPagination') ? Tools::getValue('orderPagination') : 1 );
        $itemPerPage = Configuration::get('MOD_ORDER_ITEM_PER_PAGE');

        $moderations = OrderModerationDetail::getModerations($itemPerPage, $pageNo);

        echo '
        <div id="order-moderation-con">
            <h3>'.$this->l('Order Moderations').'</h3>
            <form method=POST id="order-moderation-form" action="'.$currentIndex.'&moderationType=order&token='.$this->token.'">
                <div class="table-action-buttons">
                    <input type="submit" class="button" name="approveSelected" value="'.$this->l('Approve').'">
                    <input type="submit" class="button" name="cancelSelected" value="'.$this->l('Cancel').'">
                </div>

                <input type="hidden" id="order-moderation-action" name="moderation_action">

                <table class="table" cellpadding="0" cellspacing="0">
                    <thead>
                        <tr class="nodrag nodrop">
                            <th>
                                <input type="checkbox" class="select-all" id="select-all-orders">
                                <label for="select-all-orders">Select All</label>
                            </th>
                            <th>Order</th>
                            <th>Customer</th>
                            <th>Type</th>
                            <th>Reason</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>';
                        if (!sizeof($moderations['objects'])) {
                            echo '<tr><td class="center" colspan="'.sizeof($this->_list).'">'.$this->l('Not exist any order that waiting for approve').'</td></tr>';
                        }

                        $irow = 0;
                        $moderationReasons = moderation::getOrderModerationReasons();
                        $productModerationReasons = moderation::getProductModerationReasons(); // If order have only one product employee select product's reason.


                        foreach ($moderations['objects'] AS $tr) {
                            $id_moderation = (int)($tr['id_moderation']);
                            $id_moderation_type = (int) $tr['id_moderation_type'];
                            $id_order = (int)($tr['id_order']);
                            $id_order_detail = (int)($tr['id_order_detail']);
                            $iOrder = new Order($id_order);
                            $customer = new Customer($iOrder->id_customer);

                            echo '
                            <tr data-id-moderation="'.$id_moderation.'" '.($irow++ % 2 ? ' class="alt_row"' : '').'>
                                <td style="vertical-align: top; padding: 4px 0 4px 0" class="center">
                                    <input type="checkbox" id="order_moderation_'.$id_moderation.'" class="cb-moderation-item" name="moderation['.$id_moderation.'][process]" value="'.$id_moderation.'"  />
                                </td>
                                <td style="width: 70px; vertical-align: top; padding: 4px 0 4px 0;">
                                    <a href="index.php?tab=AdminOrders&id_order='.$id_order.'&vieworder&token='.Tools::getAdminTokenLite('AdminOrders').'">'.
                                        $tr['id_order']
                                    .'</a>
                                </td>
                                <td style="width: 140px; vertical-align: top; padding: 4px 0 4px 0; ">
                                    <a href="index.php?tab=AdminCustomers&id_customer='.(int)$customer->id.'&viewcustomer&token='.Tools::getAdminTokenLite('AdminCustomers').'">'.
                                        $customer->firstname.' '.$customer->lastname
                                    .'</a>
                                </td>

                                <td style="width: 140px; vertical-align: top; padding: 4px 0 4px 0;">';

                                    if ($id_moderation_type == Configuration::get('MODERATION_TYPE_EXCHANGE')) {
                                        echo $this->l('Exchange');
                                    } else if ($id_moderation_type == Configuration::get('MODERATION_TYPE_CANCEL')) {
                                        echo $this->l('Cancel');
                                    }

                                    echo ' ('.$iOrder->module.')
                                </td>

                                <td style="width: 140px; vertical-align: top; padding: 4px 0 4px 0;">';
                                    $useOrderModerationReasonText = false;
                                    foreach ($moderationReasons as $item) {
                                        if ($item['id'] == $tr['id_reason']) {
                                            echo $item['text'];
                                            $useOrderModerationReasonText = true;
                                            break;
                                        }
                                    }

                                    if (!$useOrderModerationReasonText) {
                                        // If order have only one product, employee select product's reason instead order.
                                        foreach ($productModerationReasons as $item) {
                                            if ($item['id'] == $tr['id_reason']) {
                                                echo $item['text'];
                                                break;
                                            }
                                        }
                                    }
                                    if ($tr['message']) {
                                        echo '(<a href="#" onclick="alert(\''.addslashes($tr['message']).'\');return false;">Note</a>)';
                                    }
                                echo '</td>
                                <td class="action-buttons-con">
                                    <img class="moderation-action" data-action="approve" src="../img/admin/enabled.gif" title="Approve" class="img_approve" >
                                    <img class="moderation-action" data-action="cancel" src="../img/admin/disabled.gif" title="'.$this->l('Delete').'" />
                                </td>
                            </tr>';
                        }
                echo '</tbody>
                </table>

                <div id="order-moderation-pagination" class="pagination butigo-pagination green-pagination"></div>
                <script type="text/javascript">
                    $(function(){
                        $("#order-moderation-pagination").pagination('.$moderations['totalItem'].', {
                            prev_text: "'.$this->l("Prev").'"
                            , next_text: "'.$this->l("Next").'"
                            , items_per_page: '.$itemPerPage.'
                            , num_display_entries: 10
                            , num_edge_entries: 2
                            , current_page : '.($pageNo - 1).'
                            , callback: function(pageNo, $pagination) {
                                if (pageNo == '.$pageNo.' - 1) return;
                                var urlParams =  getQueryString();
                                urlParams["orderPagination"] = parseInt(pageNo) + 1;
                                goToUrl(location.pathname + "?" + $.param(urlParams));
                                return false;
                            }
                        });

                        $("#order-moderation-form").find(".moderation-action").click(function(){
                            $("#order-moderation-action").val($(this).attr("data-action"));
                            $(this).closest("tr").find(":checkbox").attr("checked", true);
                            $(this).closest("form").submit();
                        });
                    });
                </script>
            </form>
        </div>
        ';

        echo '<script>

        </script>';
    }

    public function displayProductModerations() {

        global $currentIndex, $cookie;

        $pageNo = (Tools::getValue('productPagination') ? Tools::getValue('productPagination') : 1 );
        $itemPerPage = Configuration::get('MOD_PROD_ITEM_PER_PAGE');

        $moderations = ProductModerationDetail::getModerations($itemPerPage, $pageNo);

        echo '
        <div id="product-moderation-con">
            <h3>'.$this->l('Product Moderations').'</h3>
            <form method=POST id="product-moderation-form" action="'.$currentIndex.'&moderationType=product&token='.$this->token.'">
                <div class="table-action-buttons">
                    <input type="submit" class="button" name="approveSelected" value="'.$this->l('Approve').'">
                    <input type="submit" class="button" name="cancelSelected" value="'.$this->l('Cancel').'">
                </div>

                <input type="hidden" id="product-moderation-action" name="moderation_action">


                <table class="table" cellpadding="0" cellspacing="0">
                    <thead>
                        <tr class="nodrag nodrop">
                            <th>
                                <input type="checkbox" class="select-all" id="select-all-products">
                                <label for="select-all-products">Select All</label>
                            </th>
                            <th>Order</th>
                            <th>Customer</th>
                            <th>Product</th>
                            <th>Type</th>
                            <th>Quantity</th>
                            <th>Reason</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>';
                        if (!sizeof($moderations['objects'])) {
                            echo '<tr><td class="center" colspan="'.sizeof($this->_list).'">'.$this->l('Not exist any product that waiting for approve').'</td></tr>';
                        }

                        $irow = 0;
                        $moderationReasons = moderation::getProductModerationReasons();

                        foreach ($moderations['objects'] AS $tr) {
                            $id_moderation = (int)($tr['id_moderation']);
                            $id_moderation_type = (int) $tr['id_moderation_type'];
                            $id_order = (int)($tr['id_order']);
                            $id_order_detail = (int)($tr['id_order_detail']);
                            $iOrder = new Order($id_order);
                            $customer = new Customer($iOrder->id_customer);
                            $iOrderDetail = new OrderDetail($tr['id_order_detail']) ;
                            echo '
                            <tr data-id-moderation="'.$id_moderation.'" '.($irow++ % 2 ? ' class="alt_row"' : '').'>
                                <td style="padding: 4px" class="center">
                                    <input type="checkbox" id="product_moderation_'.$id_moderation.'" class="cb-moderation-item" name="moderation['.$id_moderation.'][process]" value="'.$id_moderation.'"  />
                                </td>
                                <td style="padding: 4px;">
                                    <a href="index.php?tab=AdminOrders&id_order='.$id_order.'&vieworder&token='.Tools::getAdminTokenLite('AdminOrders').'">'.
                                        $tr['id_order']
                                    .'</a>
                                </td>
                                <td style="padding: 4px;width: 140px; ">
                                    <a href="index.php?tab=AdminCustomers&id_customer='.(int)$customer->id.'&viewcustomer&token='.Tools::getAdminTokenLite('AdminCustomers').'">'.
                                        $customer->firstname.' '.$customer->lastname
                                    .'</a>
                                </td>
                                <td style="padding: 4px;width: 160px; ">
                                    <a href="index.php?tab=AdminCatalog&product='.(int)$iOrderDetail->product_id.'&updateproduct&token='.Tools::getAdminTokenLite('AdminCatalog').'">'
                                        .$iOrderDetail->product_name.
                                    '</a>
                                </td>
                                <td style="padding: 4px;width: 100px; ">';

                                    if ($id_moderation_type == moderation::MODERATION_TYPE_EXCHANGE) {
                                        echo $this->l('Exchange');
                                    } else if ($id_moderation_type == moderation::MODERATION_TYPE_CANCEL) {
                                        echo $this->l('Cancel');
                                    }  elseif ($id_moderation_type == moderation::PROD_MOD_TYPE_MANUAL_REFUND) {
                                        echo $this->l('Manual refund');
                                    }  elseif ($id_moderation_type == moderation::PROD_MOD_TYPE_CANCEL_EXCHANGE) {
                                        echo $this->l('Exchange cancellation');
                                    }

                                    echo ' ('.$iOrder->module.')
                                </td>
                                <td style="width:50px;text-align:center;">'.$tr['quantity'].'</td>

                                <td style="width: 140px;padding: 4px 0 4px 0;">';
                                    if ($id_moderation_type == moderation::PROD_MOD_TYPE_MANUAL_REFUND) {
                                        echo $tr['message']. ' TL';
                                    } else {
                                        foreach ($moderationReasons as $item) {
                                            if ($item['id'] == $tr['id_reason']) {
                                                echo $item['text'];
                                            }
                                        }
                                        if ($tr['message']) {
                                            echo '(<a href="#" onclick="alert(\''.addslashes($tr['message']).'\');return false;">Note</a>)';
                                        }
                                    }
                                echo '</td>
                                <td class="action-buttons-con">
                                    <img class="moderation-action" data-action="approve" src="../img/admin/enabled.gif" title="Approve" class="img_approve" >
                                    <img class="moderation-action" data-action="cancel" src="../img/admin/disabled.gif" title="'.$this->l('Cancel').'" />
                                </td>
                            </tr>';
                        }
                echo '</tbody>
                </table>

                <div id="product-moderation-pagination" class="pagination butigo-pagination green-pagination"></div>
                <script type="text/javascript">
                    $(function(){
                        $("#product-moderation-pagination").pagination('.$moderations['totalItem'].', {
                            prev_text: "'.$this->l("Prev").'"
                            , next_text: "'.$this->l("Next").'"
                            , items_per_page: '.$itemPerPage.'
                            , num_display_entries: 10
                            , num_edge_entries: 2
                            , current_page : '.($pageNo - 1).'
                            , callback: function(pageNo, $pagination) {
                                if (pageNo == '.$pageNo.' - 1) return;
                                var urlParams =  getQueryString();
                                urlParams["productPagination"] = parseInt(pageNo) + 1;
                                goToUrl(location.pathname + "?" + $.param(urlParams));
                                return false;
                            }
                        });

                        $("#product-moderation-form").find(".moderation-action").click(function(){
                            $("#product-moderation-action").val($(this).attr("data-action"));
                            $(this).closest("tr").find(":checkbox").attr("checked", true);
                            $(this).closest("form").submit();
                        });

                    });
                </script>
            </form>
        </div>';
    }


    public function displayFraudOrders() {
        global $currentIndex, $cookie;

        $pageNo = (Tools::getValue('fraudPagination') ? Tools::getValue('fraudPagination') : 1 );
        $itemPerPage = Configuration::get('MOD_FRAUD_ITEM_PER_PAGE');

        $moderations = FraudModerationDetail::getModerations($itemPerPage, $pageNo);

        echo '
        <div id="product-moderation-con">
            <h3>'.$this->l('Fraud Moderations').'</h3>
            <form method=POST id="fraud-moderation-form" action="'.$currentIndex.'&moderationType=fraud&token='.$this->token.'">
                <div class="table-action-buttons">
                    <input type="submit" class="button" name="cancelSelected" value="'.$this->l('Mark as fraud').'">
                    <input type="submit" class="button" name="approveSelected" value="'.$this->l('Mark as not fraud').'">
                </div>

                <input type="hidden" id="fraud-moderation-action" name="moderation_action">

                <table class="table" cellpadding="0" cellspacing="0">
                    <thead>
                        <tr class="nodrag nodrop">
                            <th>
                                <input type="checkbox" class="select-all" id="select-all-frauds">
                                <label for="select-all-frauds">Select All</label>
                            </th>
                            <th>Order</th>
                            <th>Customer</th>
                            <th>'.$this->l('Set Status').'</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>';
                        if (!sizeof($moderations['objects'])) {
                            echo '<tr><td class="center" colspan="'.sizeof($this->_list).'">'.$this->l('Not exist any fraud that waiting for approve').'</td></tr>';
                        }

                        $irow = 0;
                        foreach ($moderations['objects'] AS $tr) {
                            $id_moderation = (int)($tr['id_moderation']);
                            $id_order = (int)($tr['id_order']);
                            $iOrder = new Order($id_order);
                            $customer = new Customer($iOrder->id_customer);
                            $states = OrderState::getOrderStates((int)($cookie->id_lang));
                            echo '
                            <tr data-id-moderation="'.$id_moderation.'" '.($irow++ % 2 ? ' class="alt_row"' : '').'>
                                <td style="padding: 4px" class="center">
                                    <input type="checkbox" id="product_moderation_'.$id_moderation.'" class="cb-moderation-item" name="moderation['.$id_moderation.'][process]" value="'.$id_moderation.'"  />
                                </td>
                                <td style="padding: 4px;">
                                    <a href="index.php?tab=AdminOrders&id_order='.$id_order.'&vieworder&token='.Tools::getAdminTokenLite('AdminOrders').'">'.
                                        $tr['id_order']
                                    .'</a>
                                </td>
                                <td style="padding: 4px">
                                    <a href="index.php?tab=AdminCustomers&id_customer='.(int)$customer->id.'&viewcustomer&token='.Tools::getAdminTokenLite('AdminCustomers').'">'.
                                        $customer->firstname.' '.$customer->lastname
                                    .'</a>
                                </td>
                                <td style="width: 140px; vertical-align: top; padding: 4px 0 4px 0;">
                                    <select name="moderation['.$id_moderation.'][id_order_state]">';
                                        foreach($states as $state) {
                                            if (Configuration::get('PS_OS_PREPARATION') == $state['id_order_state']) {
                                                echo '<option value="'.$state['id_order_state'].'" selected >'.$state['name'].'</option>';
                                            } elseif (Configuration::get('PS_OS_FRAUD_ORDER') == $state['id_order_state']) {
                                                echo '<option value="'.$state['id_order_state'].'">'.$state['name'].'</option>';
                                            }
                                        }
                                    echo '
                                    </select>';
                                echo '</td>
                                <td class="action-buttons-con" style="text-align:center;">
                                    <img class="moderation-action" data-action="approve" src="../img/admin/enabled.gif" title="Mark as not fraud" class="img_approve">
                                </td>
                            </tr>';
                        }
                echo '</tbody>
                </table>

                <div id="fraud-moderation-pagination" class="pagination butigo-pagination green-pagination"></div>
                <script type="text/javascript">
                    $(function(){
                        $("#fraud-moderation-pagination").pagination('.$moderations['totalItem'].', {
                            prev_text: "'.$this->l("Prev").'"
                            , next_text: "'.$this->l("Next").'"
                            , items_per_page: '.$itemPerPage.'
                            , num_display_entries: 10
                            , num_edge_entries: 2
                            , current_page : '.($pageNo - 1).'
                            , callback: function(pageNo, $pagination) {
                                if (pageNo == '.$pageNo.' - 1) return;
                                var urlParams =  getQueryString();
                                urlParams["fraudPagination"] = parseInt(pageNo) + 1;
                                goToUrl(location.pathname + "?" + $.param(urlParams));
                                return false;
                            }
                        });
                    });

                    $("#fraud-moderation-form").find(".moderation-action").click(function(){
                        $("#fraud-moderation-action").val($(this).attr("data-action"));
                        $(this).closest("tr").find(":checkbox").attr("checked", true);
                        $(this).closest("form").submit();
                    });
                </script>
            </form>
        </div>
        ';
    }

    public function includeJSFiles() {
        return '<script type="text/javascript" src="'.__PS_BASE_URI__.'/js/jquery/jquery-ui-1.8.10.custom.min.js"></script>
            <script type="text/javascript" src="'._THEME_JS_DIR_.'pagination/jquery.pagination.js"></script>
            <script type="text/javascript">
                $(".select-all").click(function() {
                    $(this).closest("table").find(".cb-moderation-item").attr("checked", $(this).is(":checked"));
                });
            </script>';
    }

    public function includeCSSFiles() {
        return '<link type="text/css" rel="stylesheet" href="'.__PS_BASE_URI__.'/css/jquery-ui-1.8.10.custom.css">
        <link type="text/css" rel="stylesheet" href="'._THEME_JS_DIR_.'pagination/pagination.css">
        <style>
            .action-buttons-con{
                padding:4px;
            }
            .action-buttons-con img{
                cursor:pointer;
                margin:2px;
            }

            .reject-reason-select-dialog >div {
                width:100%;
                display:inline-block;
            }

            .reject-reason-select-dialog label {
                width:50px;
                text-align:left;
            }
            .reject-reason-select-dialog select{
                margin-left:15px;
            }
            .reject-reason-select-dialog textarea{
                width:300px;
                height:60px;
                margin-left:15px;
            }

            .img_reject, .img_approve {
                cursor:pointer;
                margin-left:10px;
            }
            .table-action-buttons{
                display:inline-block;
                margin-bottom:7px;
            }
            .pagination {
                width:100%;
            }

            #product-moderation-con, #order-moderation-con {
                padding-bottom:20px;
                margin-bottom:10px;
                display:inline-block;
                border-bottom:1px solid #ccc;
            }
            .select-all {
                float: left;
            }
            .select-all + label {
                width: auto;
                float: left;
                margin-left: 3px;
                margin-top: -2px;
            }

        </style>
        ';
    }

}
?>

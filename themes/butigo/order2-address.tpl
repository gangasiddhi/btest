{if isset($no_chkout_address) && !$no_chkout_address}
{* Will be deleted for 1.5 version and more *}
{if !isset($formatedAddressFieldsValuesList)}
    {$ignoreList.0 = "id_address"}
    {$ignoreList.1 = "id_country"}
    {$ignoreList.2 = "id_state"}
    {$ignoreList.3 = "id_customer"}
    {$ignoreList.4 = "id_manufacturer"}
    {$ignoreList.5 = "id_supplier"}
    {$ignoreList.6 = "date_add"}
    {$ignoreList.7 = "date_upd"}
    {$ignoreList.8 = "active"}
    {$ignoreList.9 = "deleted"}

    {* PrestaShop 1.4.0.17 compatibility *}
    {if isset($addresses)}
        {foreach from=$addresses key=k item=address}
            {counter start=0 skip=1 assign=address_key_number}
            {$id_address = $address.id_address}
            {foreach from=$address key=address_key item=address_content}
                {if !in_array($address_key, $ignoreList)}
                    {$formatedAddressFieldsValuesList.$id_address.ordered_fields.$address_key_number = $address_key}
                    {$formatedAddressFieldsValuesList.$id_address.formated_fields_values.$address_key = $address_content}
                    {counter}
                {/if}
            {/foreach}
        {/foreach}
    {/if}
{/if}

<script type="text/javascript">
/* <![CDATA[ */
    {if !$opc}
        var baseDir = '{$base_dir_ssl}';
        var orderProcess = 'order';
        var currencySign = '{$currencySign|html_entity_decode:2:"UTF-8"}';
        var currencyRate = '{$currencyRate|floatval}';
        var currencyFormat = '{$currencyFormat|intval}';
        var currencyBlank = '{$currencyBlank|intval}';
        var txtProduct = "{l s='product'}";
        var txtProducts = "{l s='products'}";
        var addAdresss = "{l s='Add Another Address'}";
    {/if}

        /*Variables defined for use in order-address.js javascript*/
        var address_edit = "{l s='Edit'}";
        var selection = '<div class="current_address">{l s='Address Selected'}</div>';
        var all_address_ids = new Array();
        {foreach from=$addresses key=k item=address name=adressId}
           all_address_ids[{$smarty.foreach.adressId.index}] = {$address.id_address|intval};
        {/foreach}
        var id_cart_delivery_address = {$cart->id_address_delivery};
        var id_cart_invoice_address = {$cart->id_address_invoice};
        var order_page_link = "{$link->getPageLInk('order.php', true)}";
        var address_delete_link = "{$link->getPageLInk('address.php', true)}";
        var address_delete_title = "{l s='Delete'}";
        var deleteConfirm = "{l s='Are you sure that you want to delete this address?'}";
        /*Variables defined for use in order-address.js javascript*/

    var formatedAddressFieldsValuesList = new Array();

    {foreach from=$formatedAddressFieldsValuesList key=id_address item=type}
        formatedAddressFieldsValuesList[{$id_address}] =
        {ldelim}
            'ordered_fields':[
                {foreach from=$type.ordered_fields key=num_field item=field_name name=inv_loop}
                    {if !$smarty.foreach.inv_loop.first},{/if}"{$field_name}"
                {/foreach}
            ],
            'formated_fields_values':{ldelim}
                    {foreach from=$type.formated_fields_values key=pattern_name item=field_name name=inv_loop}
                        {if !$smarty.foreach.inv_loop.first},{/if}"{$pattern_name}":"{$field_name}"
                    {/foreach}
                {rdelim}
        {rdelim}
    {/foreach}

    function getAddressesTitles()
    {ldelim}
        return {ldelim}
                        'invoice': "{l s='Your billing address'}"
                        , 'delivery': "{l s='Your delivery address'}"
            {rdelim};

    {rdelim}


    function buildAddressBlock(id_address, address_type, dest_comp)
    {ldelim}
        var adr_titles_vals = getAddressesTitles();
        var li_content = formatedAddressFieldsValuesList[id_address]['formated_fields_values'];
        var ordered_fields_name = ['title'];

        ordered_fields_name = ordered_fields_name.concat(formatedAddressFieldsValuesList[id_address]['ordered_fields']);
        ordered_fields_name = ordered_fields_name.concat(['update']);
                appendAddressList(dest_comp, li_content, ordered_fields_name);
    {rdelim}

    function appendAddressList(dest_comp, values, fields_name)
    {ldelim}
        for (var item in fields_name)
        {ldelim}
            var name = fields_name[item];
            var value = getFieldValue(name, values);
            if (value != "")
            {ldelim}
                var new_li = document.createElement('li');
                new_li.className = 'address_'+ name;
                new_li.innerHTML = getFieldValue(name, values);
                dest_comp.append(new_li);
            {rdelim}
        {rdelim}
    {rdelim}

    function getFieldValue(field_name, values)
    {ldelim}
        var reg=new RegExp("[ ]+", "g");

        var items = field_name.split(reg);
        var vals = new Array();

        for (var field_item in items)
            vals.push(values[items[field_item]]);
        return vals.join(" ");
    {rdelim}

/*]]>*/
</script>

    <div class="payment_addresses">
         <div class="address-type-con delivery-address-con" data-address-type="delivery" >
            <div class="address-name-con">
                <span>{l s='Delivery Address'}</span>
            </div>
            <div class="addresses_delivery" >

            </div>
        </div>
        <div id="same_as_delivery">
            <input type="checkbox" name="same" id="addressesAreEquals" value="1" {if $cart->id_address_invoice == $cart->id_address_delivery || $addresses|@count == 1}checked="checked"{/if} />
            <label id="addressSame" for="addressesAreEquals">{l s='Use same address as delivery address'}</label>
        </div>
        <div class="address-type-con invoice-address-con"  data-address-type="invoice" style="{if $cart->id_address_invoice == $cart->id_address_delivery}display:none;{/if}" >
            <div class="address-name-con">
                <span>{l s='Billing Address'}</span>
            </div>
            <div class="addresses_invoice" >

            </div>
        </div>
    </div>
    {*If it is a free order the submit address button directs to order step 3*}
    {if isset($free_order) && $free_order}
        <form action="{$link->getPageLink('order.php', true)}?step=3" method="post">
    {/if}
        <div class="cart_address">
        <input type="hidden" class="hidden" name="step" value="3" />
        {if isset($back)}
            <input type="hidden" name="back" value="{$back}" />
        {/if}
        {if isset($utm_params)}
            <input type="hidden" class="hidden" name="utm_params" value="{$utm_params}" />
        {/if}
        {if isset($carriers)}
                <input type="hidden" name="id_carrier" value="{$checked|intval}" id="id_carrier{$checked|intval}" />
        {/if}
        <input type="submit" name="processAddress" value="{l s='Submit address'}" class="buttonmedium blue" {if !isset($free_order)}id="submitaddress"{/if}/>
        <input type="submit" name="processAddress" value="" class="submit_address2" id="submitaddress2" style="display:none;"/>
    </div>
    {*If it is a free order the submit address button directs to order step 3*}
    {if isset($free_order) && $free_order}
        </form>
    {/if}
{else}
    <div class="payment_addresses">
        <div class="new-address">
            <p>
                <a class="newAdress" href="{$link->getPageLInk('address.php', true)}" title="{l s='Add a new address'}">{l s='Add a new address'}</a>
            </p>
        </div>
    </div>
{/if}

<script type="text/javascript">
    {literal}
        $(function() {
            $(".newAdress").click(function(e) {
                e.preventDefault();
                address_pop("d");
            });
        });
    {/literal}
</script>

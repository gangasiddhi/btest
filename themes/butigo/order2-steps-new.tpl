<script type="text/javascript">
/* <![CDATA[ */
    var baseDir = '{$base_dir_ssl}';
/*]]>*/
</script>

<!--[if IE 7]>
    <style type="text/css">
     {literal}
        .arrow-image-checkout-first,
        .arrow-image-checkout-first-unselected,
        .arrow-image-checkout-middle,
        .arrow-image-checkout-last {
            position: absolute;
            top: 0;
            right: 0;
            background-image: none !important;
        }
        .spc-address-first, .spc-address, .spc-payment {
            float: left;
            width: 115px;
            margin: 0px;
        }
      {/literal}
    </style>
<![endif]-->
{if $errors}
    {include file="$tpl_dir./errors.tpl"}
{/if}
<div class="spc-left-part">
    {*Steps*}
        <ul class="spc-order-steps" id="spc-order-step">
            <li class="spc-address-first" id="address_step_first">
                <a href="{$link->getPageLink('siparis')}?step=1">{l s='1. My Cart'}<span class="arrow-image-checkout-first">&nbsp;</span></a>
            </li>
            <li class="spc-address selected" id="address_step">
                <a href="#">{l s='2. Address'}<span class="{if $free_order || ($no_chkout_address == 1)} arrow-image-checkout-last {else} arrow-image-checkout-middle{/if}">&nbsp;</span></a>
            </li>
            {if isset($no_chkout_address) && !$no_chkout_address && isset($PaymentMethods)}
            <li class="spc-payment" id="payment_step">
                <a href="#">{l s='3. Payment'}<span class="arrow-image-checkout-last">&nbsp;</span></a>
            </li>
            {/if}
        </ul>


    {*Address Part*}
    <div id="address_payment">
        {include file="$tpl_dir./order2-address-new.tpl"}
    </div>

    {*Payment Part*}
    {if isset($no_chkout_address) && !$no_chkout_address}
    <div id="payment_methods"  style="display:none;">
            {if isset($PaymentMethods) } {$PaymentMethods} {/if}
    </div>
    {/if}
</div>
{*Cart Display*}
<div class="spc-right-part-address">
 {include file="$tpl_dir./shopping-cart-product-line2-new.tpl"}
</div>

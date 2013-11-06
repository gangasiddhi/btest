<script type="text/javascript">
/* <![CDATA[ */
    var baseDir = '{$base_dir_ssl}';
/*]]>*/
</script>
{if $errors}
    {include file="$tpl_dir./errors.tpl"}
{/if}
<div class="spc-left-part">
    {*Steps*}
    <div id="spc-order-steps-follow">
        <ul class="spc-order-steps" id="spc-order-step">
            <li class="spc-address selected" id="address_step">
                <a href="#"> 1.&nbsp;{l s='Address'}</a>
            </li>
            {if isset($no_chkout_address) && !$no_chkout_address && isset($PaymentMethods)}
            <li class="spc-payment" id="payment_step">
                <a href="#">2.&nbsp;{l s='Payment'}</a>
            </li>
            {/if}
        </ul>
    </div> 
    {*Address Part*}
    <div id="address_payment">  
        {include file="$tpl_dir./order2-address.tpl"}
    </div>  
       
    {*Payment Part*}
    {if isset($no_chkout_address) && !$no_chkout_address}
    <div id="payment_methods"  style="display:none;">    
            {if isset($PaymentMethods) } {$PaymentMethods} {/if}        
    </div>
    {/if}
</div>
{*Cart Display*}
<div class="spc-right-part">
 {include file="$tpl_dir./shopping-cart-product-line2.tpl"}
</div>
<div class="spc-address-footer">
{include file="$tpl_dir./cart_bottom_footer.tpl"}
</div>
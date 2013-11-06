<script type="text/javascript">
    {literal}
        function acceptTCPP(msg) {
            if (($('#agree_sales').length &&
                ! $('input#agree_sales:checked').length) ||
                ($('#agree_pre_sales').length && ! $('input#agree_pre_sales:checked').length)
            ) {
                alert(msg);
                return false;
            } else {
                if($('#payment-process').hasClass('hidden')){
                    $('#payment-process').removeClass('hidden')
                    $('#payment-checkout').hide();
                    $('#address-checkout').hide();
                    $('#payment-process').show();
                }
                if($('input[name="orderProcessing"]').hasClass('hidden')){
                    $('input[name="orderProcessing"]').removeClass('hidden');
                    $('input[name="paymentSubmit"]').addClass('hidden');
                }
                return true;
            }
        }

        /* Fancy box for Sales-Agreements & Installments popup */
        $("a.agree").fancybox({
            'autoSize' : false,
            'width' : 800,
            'height' : 400,
            'margin' : 2,
            'padding': 5,
            'titleShow': false,
            'centerOnScroll': true,
            'overlayShow': true,
            'overlayColor': '#000',
            'type': 'iframe'
        });

        $("a#show-instal-popup").fancybox({
            'autoSize': false,
            'width': 700,
            'height': 250,
            'margin': 2,
            'padding': 5,
            'titleShow': false,
            'centerOnScroll': true,
            'overlayShow':  true,
            'overlayColor': '#000'
        });
    {/literal}

    var free_shipping = parseFloat('{$free_shipping}') || 0;
    var cod_total_amount = parseFloat('{$total}') || 0;
    var order_total_without_shipping = Number('{$order_total_without_shipping}').toFixed(2);
    var dailydeal_product = parseFloat('{$dailydeal_product}') || 0;

    cod_total_amount = Number(cod_total_amount).toFixed(2);
    $('.cod_extra_shipping').removeClass('hidden');
    $('#spc-total-price').empty();
    $('#spc-total-price').append(cod_total_amount + " " + sign);

    {literal}
        $('form').submit(function(){
            if($("#agree_sales").attr('checked') == true) {
                if($('input[name="orderProcessing"]').hasClass('hidden')){
                    $('input[name="orderProcessing"]').removeClass('hidden');
                    $('input[name="paymentSubmit"]').addClass('hidden');
                }
            }
        });

        if(typeof dailydeal_product !='undefined' && dailydeal_product ==1){
            $('.cod_extra_shipping').addClass('hidden');
            $('#spc-total-price').empty();
            $('#spc-total-price').append(order_total_without_shipping + " " + sign);
        }
    {/literal}

    var tosMessage = "{l s='Please accept the terms of service before the next step.' mod='cashondelivery' js=1}";
</script>
{assign var='current_step' value='payment'}

<form action="{$this_path_ssl}validation.php" method="post"
    onsubmit="return acceptTCPP(tosMessage);">
    <div id="cod_desc" class="payment_details">
        <p>{l s='Orders with Cash on Delivery is charged an extra' mod='cashondelivery'} {$shippingChargeForCashOnDelivery} {$currencySign} {l s='for shipping. Our Customer Service representatives will call you as soon as possible to confirm the order.' mod='cashondelivery'}</p>
    </div>

    <div class="check-agree-new-checkout">
        <input type="checkbox" name="check_agree" id="agree_sales" class="sales_agreemnt" />
        <input type="hidden" id="display-installment" name="display_installment" value="0" />

        <a class="agree" id="agreement1" title="Pre Sales Agreement"
            href="{$base_dir_ssl}agreements.php?id_cms=20&content_only=1&cod=1">Ön-satış sözleşmesini</a>&nbsp;ve&nbsp;<br /><a
                class="agree checkout-agreement" id="agreement2" title="Sales Agreement"
                href="{$base_dir_ssl}agreements.php?id_cms=21&content_only=1&cod=1">satış sözleşmesini</a>&nbsp;onaylıyorum.
    </div>

    <div class ="payment_details">
        <div class ="final_price">
            {l s='Total' mod='cashondelivery'}
            <span class="cart_total_label font-pink">({l s='Tax Included' mod='cashondelivery'})</span>
            <span id="submit_total">{displayPrice price=$total}</span>
        </div>
    </div>

    <div id="submit-payment">
        <input type="hidden" name="confirm" value="1" />
        <input type="submit" name="submit" value="{l s='CheckOut' mod='cashondelivery'}"
               class="buttonmedium pink checkout" style="top:105px" id="cod-checkout-button" />
        <input type="text" name="orderProcessing" class="buttonmedium pink checkout hidden"
               readonly="readonly" value="{l s='Order Processing' mod='cashondelivery'}...." style="top:105px"/>
    </div>
</form>

{if ! $twoStepCheckout}
    <img style="margin: 200px 0 0 0;" src="{$img_dir}cart/cart-footer-black-cod.gif"
        alt="{l s='Cart Helpline' mod='cashondelivery'}" class="cart-bottom"/>
{/if}

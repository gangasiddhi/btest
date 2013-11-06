<script type="text/javascript">
/* <![CDATA[ */
	var baseDir = '{$base_dir_ssl}';
	var default_id_carrier = {$default_id_carrier};
	var cod_carrier = {$cod_carrier};
/* ]]> */
</script>
{if isset($two_page_checkout) && !$two_page_checkout}
{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}
{/if}
<div class="hidden">
    <script type="text/javascript">
        var totalPrice = {$total_price};
        totalPrice = totalPrice.toFixed(2);
        var sign = '{$currency_type}';
    </script>
</div>
<div id="payment_buttons" class="payment-details-checkout">
{*Providing payment options to customers*}
<input type="submit" name="payment_option" id="default_payment_option" class="payment-options not_selected_1" value="{l s='Credit Card' mod='mediator'}"/>
<input type="submit" name="payment_option" id="cod" class="payment-options not_selected_2" value="{l s='Cash on Delivery' mod='mediator'}"/>
</div>
{*According to the payment option selected by the customer, content is loaded via ajax into the div *}
<div id="payment_page">
</div>

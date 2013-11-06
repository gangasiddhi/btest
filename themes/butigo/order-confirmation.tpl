<script type="text/javascript">
	//<![CDATA[
		var baseDir = '{$base_dir_ssl}';
	//]]>
	{*$('#shopping-cart-top a').removeClass('check-out');
	$('.ajax_cart_product_txt').addClass('collapsed');
	$('.ajax_cart_quantity').addClass('collapsed');
	$('.ajax_cart_no_product').removeClass('collapsed');
	$('.ajax_cart_product_txt_s').addClass('collapsed');
        $('.ajax_cart_closure').addClass('collapsed');*}
	$('.ajax_cart_quantity').empty().append('0');
</script>

{*capture name=path}{l s='Order confirmation'}{/capture}
{include file="$tpl_dir./breadcrumb.tpl"}

<h1>{l s='Order confirmation'}</h1>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

{include file="$tpl_dir./errors.tpl"*}

{$HOOK_ORDER_CONFIRMATION}
{$HOOK_PAYMENT_RETURN}

{*<br />
{if $is_guest}
	<p>{l s='Your order ID is:'} <span class="bold">{$id_order_formatted}</span> . {l s='Your order ID has been sent to your e-mail.'}</p>
	<a href="{$link->getPageLink('guest-tracking.php', true)}?id_order={$id_order}" title="{l s='Follow my order'}"><img src="{$img_dir}icon/order.gif" alt="{l s='Follow my order'}" class="icon" /></a>
	<a href="{$link->getPageLink('guest-tracking.php', true)}?id_order={$id_order}" title="{l s='Follow my order'}">{l s='Follow my order'}</a>
{else}
	<a href="{$link->getPageLink('history.php', true)}" title="{l s='Back to orders'}"><img src="{$img_dir}icon/order.gif" alt="{l s='Back to orders'}" class="icon" /></a>
	<a href="{$link->getPageLink('history.php', true)}" title="{l s='Back to orders'}">{l s='Back to orders'}</a>
{/if}*}

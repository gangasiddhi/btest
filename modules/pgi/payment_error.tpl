{assign var='current_step' value='payment_error'}
{include file=$tpl_dir./order-steps.tpl}
<div class="shopping-cart">
<div class="errorContainer">
	<img src="{$img_dir}cart/shopping problem.gif" alt="{l s='Payment error message' mod='pgi'}"/>
	<a href="{$link->getPageLink('order.php',true)}?step=3{if isset($back) && $back}&amp;back={$back}{/if}" class="buttonsmall pink" title="{l s='Back To Payment' mod='pgg'}">
		{l s='Back to overview to pay'}
	</a>
</div>
{include file="$tpl_dir./cart_bottom_footer.tpl"}
</div>

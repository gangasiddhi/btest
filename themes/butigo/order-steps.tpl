{* Assign a value to 'current_step' to display current style *}

{if !$opc}
{* Order Steps *}
<div id="order_steps-follow">
	<ul class="order_steps" id="order_step">
		<li class="{if $current_step=='summary'}step_start_current{else}{if $current_step=='payment' || $current_step=='address'}step_done{else}step_todo{/if}{/if} step_start">
			{if $current_step=='payment' || $current_step=='shipping' || $current_step=='address' || $current_step=='login'}
			<a href="{$link->getPageLink('order.php', true)}?step=1{if isset($back) && $back}?back={$back}{/if}">
				<img src="{$img_dir}cart/cart.gif" alt="{l s='Summary'}"/>
			</a>
			{else}
				<img src="{$img_dir}cart/cart.gif" alt="{l s='Summary'}"/>
			{/if}
		</li>
		{*<li class="{if $current_step=='login'}step_current{else}{if $current_step=='payment' || $current_step=='shipping' || $current_step=='address'}step_done{else}step_todo{/if}{/if}">
			{if $current_step=='payment' || $current_step=='shipping' || $current_step=='address'}
			<a href="{$link->getPageLink('order.php', true)}?step=1{if isset($back) && $back}&amp;back={$back}{/if}">
				{l s='Login'}
			</a>
			{else}
				{l s='Login'}
			{/if}
		</li>*}
		<li class="{if $current_step=='address'}step_current{else}{if $current_step=='payment'}step_done{else}step_todo{/if}{/if}">
			{if $current_step=='payment' || $current_step=='shipping'}
			<a href="{$link->getPageLink('order.php', true)}?step=2{if isset($back) && $back}&amp;back={$back}{/if}">
				<img src="{$img_dir}cart/address.gif" alt="{l s='Address'}"/>
			</a>
			{else}
				<img src="{$img_dir}cart/address.gif" alt="{l s='Address'}"/>
			{/if}
		</li>
		<li class="{if $current_step=='payment' || $current_step=='payment_error'}step_current{else}{if $current_step=='confirmation'}step_done{else}step_todo{/if}{/if}">
			{if $current_step=='payment_error'}
			<a href="{$link->getPageLink('order.php', true)}?step=3{if isset($back) && $back}&amp;back={$back}{/if}">
				<img src="{$img_dir}cart/payment.gif" alt="{l s='Payment'}"/>
			</a>
			{else}
				<img src="{$img_dir}cart/payment.gif" alt="{l s='Payment'}"/>
			{/if}
		</li>
		<li class="{if $current_step=='confirmation'}step_end_current{else}step_todo{/if} step_end">
			<img src="{$img_dir}cart/complete.gif" alt="{l s='Complete'}"/>
		</li>
	</ul>
</div>
{* Order Steps *}
{/if}
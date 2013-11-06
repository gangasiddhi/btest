{* Block user information module HEADER*}

{if $logged}
<div id="header-info">
<div id="user-info">
    <span>{l s='Musteri Mutluluk Ekibi : (216) 418 26 26' mod='blockuserinfo'}&#44;&nbsp;{l s='destek@butigo.com' mod='blockuserinfo'}</span>
</div>{*end of logout*}
<div id="my-acc-cart">
    {*<div id="invite-friends-link">
        <a href="{$link->getPageLink('referrals-friends.php')}" title="{l s='Invite Friends' mod='blockuserinfo'}">{l s='Invite Friends' mod='blockuserinfo'}</a>
    </div>*}
	{*<div {if $page_name == 'identity' || $page_name == 'referrals-stylepoints' || $page_name == 'addresses' || $page_name == 'history'}class="current"{/if} id="identity-menu" onmouseover="$('div#my-acc').show()" onmouseout="$('div#my-acc').hide()">
		<a href="#" class="identity" title="{l s='HESABIM' mod='blockuserinfo'}">{l s='HESABIM' mod='blockuserinfo'}</a>
		<div id="my-acc">
			<ul>
				<li {if $page_name == 'identity'}class="current"{/if}><a href="{$link->getPageLink('identity.php', true)}" class="identity" title="{l s='Membership Information' mod='blockuserinfo'}">{l s='Membership Information' mod='blockuserinfo'}</a></li>
				<li {if $page_name == 'referrals-stylepoints'}class="current"{/if}><a href="{$link->getPageLink('referrals-stylepoints.php')}" class="style_points" title="{l s='Style Points' mod='blockuserinfo'}">{l s='Style Points' mod='blockuserinfo'}</a></li>
				<li {if $page_name == 'voucher'}class="current"{/if}><a href="{$link->getPageLink('vouchers.php')}" class="vouchers" title="{l s='My Vouchers' mod='blockuserinfo'}">{l s='My Vouchers' mod='blockuserinfo'}</a></li>
				<li {if $page_name == 'addresses' || $page_name == 'address'}class="current"{/if}><a href="{$link->getPageLink('addresses.php', true)}" class="addresses" title="{l s='Shipping Addresses' mod='blockuserinfo'}">{l s='Shipping Addresses' mod='blockuserinfo'}</a></li>
				<li {if $page_name == 'history'}class="current"{/if}><a href="{$link->getPageLink('history.php', true)}" class="history" title="{l s='Order History' mod='blockuserinfo'}">{l s='Order History' mod='blockuserinfo'}</a></li>
				{if isset($is_my_fav_active) && $is_my_fav_active}
					<li {if $page_name == 'my-favourites'}class="current"{/if}><a href="{$link->getPageLink('my-favourites.php')}" class="my-favourites" title="{l s='My Favourites' mod='blockuserinfo'}">{l s='My Favourites' mod='blockuserinfo'}</a></li>
				{/if}
				<li><a href="{$link->getPageLink('index.php')}?mylogout" title="{l s='Log out' mod='blockuserinfo'}">{l s='Log out' mod='blockuserinfo'}</a><li>
			</ul>
		</div>
	</div>*}

	<div id="shopping-cart-top">
        {if $cart_qties == 0}
            <span class="ajax_cart_no_product{*if $cart_qties > 0} collapsed{/if*}" id="shopping-cart-top-span">{l s='BAG' mod='blockuserinfo'} (0)</span>
        {else}
            {*<img src="{$img_dir}buttons/cart.png" alt="{l s='Your Shopping Cart' mod='blockuserinfo'}" />*}
            <a id ="shopping-cart-top-link" {*if $cart_qties == 0} class="collapsed" {else} class="check-out"{/if*} href="{$link->getPageLink('order.php')}?step=1" title="{l s='Your Shopping Cart' mod='blockuserinfo'}">
                {*<span class="ajax_cart_quantity{if $cart_qties == 0} collapsed{/if}">{$cart_qties}</span>*}
                <span class="ajax_cart_product_txt{*if $cart_qties != 1} collapsed{/if*}">{l s='BAG' mod='blockuserinfo'}</span>
                <span class="ajax_cart_closure{*if $cart_qties == 0} collapsed{/if*}">(</span><span class="ajax_cart_quantity{*if $cart_qties == 0} collapsed{/if*}">{$cart_qties}</span><span class="ajax_cart_closure{*if $cart_qties == 0} collapsed{/if*}">)</span>
                {*<span class="ajax_cart_product_txt_s{if $cart_qties < 2} collapsed{/if}">{l s='products' mod='blockuserinfo'}</span>*}
            </a>
        {/if}
	</div>{*end of shopping-cart-top*}
</div>
{/if}

{*<div id="header_user">
	<p id="header_user_info">
		{l s='Welcome' mod='blockuserinfo'},
		{if $cookie->isLogged()}
			<span>{$cookie->customer_firstname} {$cookie->customer_lastname}</span>
			(<a href="{$link->getPageLink('index.php')}?mylogout" title="{l s='Log me out' mod='blockuserinfo'}">{l s='Log out' mod='blockuserinfo'}</a>)
		{else}
			<a href="{$link->getPageLink('my-account.php', true)}">{l s='Log in' mod='blockuserinfo'}</a>
		{/if}
	</p>
	<ul id="header_nav">
		{if !$PS_CATALOG_MODE}
		<li id="shopping_cart">
			<a href="{$link->getPageLink("$order_process.php", true)}" title="{l s='Your Shopping Cart' mod='blockuserinfo'}">{l s='Cart:' mod='blockuserinfo'}</a>
			<span class="ajax_cart_quantity{if $cart_qties == 0} hidden{/if}">{$cart_qties}</span>
			<span class="ajax_cart_product_txt{if $cart_qties != 1} hidden{/if}">{l s='product' mod='blockuserinfo'}</span>
			<span class="ajax_cart_product_txt_s{if $cart_qties < 2} hidden{/if}">{l s='products' mod='blockuserinfo'}</span>
			{if $cart_qties >= 0}
				<span class="ajax_cart_total{if $cart_qties == 0} hidden{/if}">
					{if $priceDisplay == 1}
						{assign var='blockuser_cart_flag' value='Cart::BOTH_WITHOUT_SHIPPING'|constant}
						{convertPrice price=$cart->getOrderTotal(false, $blockuser_cart_flag)}
					{else}
						{assign var='blockuser_cart_flag' value='Cart::BOTH_WITHOUT_SHIPPING'|constant}
						{convertPrice price=$cart->getOrderTotal(true, $blockuser_cart_flag)}
					{/if}
				</span>
			{/if}
			<span class="ajax_cart_no_product{if $cart_qties > 0} hidden{/if}">{l s='(empty)' mod='blockuserinfo'}</span>
		</li>
		{/if}
		<li id="your_account"><a href="{$link->getPageLink('my-account.php', true)}" title="{l s='Your Account' mod='blockuserinfo'}">{l s='Your Account' mod='blockuserinfo'}</a></li>
	</ul>
</div>*}

{* /Block user information module HEADER *}


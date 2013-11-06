<!--[if IE 7]>
    <style type="text/css">
     {literal}
        #title-products,
        .product-name-image {
            width: 50%;
        }
        .product-name,
        .product-attr {
            float: left;
            font-weight: bold;
            width: 54%;
        }
      {/literal}
    </style>
<![endif]-->

{*<h1 id="cart_title">{l s='Shopping cart summary'}</h1>*}

{assign var='current_step' value='summary'}
{*include file="$tpl_dir./errors.tpl"*}

{if isset($empty)}
<div id="cart_empty">
    <img id="cart_empty_image" src="{$img_dir}cart/empty-cart-message.gif" alt="{l s='Delete'}" width="380" height="34" /><br />
	<a href="{*if (isset($smarty.server.HTTP_REFERER) && strstr($smarty.server.HTTP_REFERER, $link->getPageLink('order.php'))) || !isset($smarty.server.HTTP_REFERER)*}{$link->getPageLink('showroom.php')}{*else}{$smarty.server.HTTP_REFERER|escape:'htmlall':'UTF-8'|secureReferrer}{/if*}" class="buttonmedium blue checkout-empty-cart" title="{l s='Continue shopping'}">{l s='Continue shopping'}</a>
</div>
{elseif $PS_CATALOG_MODE}
<p class="warning">{l s='This store has not accepted your new order.'}</p>
{else}
<script type="text/javascript">
	/* <![CDATA[ */
	var baseDir = '{$base_dir_ssl}';
	var currencySign = '{$currencySign|html_entity_decode:2:"UTF-8"}';
	var currencyRate = '{$currencyRate|floatval}';
	var currencyFormat = '{$currencyFormat|intval}';
	var currencyBlank = '{$currencyBlank|intval}';
	var txtProduct = "{l s='product'}";
	var txtProducts = "{l s='products'}";
    var accessoryErrorMessage = "{l s='You will get Discounts, Please add the accessories : '}";
	/* ]]> */
</script>

{if $bu_env=='production'}
<div class="hidden">
    {* NanoInteractive Integration *}
    <script type="text/javascript">
        {*{foreach from=$products item=product name=productLoop}
            productIds.push({$product.id_product});
        {/foreach}*}

        {literal}
            (function(d){
                var HEIAS_PARAMS = [];
                HEIAS_PARAMS.push(['type', 'ppx'], ['ssl', 'auto'], ['n', '6451'], ['cus', '17201']);
                HEIAS_PARAMS.push(['pb', '1']);

				HEIAS_PARAMS.push(['order_article',{/literal}'{foreach name=productLoop item=product from=$products}{$product.id_product}{if ! $smarty.foreach.productLoop.last},{/if}{/foreach}'{literal}]);

                if (typeof window.HEIAS === 'undefined') { window.HEIAS = []; }
                window.HEIAS.push(HEIAS_PARAMS);

                var scr = d.createElement('script');
                scr.async = true;
                scr.src = (d.location.protocol === 'https:' ? 'https:' : 'http:') + '//ads.heias.com/x/heias.async/p.min.js';
                var elem = d.getElementsByTagName('script')[0];
                elem.parentNode.insertBefore(scr, elem);
            }(document));
        {/literal}
    </script>

	{* Google Remarketing Code for Added Product to Cart *}
	<script type="text/javascript">
	{literal}
		/* <![CDATA[ */
		var google_conversion_id = 1009336416;
		var google_conversion_language = "en";
		var google_conversion_format = "3";
		var google_conversion_color = "ffffff";
		var google_conversion_label = "xWrVCPjk1QIQ4ICl4QM";
		var google_conversion_value = 0;
		/* ]]> */
	{/literal}
	</script>
	<script type="text/javascript" src="https://www.googleadservices.com/pagead/conversion.js"></script>
	<noscript>
		<div style="display:inline;">
			<img height="1" width="1" style="border-style:none;" alt="" src="https://www.googleadservices.com/pagead/conversion/1009336416/?label=xWrVCPjk1QIQ4ICl4QM&amp;guid=ON&amp;script=0"/>
		</div>
	</noscript>
</div>
{/if}

<div id="cart_empty" style="display:none">
    <img id="cart_empty_image" src="{$img_dir}cart/empty-cart-message.gif" alt="{l s='Delete'}" width="380" height="34" /><br />
	<a href="{*if (isset($smarty.server.HTTP_REFERER) && strstr($smarty.server.HTTP_REFERER, $link->getPageLink('order.php'))) || !isset($smarty.server.HTTP_REFERER)*}{$link->getPageLink('showroom.php')}{*else}{$smarty.server.HTTP_REFERER|escape:'htmlall':'UTF-8'|secureReferrer}{/if*}" class="buttonmedium blue checkout-empty-cart" title="{l s='Continue shopping'}">{l s='Continue shopping'}</a>
</div>

<div id="center_column">
<div id="cart-container">
    <div id="cart-details-title">
        <div id="title-products"><span>{l s='Product'}</span></div>
        <div id="title-unit-price">{l s='Unit Price'}</div>
        <div id="title-quantity">{l s='Quantity'}</div>
        <div id="title-total-price"><span>{l s='Total Price'}</span></div>
    </div>
    {foreach from=$products item=product name=productLoop}
        {assign var='productId' value=$product.id_product}
        {assign var='productAttributeId' value=$product.id_product_attribute}
        {assign var='quantityDisplayed' value=0}
        <div class="product-container-outer{if $product.stock_quantity == 0} pink-background{/if} {if $smarty.foreach.productLoop.last}last_item{elseif $smarty.foreach.productLoop.first}first_item{/if}">
            <div class="product-name-image">
                <div class="product-image">
                    <a href="{$link->getProductLink($product.id_product, $ipa, $product.link_rewrite, $product.category)|escape:'htmlall':'UTF-8'}">
                        <img src="{$link->getImageLink($product.link_rewrite, $product.id_image, 'home')}" alt="{$product.name|escape:'htmlall':'UTF-8'}" />
                    </a>
                </div>
                <div class="product-name">
                    <a href="{$link->getProductLink($product.id_product, $ipa, $product.link_rewrite, $product.category)|escape:'htmlall':'UTF-8'}">
                    {$product.name|escape:'htmlall':'UTF-8'}
                    </a>
                </div>
                <div class="product-attr">
                    {if $product.attributes}
                        <span>{$product.attributes|escape:'htmlall':'UTF-8'|truncate:22:"":true}</span>
                    {/if}
                </div>
			{if $product.stock_quantity == 0}
				<span class="error-message-new-checkout">{l s='This product has recently been sold out. Please remove it from your cart.'}</span>
			{elseif $product.quantity > $product.stock_quantity}
				<span class="error-message-new-checkout">{l s='The number of products you\'d like to buy is beyond our stock.'}</span>
            {elseif isset($product.out_of_stock) && $product.out_of_stock == 1}
                <span class="error-message-new-checkout">{l s='This product is a pre-order will be sent within 10 days of average.'}</span>
			{/if}
            </div>

            <div class="product-unit-price">
                <span id="total_product_price_{$product.id_product}_{$product.id_product_attribute}">
                {if !$priceDisplay}{convertPrice price=$product.price_wt}{else}{convertPrice price=$product.price}{/if}
                </span>
            </div>
            <div class="product-quantity">
                <form action="{$link->getPageLink('cart.php', true)}" method="post">
                    <input class="qty-vary" size="2" id="quantity-vary_{$product.id_product|intval}" type="text" value="{if $quantityDisplayed == 0 AND isset($customizedDatas.$productId.$productAttributeId)}{$customizedDatas.$productId.$productAttributeId|@count}{else}{$product.cart_quantity-$quantityDisplayed}{/if}" name="entered_qty"/>
                    <input type="hidden" id="current_qty_{$product.id_product|intval}" name="current_qty" value="{if $quantityDisplayed == 0 AND isset($customizedDatas.$productId.$productAttributeId)}{$customizedDatas.$productId.$productAttributeId|@count}{else}{$product.cart_quantity-$quantityDisplayed}{/if}"/>
                    <input type="hidden" name="qty" value=""/>
                    <input type="hidden" name="add" value="1"/>
                    <input type="hidden" name="op" value=""/>
                    <input type="hidden" name="id_product" value="{$product.id_product|intval}"/>
                    <input type="hidden" name="ipa" value="{$product.id_product_attribute|intval}"/>
                    <input type="hidden" name="id_customization" value="{$id_customization}"/>
                    <input type="hidden" name="token" value="{$token_cart}"/>
                    <input type="submit" value="{l s='Refresh'}" rel="nofollow" class="qty-refresh" id="{$product.id_product}_{$product.id_product_attribute}_{$id_customization}"/>
                </form>
                <div class="qty-remove-outer">
                    <a rel="nofollow" class="qty-remove" id="{$product.id_product}_{$product.id_product_attribute}_{$id_customization}" href="{$link->getPageLink('cart.php', true)}?delete&amp;id_product={$product.id_product|intval}&amp;ipa={$product.id_product_attribute|intval}&amp;id_customization={$id_customization}&amp;token={$token_cart}"><span></span>{l s='Remove'}</a>
                </div>
                {*<a href="#" class="qty-favourite">{l s='Move to Favourites'}</a>*}
            </div>
            <div class="product-total-price">
                <span id="total_product_price_{$product.id_product}_{$product.id_product_attribute}">
                {if $quantityDisplayed == 0 AND isset($customizedDatas.$productId.$productAttributeId)}
                    {if !$priceDisplay}{displayPrice price=$product.total_customization_wt}{else}{displayPrice price=$product.total_customization}{/if}
                {else}
                    {if !$priceDisplay}{displayPrice price=$product.total_wt}{else}{displayPrice price=$product.total}{/if}
                {/if}
                </span>
            </div>
        </div>
    {/foreach}
    {if sizeof($discounts)}
        <div class="cart-discount-container-outer">
        {foreach from=$discounts item=discount name=discountLoop}
            {*<div class="cart-discount-details {if $smarty.foreach.discountLoop.last}last_item{elseif $smarty.foreach.discountLoop.first}first_item{else}item{/if}" id="cart_discount_{$discount.id_discount}">
            *}{if $show_first_cart_discount && $discount.id_discount_type == 5 && $discount.name == $first_cart_discount_name}
                <div class="cart-discount-desc">
                    <span>
                        {l s='Sürpriz! Merhaba hediyesi olarak önümüzdeki yapacağın ilk alışveriş sana'}
                        {if $discount.value_real > 0}
                            <strong style="color:inherit">{if !$priceDisplay}{displayPrice price=$discount.value_real}{else}{displayPrice price=$discount.value_tax_exc}{/if}</strong>
                        {/if}
                        {l s='indirimli. Güle güle kullan.'}
                    </span>
                </div>
                <div class="cart-discount-del"></div>
                <div class="cart-discount-value">
                {if $discount.value_real > 0}
                    <span class="price-discount">
                        {if !$priceDisplay}{displayPrice price=$discount.value_real*-1}{else}{displayPrice price=$discount.value_tax_exc*-1}{/if}
                    </span>
                {/if}
                </div>
            {/if}
            {if $discount.id_discount_type != 5 && $discount.quantity != 0}
                <div class="cart-discount-desc">
                    <span>{$discount.description}</span>
                </div>
                <div class="cart-discount-del">
                    <a rel="nofollow" class="qty-remove" id="{$product.id_product}_{$product.id_product_attribute}_{$id_customization}" href="{if $opc}{$link->getPageLink('order-opc.php', true)}{else}{$link->getPageLink('order.php', true)}{/if}?deleteDiscount={$discount.id_discount}"><span></span>{l s='Remove'}</a>
                </div>
                <div class="cart-discount-value">
                    <span {if $discount.id_discount_type == 4}id="credit_discount"{/if} {if $discount.id_discount_type == 6}id="buy1_get1_discount"{/if} class="price-discount">
                        {if  $discount.id_discount_type != 6}
                            {if $discount.value_real > 0 }
                                {if !$priceDisplay}{displayPrice price=$discount.value_real*-1}{else}{displayPrice price=$discount.value_tax_exc*-1}{/if}
                            {/if}
                        {else}
                            {if !$priceDisplay}{displayPrice price=$discount.value_real*-1}{else}{displayPrice price=$discount.value_tax_exc*-1}{/if}
                        {/if}
                    </span>
                </div>
            {/if}
            {*</div>*}
        {/foreach}
        </div>
    {/if}
    <div id="discount-container-outer">
        <div id="discount-container-inner">
            <span id="discount-label">{l s='Discount Code'}</span>
            <form action="{if $opc}{$link->getPageLink('order-opc.php', true)}{else}{$link->getPageLink('order.php', true)}{/if}" method="post">
                <input type="text" value="" id="discount-input" name="discount_name"/>
                <input type="hidden" name="submitDiscount">
                <input type="submit" id="discount-submit" value="{l s='Send'}" name="submitAddDiscount">
            </form>
        </div>
    </div>

    <form action="{$link->getPageLink('order.php',true)}?step=2" method="post">
        <div class="cart_navigation">
            {if !$show_site}
                <a href="{*if (isset($smarty.server.HTTP_REFERER) && strstr($smarty.server.HTTP_REFERER, $link->getPageLink('order.php'))) || !isset($smarty.server.HTTP_REFERER)*}{$link->getPageLink('showroom.php')}{*else}{$smarty.server.HTTP_REFERER|escape:'htmlall':'UTF-8'|secureReferrer}{/if*}"  class="continue-shopping" style="float: left" id="backaddress" title="{l s='Continue shopping'}">{l s='Continue shopping'}</a>
                <a href="{*if (isset($smarty.server.HTTP_REFERER) && strstr($smarty.server.HTTP_REFERER, $link->getPageLink('order.php'))) || !isset($smarty.server.HTTP_REFERER)*}{$link->getPageLink('showroom.php')}{*else}{$smarty.server.HTTP_REFERER|escape:'htmlall':'UTF-8'|secureReferrer}{/if*}" class="back_showroom" id="backaddress2" title="{l s='Continue shopping'}" style="display:none;"></a>
            {else}
                <a href="{*if (isset($smarty.server.HTTP_REFERER) && strstr($smarty.server.HTTP_REFERER, $link->getPageLink('order.php'))) || !isset($smarty.server.HTTP_REFERER)*}{$link->getPageLink('lookbook.php')}{*else}{$smarty.server.HTTP_REFERER|escape:'htmlall':'UTF-8'|secureReferrer}{/if*}"  class="continue-shopping" style="float: left" id="backaddress" title="{l s='Continue'}">{l s='Continue shopping'}</a>
                <a href="{*if (isset($smarty.server.HTTP_REFERER) && strstr($smarty.server.HTTP_REFERER, $link->getPageLink('order.php'))) || !isset($smarty.server.HTTP_REFERER)*}{$link->getPageLink('lookbook.php')}{*else}{$smarty.server.HTTP_REFERER|escape:'htmlall':'UTF-8'|secureReferrer}{/if*}" class="back_showroom" id="backaddress2" title="{l s='Continue'}" style="display:none;"></a>
            {/if}
            {*<a href="{$link->getPageLink('showroom.php',true)}" class="btn_cont_shopping" title="{l s='Continue shopping'}"></a>*}
            <input type="hidden" name="numberOfAccessorisedProducts" value="{$numberOfAccessorisedProducts}"/>
                        <input type="hidden" name="numberOfAccessories" value="{$numberOfAccessories}"/>
                        <input type="hidden" class="hidden" name="step" value="2" />
            <input type="submit" name="processAddress" value="" class="place order" id="placeorderbtn2" style="display:none;"/>
        </div>
    </form>
</div>

{*0RDER SUMMARY -- NEW CHECKOUT PAGE*}
<div class="spc-right-part">
    {include file="$tpl_dir./shopping-cart-product-line2-new.tpl"}
</div>
</div>

{/if}
{if $bu_env=='production' || $bu_env=='development'}
{if isset($customer_join_month) && isset($customer_join_year)}
<div class="hidden">
	{* GA - grouping of customers *}
	<script type="text/javascript">
		_gaq.push(['_setCustomVar', 2, 'Customer Join Month', '{$customer_join_month}', 1]);
		_gaq.push(['_setCustomVar', 3, 'Customer Join Year', '{$customer_join_year}', 1]);
	</script>
</div>
{/if}
{/if}
<script type="text/javascript">
	{literal}
    var qty_not_null = "{/literal}{l s='Quantity must not be empty' js=1}{literal}";
    var qty_are_same = "{/literal}{l s='You already have the same quantity in cart' js=1}{literal}";
	$(function() {
		$('#placeorderbtn').click(function(e) {
			if ($('.passive-product').length) {
				e.preventDefault();
				alert("{/literal}{l s='Some products in your cart are out of stock. Please modify your order first and try again.'}{literal}");
			}
		});
		$('.right-arrow-passive').click(function() {
			alert("{/literal}{l s='The number of products you\'d like to buy is beyond our stock.' js=1}{literal}");
		});
    });
	{/literal}
</script>
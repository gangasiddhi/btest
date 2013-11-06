<tr id="product_{$product.id_product}_{$product.id_product_attribute}"  class="product-item {if $smarty.foreach.productLoop.last}last_item{elseif $smarty.foreach.productLoop.first}first_item{/if}{if isset($customizedDatas.$productId.$productAttributeId) AND $quantityDisplayed == 0}alternate_item{/if} cart_item {if $product.stock_quantity == 0 OR $product.quantity>$product.stock_quantity} passive-product{/if}{if $product.active == 0} disabled-product{/if}">
	<td class="prod_delete">
		{if !isset($customizedDatas.$productId.$productAttributeId) OR $quantityDisplayed > 0}
		<a rel="nofollow" class="cart_quantity_delete" id="{$product.id_product}_{$product.id_product_attribute}" href="{$link->getPageLink('cart.php', true)}?delete&amp;id_product={$product.id_product|intval}&amp;ipa={$product.id_product_attribute|intval}&amp;token={$token_cart}" title="{l s='Delete'}">
			<img src="{$img_dir}cart/delete.gif" alt="{l s='Delete'}" class="icon" width="9" height="7" />
		</a>
		{/if}
	</td>
	{if $product.id_color_default == 2}
		{assign var=ipa value= $product.default_ipa_only}
	{else}
		{assign var=ipa value=0}
	{/if}
	{*Replace all 'NULL' with $ipa in getProductLink() , when multiple color combination with single product_id is used*}
	<td class="cart_product">
		<div class = "product_image">
			<a href="{$link->getProductLink($product.id_product, NULL, $product.link_rewrite, $product.category)|escape:'htmlall':'UTF-8'}"><img src="{$link->getImageLink($product.link_rewrite, $product.id_image, 'prodthumb')}" alt="{$product.name|escape:'htmlall':'UTF-8'}" /></a>
		</div>
		<div class="about-product">
			<div class="info-con">
				<h5 class="cart_product_name"><a href="{$link->getProductLink($product.id_product, NULL, $product.link_rewrite, $product.category)|escape:'htmlall':'UTF-8'}">{$product.name|escape:'htmlall':'UTF-8'}</a></h5>
				{if $product.attributes}<a href="{$link->getProductLink($product.id_product, NULL, $product.link_rewrite, $product.category)|escape:'htmlall':'UTF-8'}"><span style="font-size:0.85em">{$product.attributes|escape:'htmlall':'UTF-8'}</span></a>{/if}
			</div>
			{if $product.stock_quantity == 0}
				<span class="error-message">{l s='This product has recently been sold out. Please remove it from your cart.'}</span>
			{elseif $product.quantity > $product.stock_quantity}
				<span class="error-message">{l s='The number of products you\'d like to buy is beyond our stock.'}</span>
            {elseif isset($product.out_of_stock) && $product.out_of_stock == 1}
                <span class="error-message pre-order-error-message">{l s='This product is a pre-order will be sent within 10 days of average.'}</span>
			{elseif $product.active == 0}
				<span class="error-message">{l s='This product is no longer being sold.'}</span>
			{/if}
		</div>
	</td>
	<td class="cart_quantity"{if isset($customizedDatas.$productId.$productAttributeId) AND $quantityDisplayed == 0} style="text-align: center;"{/if}>
		{if isset($customizedDatas.$productId.$productAttributeId) AND $quantityDisplayed == 0}<span id="cart_quantity_custom_{$product.id_product}_{$product.id_product_attribute}" >{$product.customizationQuantityTotal}</span>{/if}
		{if !isset($customizedDatas.$productId.$productAttributeId) OR $quantityDisplayed > 0}
			{if ! $product.isAccessoriesRelatedProduct}
				{if $product.minimal_quantity < $product.cart_quantity}
					<a rel="nofollow" class="cart_quantity_down" id="cart_quantity_down_{$product.id_product}_{$product.id_product_attribute}" href="{$link->getPageLink('cart.php', true)}?add&amp;id_product={$product.id_product|intval}&amp;ipa={$product.id_product_attribute|intval}&amp;op=down&amp;token={$token_cart}" title="{l s='Subtract'}">
						<i class="mini-arrow left-arrow"></i>
					</a>
				{else}
					<i class="mini-arrow left-arrow-passive"></i>
				{/if}
			{/if}

			<div class="quantity_cart"{if $product.isAccessoriesRelatedProduct} style="margin-left: 12px"{/if}>
				<input type="hidden" value="{if $quantityDisplayed == 0 AND isset($customizedDatas.$productId.$productAttributeId)}{$customizedDatas.$productId.$productAttributeId|@count}{else}{$product.cart_quantity-$quantityDisplayed}{/if}" name="quantity_{$product.id_product}_{$product.id_product_attribute}_hidden" />
				<input size="2" type="text" class="cart_quantity_input" value="{if $quantityDisplayed == 0 AND isset($customizedDatas.$productId.$productAttributeId)}{$customizedDatas.$productId.$productAttributeId|@count}{else}{$product.cart_quantity-$quantityDisplayed}{/if}"  name="quantity_{$product.id_product}_{$product.id_product_attribute}" readonly="readonly" />
			</div>

			{if ! $product.isAccessoriesRelatedProduct}
				{if $product.quantity < $product.stock_quantity}
					<a rel="nofollow" class="cart_quantity_up" id="cart_quantity_up_{$product.id_product}_{$product.id_product_attribute}" href="{$link->getPageLink('cart.php', true)}?add&amp;id_product={$product.id_product|intval}&amp;ipa={$product.id_product_attribute|intval}&amp;token={$token_cart}" title="{l s='Add'}">
						<i class="mini-arrow right-arrow"></i>
					</a>
				{else}
					<i class="mini-arrow right-arrow-passive"></i>
				{/if}
			{/if}

			<br />
		{/if}
	</td>
	<td class="cart_unit">
		<span class="cart-price" id="product_price_{$product.id_product}_{$product.id_product_attribute}">
			{if !$priceDisplay}{convertPrice price=$product.price_wt}{else}{convertPrice price=$product.price}{/if}
		</span>
	</td>
	<td class="cart_total">
		<span class="cart-price" id="total_product_price_{$product.id_product}_{$product.id_product_attribute}">
			{if $quantityDisplayed == 0 AND isset($customizedDatas.$productId.$productAttributeId)}
				{if !$priceDisplay}{displayPrice price=$product.total_customization_wt}{else}{displayPrice price=$product.total_customization}{/if}
			{else}
				{if !$priceDisplay}{displayPrice price=$product.total_wt}{else}{displayPrice price=$product.total}{/if}
			{/if}
		</span>
	</td>
</tr>

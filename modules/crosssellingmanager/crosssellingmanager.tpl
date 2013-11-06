{if isset($orderProducts) && count($orderProducts)}
<div id="cross-selling-manager-block">	
	<script type="text/javascript" src="{$module_dir}js/crosssellingmanager.js"></script>
	<h2>{l s='You Might Also Like This :' mod='crosssellingmanager'}</h2>
	<div id="cross-selling-manager-carousel">
		{foreach from=$orderProducts item='orderProduct' name=orderProduct}	
		{assign var='productLink' value=$link->getProductLink($orderProduct.id_product,$orderProduct.id_product_attribute, $orderProduct.link_rewrite)}
		<div  class="crossselling-single-product {if $smarty.foreach.orderProduct.last} last-item{/if}">
			<a href="{$productLink}" title="{$orderProduct.name}" class="crossselling-single-product-link">
			<img src="{$orderProduct.image}" alt="{$orderProduct.name|htmlspecialchars}" />
			</a>
			<div class="crossselling-single-product-name">
			<a href="{$productLink}" title="{$orderProduct.name}">{$orderProduct.name|escape:htmlall:'UTF-8'|truncate:45:"..."}</a>
			</div>
			{*<div class="crossselling-single-product-price">
			<span class="price">{convertPrice price=$orderProduct.displayed_price}</span>
			</div>*}
			{*<div class="crossselling-addtocart">
			<a class="exclusive" rel="ajax_id_product_{$orderProduct.id_product}"  href="{$link->getPageLink('cart.php')}?qty=1&amp;id_product={$orderProduct.id_product}&amp;token={$static_token}&amp;add"  title="{l s='Add to cart' mod='crosssellingmanager'}" >{l s='Add to Cart' mod='crosssellingmanager'}</a>
			</div>*}
		</div>
		{/foreach}
	</div>
	{*<div class="crossselling-carnav crossselling-carnav-prev" id="carnav-prev"></div>
	<div class="crossselling-carnav crossselling-carnav-next" id="carnav-next"></div>*}
</div>		
{/if}

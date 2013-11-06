<script type="text/javascript">
{literal}
$(document).ready(function() {
	$('.showroom_sel_cntnr').lazyloader('a.product_image img');
});
{/literal}
</script>
<div class="cloth_wrapper">
	{$HOOK_CLOTH_SLIDE_SHOW}

	<h2>{l s='Cloth Boutique'}</h2>

	{if isset($all_cloth_products) AND $all_cloth_products}
	<div id="all_cloth_products">
		{foreach from=$all_cloth_products item=product name=cloth_product}
		{if $smarty.foreach.cloth_product.index % 4 == 0}
		<div class="showroom_sel_cntnr_row">
		{/if}
			<div class="showroom_sel_cntnr {if $smarty.foreach.cloth_product.index % 4 == 3}last_item{/if}">
				<div class="showfb_{$product.id_product}">
				<div  class="showroom_sel_shoe {if $product.quantity == 0}sold_out{/if}{if ( $product.quantity > 0 && $product.quantity < $last_qties )}low_stock{/if}">

					{*<a href="{$product.link}" title="{$product.name|escape:html:'UTF-8'}" class="product_image">
						<img src="{$img_ps_dir}spacer.gif" data-src="{$link->getImageLink($product.link_rewrite, $product.id_image, 'prodsmall')}" height="{$prodsmallSize.height}" width="{$prodsmallSize.width}" alt="{$product.name|escape:html:'UTF-8'}" />
					</a>*}
					<a href="{$product.link}" title="{$product.name|escape:html:'UTF-8'}" class="product_image">
						<img class="prod_img1{$product.id_image}" src="{$img_ps_dir}spacer.gif" data-src="{$link->getImageLink($product.link_rewrite, $product.id_image, 'clothsmall')}" height="{$prodsmallSize.height}" width="{$prodsmallSize.width}" alt="{$product.name|escape:html:'UTF-8'}" />
						{*<img class="hover_state prod_img2{$product.id_image}" src="{$link->getImageLink($product.link_rewrite, $product.mouseover_image, 'prodsmall')}" height="{$prodsmallSize.height}" width="{$prodsmallSize.width}" alt="{$product.name|escape:html:'UTF-8'}" style="display:none;"/>*}
						{if ($product.quantity > 0 && $product.quantity < $last_qties )}
								<div class="low_stock_container">
									<span>{l s='low stock'}</span>
								</div>
								{/if}
								{if $product.quantity == 0}
								<div class="sold_out_container">
									<span>{l s='sold out'}</span>
								</div>
							{/if}
					</a>
				</div>{* end of showroom_sel_shoe*}
				<div class="name_color_container">
					{*Favourite Button*}
					{if isset($is_my_fav_active) && $is_my_fav_active}
						{if $product.product_combination > 0}
							{assign var=ipa value=$product.id_product_attribute}
						{else}
							{assign var=ipa value=$product.default_combination}
						{/if}
					{/if}
					{*Favourite Button*}
					<div id="name_fav">
					<div class="showroom_shoe_name" {if isset($is_my_fav_active) && $is_my_fav_active} id="ajax_response_{$product.id_product}_{$ipa}" {/if}>
						<a href="{$product.link}" class="showroom_shoe_name" title="{$product.name|escape:html:'UTF-8'}">{$product.name|truncate:27:'...'|escape:'htmlall':'UTF-8'}</a>
						{*Favourite Button*}
						{if isset($is_my_fav_active) && $is_my_fav_active}
							<input type="hidden" name="qty" id="quantity_wanted" value="1"/>
							{assign var=in_myfavorite value=0}
							{if isset($my_fav_ids)}
								{if $product.product_combination > 0}
									{if isset($my_fav_ipa) && $product.id_product|in_array:$my_fav_ids  && $ipa|in_array:$my_fav_ipa}
										{assign var=in_myfavorite value=1}
									 {/if}
								{else}
									{if $product.id_product|in_array:$my_fav_ids}
										{assign var=in_myfavorite value=1}
									{/if}
								{/if}
							{/if}

							{if $in_myfavorite == 1}
								<a href="javascript:;" id="faved_{$product.id_product}_{$ipa}"  class="favorite_flag in_myfavorite"  onclick="FavProductRemove('mutiple_products', 'delete', '{$product.id_product}',{$ipa}, false)">
								</a>
							{else}
								<a href="#" id="to_fav_{$product.id_product}_{$ipa}" class="favorite_flag"  onclick="FavlistCart('mutiple_products', 'add', '{$product.id_product}',{$ipa}, document.getElementById('quantity_wanted').value, false); return false;">
								</a>
							{/if}
						{/if}
						{*Favourite Button*}
					</div>
						</div>
					{* displaying shoesize in popup*}
							{if isset($product.shoe_sizes)}
								<a class="sizeseeing"  onclick="$('#sizepopup_{$product.id_product}{if $product.product_combination > 0}_{$product.id_product_attribute}{/if}').toggle()"></a>
								{/if}
					{* displaying shoesize in popup*}
					<div class = "showroom_product_price">
						{*ShowRoom Disappear Start*}
						{if $product.reduction AND $product.specific_prices.strike_out}
							<span class="discount">{convertPrice price=$product.price_without_reduction}</span>
						{/if}
                        {*ShowRoom Disappear End*}
						{displayPrice price = $product.price}
					</div>
					{if isset($product.product_colors) }
						<div id="product_colors">
							<ul id="color_list">
							{foreach from=$product.product_colors  item='color'}
								<li>
								{if file_exists($col_img_dir|cat:$color.id_attribute|cat:'.jpg')}
									<span style="background: transparent url({$img_col_dir}{$color.id_attribute}.jpg) 0 0 no-repeat"></span>
								{else}
									<span style="background: {$color.attribute_color};"></span>
								{/if}
								</li>
							{/foreach}
							</ul>
						</div>
					{/if}
				</div>{* name_color_container*}
					{* displaying shoesize in popup*}
						<div id="sizepopup_{$product.id_product}{if $product.product_combination > 0}_{$product.id_product_attribute}{/if}" class="sizeview" style="display:none">
							<a class="sizeseeing_close" onclick="$('#sizepopup_{$product.id_product}{if $product.product_combination > 0}_{$product.id_product_attribute}{/if}').hide()"></a>
							<ul id="size_list">
							{if $product.product_combination > 0}
								{foreach from=$product.shoe_sizes  item='size'}
									{foreach from=$size key = 'qty' item='sizes'}
							{foreach from=$sizes item='sizea'}
									<li {if $qty <= 0}class ="no_stock" {/if}>
									{$sizea}
									</li>

									{/foreach}
									{/foreach}
									{/foreach}
							{else}
								{foreach from=$product.shoe_sizes item='size'}
									<li {if $size.qty <= 0}class ="no_stock" {/if}>
									{$size.id}
									</li>
								{/foreach}
						{/if}
						</ul>
						</div>
						{* displaying shoesize in popup*}
				</div> {*end of showfb*}
			</div>{* end of showroom_sel_cntnr*}
		{if $smarty.foreach.cloth_product.index % 4 == 3}
		</div>
		{/if}

		{/foreach}
	</div>{*end of all_cloth_products *}
	{else}
		<div class="out-of-stock"></div>
	{/if}


	{* Quickview response container *}
	<div style="display:none"><div id="qv_fbox_cont"></div></div>

</div>
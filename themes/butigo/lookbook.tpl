{$HOOK_AFTER_MENU}
<script type=text/javascript>
	var img_src = "{$img_dir}discount/10-oc-13-popup.png";
	var redirt_url = "{$link->getCategoryLink($discount_link_rewrite)}";
</script>


{assign var=cat_name value=$category->name|lower|replace:' ':'' }
<div class="features_wrapper">
    <div id="lk_image" style="margin: 2px 0 20px;">
        <img src="{$img_cat_dir}{$category->id}.jpg" alt="{cat_name}" />
	</div>

	{if isset($all_featured_products) AND $all_featured_products}
	<div id="all_featured_products" class="container_lookbook">
		{foreach from=$all_featured_products item=product name=Featured_products}
			<div class="showroom_sel_cntnr" style="{if $product.id_product|in_array:$test_product_ids}{if !isset($price_test_group)}display:none{/if}{/if}">
				<div class="showfb_{$product.id_product}">
				<div class="showroom_sel_shoe {if $product.quantity <= 0} sold_out{elseif $product.quantity > 0}{if $product.on_sale && $product.reduction} special-discount{elseif $product.quantity < $last_qties} low_stock{/if}{/if}"
                     {*if isset($showroom_with_size)*} onmouseover="$('#shoe_size_button_{$product.id_product}{if $product.product_combination > 0}_{$product.id_product_attribute}{/if}').toggle()" onmouseout="$('#shoe_size_button_{$product.id_product}{if $product.product_combination > 0}_{$product.id_product_attribute}{/if}').hide()"{*/if*}>

					<a href="{$product.link}" title="{$product.name|escape:html:'UTF-8'}" class="product_image">
						<img class="hover_state prod_img2{$product.id_image}" src="{$link->getImageLink($product.link_rewrite, $product.mouseover_image, 'prodsmall')}"  alt="{$product.name|escape:html:'UTF-8'}" style="display:none"/>
						<img class="prod_img1{$product.id_image}" src="{$img_ps_dir}spacer.gif" data-src="{$link->getImageLink($product.link_rewrite, $product.id_image, 'prodsmall')}" height="{$prodsmallSize.height}" width="{$prodsmallSize.width}" alt="{$product.name|escape:html:'UTF-8'}" />{if $product.quantity > 0}
							{if $product.on_sale && $product.reduction}
							<div class="special-discount-container">
									<span></span>
							</div>
							{elseif $product.quantity < $last_qties}
							<div class="low_stock_container">
								<span>{l s='low stock'}</span>
							</div>
							{/if}
                        {elseif $product.quantity <= 0}
                        <div class="sold_out_container">
                            <span>{l s='sold out'}</span>
                        </div>
                        {/if}
					</a>
				</div>{* end of showroom_sel_shoe*}
                <div class="name_color_container" {*if isset($showroom_with_size)*} onmouseover="$('#shoe_size_button_{$product.id_product}{if $product.product_combination > 0}_{$product.id_product_attribute}{/if}').toggle()" onmouseout="$('#shoe_size_button_{$product.id_product}{if $product.product_combination > 0}_{$product.id_product_attribute}{/if}').hide()" {*/if*}>
						{if isset($is_my_fav_active) && $is_my_fav_active}
							{if $product.product_combination > 0}
								{assign var=ipa value=$product.id_product_attribute}
							{else}
								{assign var=ipa value=$product.default_combination}
							{/if}
						{/if}
					{*Favourite Button*}
					<div class="showroom_shoe_name" {if isset($is_my_fav_active) && $is_my_fav_active} id="ajax_response_{$product.id_product}_{$ipa}" {/if}>
						<div id="name_fav">
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
							{foreach from=$product.product_colors key='id_attribute' item='color'}
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
					{if $product.collection_name}<span class="prod_col_name">{$product.collection_name}</span>{/if}
				</div>{* name_color_container*}
			</div> {*end of showfb*}
			</div>{* end of showroom_sel_cntnr*}
            {*if isset($showroom_with_size)*}
			{*Customer Likes & Dislikes Block Start*}
			{if $customerLikesDislikesEnable == 1}
				{assign var=customerLikedProduct value=$product.id_product}
				<div class="customer-like-dislike-container">
					<p class="customer-like-dislike-sub-container">
						<a class="customer-like-btn like-{$product.id_product} {if $customerLikes.$customerLikedProduct} showroom-like-selected{/if}" productId="{$product.id_product}"></a>
						<a class="customer-dislike-btn dislike-{$product.id_product} {if $customerDislikes.$customerLikedProduct} showroom-dislike-selected{/if}" productId="{$product.id_product}"></a>
					</p>
				</div>
			{/if}
			{*Customer Likes & Dislikes Block End*}
			{*$Hook_Customer_Likes_Dislikes*}
            {if isset($product.shoe_sizes)}
            {* displaying shoesize in popup*}
            <div class="shoe-size-button-container" id="shoe_size_button_{$product.id_product}{if $product.product_combination > 0}_{$product.id_product_attribute}{/if}"
                  onmouseover="$(this).show()" onmouseout="$('#sizepopup_{$product.id_product}{if $product.product_combination > 0}_{$product.id_product_attribute}{/if}').hide();$(this).hide();">
                <div class="shoe-size-button" onclick="$('#sizepopup_{$product.id_product}{if $product.product_combination > 0}_{$product.id_product_attribute}{/if}').toggle()" onmouseout="$('#shoe_size_button_{$product.id_product}{if $product.product_combination > 0}_{$product.id_product_attribute}{/if}').hide();"></div>
            </div>
            <div id="sizepopup_{$product.id_product}{if $product.product_combination > 0}_{$product.id_product_attribute}{/if}" class="sizeview" onmouseover= "$('#shoe_size_button_{$product.id_product}{if $product.product_combination > 0}_{$product.id_product_attribute}{/if}').show();$(this).show();" onmouseout="$('#shoe_size_button_{$product.id_product}{if $product.product_combination > 0}_{$product.id_product_attribute}{/if}').hide();$(this).hide();">
                {*<a class="sizeseeing_close"></a>*}
                <ul id="size_list">
                {foreach from=$product.shoe_sizes item='size'}
                    <li {if $size.product_qty < 1}class="no_stock"{/if}>{$size.attribute_name}</li>
                {/foreach}
                    {*if $product.product_combination > 0}
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
                    {/if*}
                </ul>
            </div> {*end of shoe_sizes*}
            {/if}
            {*/if*}
        {/foreach}

	</div>{*end of all_featured_products *}
	{else}
		<div class="out-of-stock"></div>
	{/if}

	{$HOOK_LOOKBOOK_NAV}

	{* How Lookbooks works popup *}
	<a id="fbox-hiw-lkbk" href="{$img_dir}lookbook/collections-popup.jpg" style="display:none; text-indent:-99999px; overflow:hidden">{l s='How Lookbooks works'}</a>
	{if isset($butigim_pop_up)}
		<script type="text/javascript">
			var butigim_pop_up = {$butigim_pop_up};
		</script>
		{if $butigim_pop_up==1}
			<a id="24-hr-showroom" href="{$img_dir}lookbook/24_showroom_sg.jpg" style="display:none; text-indent:-99999px; overflow:hidden">{l s='Your Showroom will be ready in 24hrs'}</a>
		{/if}
	{/if}
</div>


{* NanoInteractive Integration *}
<script type="text/javascript">
	(function(d){ldelim}
	    var HEIAS_PARAMS = [];
	    {*productIds = [];*}
	    HEIAS_PARAMS.push(['type', 'ppx'], ['ssl', 'auto'], ['n', '6451'], ['cus', '17201']);
	    HEIAS_PARAMS.push(['pb', '1']);

	    {*{foreach from=$all_featured_products item=product name=Featured_products}
		    productIds.push({$product.id_product});
	    {/foreach}*}
	    {*HEIAS_PARAMS.push(['order_article', productIds]);*}
		HEIAS_PARAMS.push(['order_article','{foreach name=Featured_products item=product from=$all_featured_products}{$product.id_product}{if ! $smarty.foreach.Featured_products.last},{/if}{/foreach}']);
	    if (typeof window.HEIAS === 'undefined') window.HEIAS = [];
	    window.HEIAS.push(HEIAS_PARAMS);

	    var scr = d.createElement('script');
	    scr.async = true;
	    scr.src = (d.location.protocol === 'https:' ? 'https:' : 'http:') + '//ads.heias.com/x/heias.async/p.min.js';
	    var elem = d.getElementsByTagName('script')[0];
	    elem.parentNode.insertBefore(scr, elem);
	{rdelim}(document));
</script>
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

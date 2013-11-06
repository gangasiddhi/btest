{* Displays all products for the showroom *}
{* start - display left slides in showroom & product page *}
{*<script type="text/javascript">
var product_links =  new Array();
{if isset($customer_shoes) AND $customer_shoes}
	{foreach from=$customer_shoes item=product}
		 product_links[{$product.id_product|intval}] = new Array('{$product.link}');
	{/foreach}
{/if}
{if isset($more_shoes) AND $more_shoes}
	{foreach from=$more_shoes item=product}
		 product_links[{$product.id_product|intval}] = new Array('{$product.link}');
	{/foreach}
{/if}
{if isset($featured_shoes) AND $featured_shoes}
	{foreach from=$featured_shoes item=product}
		 product_links[{$product.id_product|intval}] = new Array('{$product.link}');
	{/foreach}
{/if}
{if isset($products_handbags) AND $products_handbags}
	{foreach from=$products_handbags item=product}
		 product_links[{$product.id_product|intval}] = new Array('{$product.link}');
	{/foreach}
{/if}
{if isset($more_handbags) AND $more_handbags}
	{foreach from=$more_handbags item=product}
		 product_links[{$product.id_product|intval}] = new Array('{$product.link}');
	{/foreach}
{/if}
{if isset($products_jewelry) AND $products_jewelry}
	{foreach from=$products_jewelry item=product}
		 product_links[{$product.id_product|intval}] = new Array('{$product.link}');
	{/foreach}
{/if}
{if isset($more_jewelry) AND $more_jewelry}
	{foreach from=$more_jewelry item=product}
		 product_links[{$product.id_product|intval}] = new Array('{$product.link}');
	{/foreach}
{/if}
</script>*}

{foreach from=$deal_products item=product name=ProductHandbag}
{if ($product.specific_time > 0 && $deal_id == $product.id_product)}
<script type="text/javascript">
{literal}
// <![CDATA[
		 $(document).ready(function() {
		var untilTime = $("span#discount_clock").attr("class");
		$("span#discount_clock").countdown({
						until: untilTime,
						format: 'HMS',
						compact: true,
						description: ''
                    });
			});
//]]>
{/literal}
</script>
{/if}
{/foreach}
{*begin main showroom*}
<div id="showroom_contents">
	{$HOOK_AFTER_MENU}    
	<div id="showroom_header">
		<div id="fb_avatar">
			<a class="fbox-hiw iframe" href="http://www.youtube.com/watch?v=HY9ipt9g0vM&autoplay=0&rel=0">{l s='How it works?'}</a>
		</div>
		<div id="description">
				<h3>{$name}, {l s='your Personalised'} {$month_fullname}</h3>
			<img src="{$img_dir}showroom/showroom_header_text.png" alt="{l s='Welcome'}"/>
			<ul id="mn_actions">
				<li id="learn_more_link">
					<a class="fbox-hiw iframe learn_more_link" href="http://www.youtube.com/watch?v=HY9ipt9g0vM&autoplay=0&rel=0">{l s='How it works?'}</a>
				</li>
				<li id="testimonials_link">
					<a class="testimonials_link" href="{$link->getPageLink('testimonials.php')}" target="_blank">{l s='Testimonials'}</a>
				</li>
			</ul>

		</div>
	</div>

	{* deal - start *}
	{foreach from=$deal_products item=product name=ProductHandbag}
		{if ($product.specific_time > 0 && $deal_id == $product.id_product)}
			<div id="showroom_deal">
				<h2 id="deal_heading">{l s='DEAL'}</h2>
				<div id="deal" >
					<a href="{$product.link}" title="{$product.name|escape:html:'UTF-8'}" class="product_image">
						<img src="{$img_dir}showroom/dealimage.jpg" id="deal_image"  alt="{l s='Deal image'}" />
					</a>
					<div id="deal_contant">
						<a href="{$product.link}" id="deal_product_name" title="{$product.name|escape:html:'UTF-8'}">{$product.name|truncate:27:'...'|escape:'htmlall':'UTF-8'}</a>
						<div id="deal_product_desc">
							<p>
								{$product.description_short|strip_tags|truncate:90:"..."}
								<a href="{$product.link}" id="view_deal" title="{$product.name|escape:html:'UTF-8'}">View Deal</a>
							</p>
						</div>
						<div id="deal_product_time">
							{if $product.specific_time != 0}
								<span id="discount_clock" class="{$product.specific_time}" >{$product.specific_time}</span>
							{/if}
							<img src="{$img_dir}showroom/timeleftwhite.gif" id="deal_time_img"  alt="{l s='Deal image'}" />
							<a href="{$product.link}" title="{$product.name|escape:html:'UTF-8'}">
								<img src="{$img_dir}showroom/viewdeal.png" id="view_deal"  alt="{l s='Deal image'}" />
							</a>
						</div>
						{if isset($product.product_colors) }
							<div id="deal_product_colors">
								<ul id="deal_color_list">
								{foreach from=$product.product_colors key='id_attribute' item='color'}
									<li>
										<a href="{$product.link}" class="" title="{$product.name|escape:html:'UTF-8'}">
										{if file_exists($col_img_dir|cat:$color.id_attribute|cat:'.jpg')}
											<span style="background: transparent url({$img_col_dir}{$color.id_attribute}.jpg) 0 0 no-repeat"></span>
										{else}
											<span style="background: {$color.attribute_color};"></span>
										{/if}
										</a>
									</li>
								{/foreach}
								</ul>
							</div>
						{/if}
					</div>
				</div>
			</div>
		{/if}
	{/foreach}
	{* deal-end *}

	{*<div id="welcome_msg">
		<img src="{$img_dir}showroom/showroom-top-header.gif" id = "remove_text"  alt="{l s='Your_personal_selection'}" usemap="#top_msg"/>
		<map name="top_msg">
			<area shape="rect" coords="19,49,200,82" alt="{l s='How it works'}" href="{$link_hiw_slideshow}"  id = "cliked" class="fbox-hiw iframe" />
			<area shape="rect" coords="218,47,400,80" alt="{l s='Testimonials'}" href="{$ps_dir}testimonials.php" target="_blank" />
		</map>*}
		{*
		<div >Butigine Hoşgeldin, hadi tanışalım</div>
		<a href="#">nasil calsiyor</a>
		<h3>{l s='Welcome to your showroom,'}&nbsp;{$name}&#33;</h3>*}
		{* <img src="{$img_dir}lp-fb.gif" height="18" width="60" alt=""/>
		<img src="{$img_dir}tip_question.gif" height="17" width="17" alt=""/> *}
		{*<p>
			{l s='Welcome to your first ShoeDazzle showroom! Check out all the shoes handbags and jewelry your stylists have chosen for you this month!'}<br/>
			 <span>Account Status&#58;</span>Pending
			<span>{l s='Credits'}&nbsp;&#58;&nbsp;{$credits}</span>
			<span id="price"><img src="{$img_dir}showroom/price-info.jpg" alt="{l s='Price information'}"/></span>
		</p>
		{if $member == 2}
			{if $day>=1 AND $day<=5}
				<span class="skip_month"><a href="#" title="{l s='Skip this month'}">{l s='Skip this month'}</a></span>
			{else}
				<span class="skip_month"><img src="{$img_dir}showroom/skip-disabled.jpg" alt="{l s='Skip this month'}"/></span>
			{/if}
		{/if}*}
	{*</div>*}{*end of welcome_msg*}

	{*<div id="persistent_nav">
		<div class="center-content">
			<ul>
				<li class="pn_cols"><a href="#{l s='Collections'}" title="{l s='Collections'}">{l s='Collections'}</a></li>
				<li class="pn_shoes"><a href="#{l s='Shoes'}" title="{l s='Shoes'}">{l s='Shoes'}</a></li>
				<li class="pn_handbags"><a href="#{l s='Handbags'}" title="{l s='Handbags'}">{l s='Handbags'}</a></li>
				<li class="pn_jewelry"><a href="#{l s='Jewelry'}" title="{l s='Jewelry'}">{l s='Jewelry'}</a></li>
			</ul>
		</div>
	</div>*}{* end of persistent_nav *}

	<div class="showroom_box showroom_products">
		{*begin shoes*}
		<div class="showroom_header_products {*$category_month_id} {$category_shoes_id} {$category_shoes_style_id*}">
			<a name="{l s='Shoes'}"></a>
			{*<h2><img src="{$img_dir}showroom/months/{$month}.gif"  alt="{l s='month'}"/>
				<img src="{$img_dir}showroom/shoes.gif" alt="{l s='Shoes'}"/></h2>*}
			<h2>{l s='Shoes'}</h2>
		</div>

		{include file=$tpl_dir./showroom-$customer_style_name.tpl}

		{*begin handbags*}
		<div class="showroom_header_products">
			<a name="{l s='Handbags'}"></a>
			<h2>{l s='handbags'}</h2>
			{*<h2><img src="{$img_dir}/showroom/months/{$month}.gif" alt="{l s='month'}"/>
				<img src="{$img_dir}showroom/handbags.gif" alt="{l s='Handbags'}"/></h2>*}
		</div>
		{if isset($products_handbags) AND $products_handbags}
			{*<div class="hdr_personal_selection">
				<img src="{$img_dir}showroom/yourpersonalselection.gif" alt="{l s='Your_personal_selection'}"/>
			</div>*}
			<div id="original_selection_handbags">
			{foreach from=$products_handbags item=product name=ProductHandbag}
				<div class="showroom_sel_cntnr">
					<div class="showfb_{$product.id_product}">
					<div class="showroom_sel_handbags {if $product.quantity <= 0} sold_out{elseif $product.quantity > 0}{if $product.on_sale && $product.reduction} special-discount{elseif $product.quantity < $last_qties} low_stock{/if}{/if}">
						<a href="{$product.link}" title="{$product.name|escape:html:'UTF-8'}" class="product_image">
							<img class="hover_state prod_img2{$product.id_image}" src="{$link->getImageLink($product.link_rewrite, $product.mouseover_image, 'prodsmall')}" alt="{$product.name|escape:html:'UTF-8'}" style="display:none"/>
							<img class="prod_img1{$product.id_image}" src="{$img_ps_dir}spacer.gif" data-src="{$link->getImageLink($product.link_rewrite, $product.id_image, 'prodsmall')}" height="{$prodsmallSize.height}" width="{$prodsmallSize.width}" style="display:inline;zoom:1;" alt="{$product.name|escape:html:'UTF-8'}" />
							{if $product.quantity > 0}
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
					</div>{* end of showroom_sel_handbags*}
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
						<div class="showroom_handbags_name" {if isset($is_my_fav_active) && $is_my_fav_active} id="ajax_response_{$product.id_product}_{$ipa}" {/if}>
							<a href="{$product.link}" class="showroom_handbags_name" title="{$product.name|escape:html:'UTF-8'}">{$product.name|truncate:27:'...'|escape:'htmlall':'UTF-8'}</a>
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
					</div>{* name_color_container*}
					{*<span class="fblike">
						<fb:like href="{$product.link|urlencode}" send="false" layout="button_count" width="160" show_faces="false"></fb:like>
					</span>*}
					</div>
				</div>{* end of showroom_sel_cntnr*}
			{/foreach}
			</div>{*end of original_selection_handbags*}
		{else}
			<div class="out-of-stock"></div>
		{/if}

		{if isset($more_handbags) AND $more_handbags}
		<div class="see_more" id="more_handbags">
			{*<img src="{$img_dir}showroom/seemore-text-handbags.gif" alt="{l s='see more'}"/>*}
			<span>{l s='come to other shoes boutique Want to see?'}</span>
			<a href="" class = "buttonmedium blue" title="{l s='see more'}">{l s='see more'}</a>
		</div>
		<div class="see_more_products" style="display:none;" id="more_handbags_products">
			<div class="more_container">
				<ul id="more_handbags_list">
				{foreach from=$more_handbags item=product name=MoreHandbag}
					<li>
						<div class="showroom_sel_cntnr">
							<div class="showfb_{$product.id_product}">
							<div  class="showroom_sel_handbags {if $product.quantity <= 0} sold_out{elseif $product.quantity > 0}{if $product.on_sale && $product.reduction} special-discount{elseif $product.quantity < $last_qties} low_stock{/if}{/if}">
								<a href="{$product.link}" title="{$product.name|escape:html:'UTF-8'}" class="product_image">
									<img class="hover_state prod_img2{$product.id_image}" src="{$link->getImageLink($product.link_rewrite, $product.mouseover_image, 'prodsmall')}" height="{$prodsmallSize.height}" width="{$prodsmallSize.width}" alt="{$product.name|escape:html:'UTF-8'}" style="display:none"/>
									<img class="prod_img1{$product.id_image}" src="{$img_ps_dir}spacer.gif" data-src="{$link->getImageLink($product.link_rewrite, $product.id_image, 'prodsmall')}" height="{$prodsmallSize.height}" width="{$prodsmallSize.width}" alt="{$product.name|escape:html:'UTF-8'}" />									
									{if $product.quantity > 0}
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
							</div>{* end of showroom_sel_handbags*}
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
								<div class="showroom_handbags_name" {if isset($is_my_fav_active) && $is_my_fav_active} id="ajax_response_{$product.id_product}_{$ipa}" {/if}>
									<a href="{$product.link}" class="showroom_handbags_name" title="{$product.name|escape:html:'UTF-8'}">{$product.name|truncate:27:'...'|escape:'htmlall':'UTF-8'}</a>
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
							</div>{* name_color_container*}
							{*<span class="fblike">
								<fb:like href="{$product.link|urlencode}" send="false" layout="button_count" width="160" show_faces="false"></fb:like>
							</span>*}
							</div>
						</div>{* end of showroom_sel_cntnr*}
					</li>
				{/foreach}
				</ul>
			</div>
		</div>
		{/if}
		{*end handbags*}


		{*begin jewelry*}
		{*<div class="showroom_header_products">
			<a name="{l s='Jewelry'}"></a>
			<h2>{l s='Jewelry'}</h2>
		</div>
		{if isset($products_jewelry) AND $products_jewelry}
			<div id="original_selection_jewelry">
			{foreach from=$products_jewelry item=product name=ProductJewel}
				<div class="showroom_sel_cntnr">
					<div class="showfb_{$product.id_product}">
					<div onmouseover="qvOver('{$product.id_image}');" onmouseout="qvOut('{$product.id_image}');" class="showroom_sel_jewelery {if $product.quantity == 0}sold_out{/if}{if ( $product.quantity > 0 && $product.quantity < $last_qties )}low_stock{/if}">
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
						<a href="{$product.link}" title="{$product.name|escape:html:'UTF-8'}" class="product_image">
							<img class="prod_img1{$product.id_image}" src="{$img_ps_dir}spacer.gif" data-src="{$link->getImageLink($product.link_rewrite, $product.id_image, 'prodsmall')}" height="{$prodsmallSize.height}" width="{$prodsmallSize.width}" alt="{$product.name|escape:html:'UTF-8'}" />
						</a>
						<div class="qv_container">
							<a href="#" class="qv_image_{$product.id_image} qv_image" id="qv_image_{$product.id_image}" data-id="{$product.id_product}{if $product.product_combination > 0}_{$product.id_product_attribute}{/if}"></a>
							<a class="qv_fbox" id="qv_fbox_{$product.id_product}{if $product.product_combination > 0}_{$product.id_product_attribute}{/if}" href="#qv_fbox_cont"></a>
						</div>
					</div>
					<div class="name_color_container">
						<div class="showroom_jewelry_name">
							<a href="{$product.link}" class="showroom_jewelry_name" title="{$product.name|escape:html:'UTF-8'}">{$product.name|truncate:27:'...'|escape:'htmlall':'UTF-8'}</a>
						</div>
						<div class = "showroom_product_price">
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
					</div>
					</div>
				</div>
			{/foreach}
			</div>
		{else}
			<div class="out-of-stock"></div>
		{/if}*}

		{*if isset($more_jewelry) AND $more_jewelry}
		<div class="see_more" id="more_jewelry">
			<img src="{$img_dir}showroom/seemore-text-jewelry.gif" alt="{l s='see more'}"/>
			<a href="" title="{l s='see more'}">{l s='see more'}</a>
		</div>
		<div class="see_more_products" style="display:none;" id="more_jewelry_products">
			<div class="more_container">
				<ul id="more_jewelry_list">
				{foreach from=$more_jewelry item=product name=MoreJewel}
					<li>
						<div class="showroom_sel_cntnr">
							<div class="showfb_{$product.id_product}">
							<div onmouseover="qvOver('{$product.id_image}');" onmouseout="qvOut('{$product.id_image}');" class="showroom_sel_jewelery {if $product.quantity == 0}sold_out{/if}{if ( $product.quantity > 0 && $product.quantity < $last_qties )}low_stock{/if}">
							{if ($product.quantity > 0 && $product.quantity < $last_qties )}
								<div class="low_stock_container">
									<span>{l s='low stock'}</span>
								</div>
							{/if}
							{if $product.quantity == 0}
								<div class="sold_out_container">
									<span>{l s='sold out'}</span>
								</div>
							{/if} *}{*
							<a href="{$product.link}" title="{$product.name|escape:html:'UTF-8'}" class="product_image">
								<img src="{$img_ps_dir}spacer.gif" data-src="{$link->getImageLink($product.link_rewrite, $product.id_image, 'prodsmall')}" height="{$prodsmallSize.height}" width="{$prodsmallSize.width}" alt="{$product.name|escape:html:'UTF-8'}" />
							</a>*}{*
							<a href="{$product.link}" title="{$product.name|escape:html:'UTF-8'}" class="product_image">
							<img class="prod_img1{$product.id_image}" src="{$img_ps_dir}spacer.gif" data-src="{$link->getImageLink($product.link_rewrite, $product.id_image, 'prodsmall')}" height="{$prodsmallSize.height}" width="{$prodsmallSize.width}" alt="{$product.name|escape:html:'UTF-8'}" />
							*}{*<img class="hover_state prod_img2{$product.id_image}" src="{$link->getImageLink($product.link_rewrite, $product.mouseover_image, 'prodsmall')}" height="{$prodsmallSize.height}" width="{$prodsmallSize.width}" alt="{$product.name|escape:html:'UTF-8'}" style="display:none;"/>*}{*
						</a>
						*}{* start - quick view *}{*
						<div class="qv_container">
							<a href="#" class="qv_image_{$product.id_image} qv_image" id="qv_image_{$product.id_image}" data-id="{$product.id_product}{if $product.product_combination > 0}_{$product.id_product_attribute}{/if}"></a>
							<a class="qv_fbox" id="qv_fbox_{$product.id_product}{if $product.product_combination > 0}_{$product.id_product_attribute}{/if}" href="#qv_fbox_cont"></a>
						</div>
						*}{* end - quick view *}{*
							</div>{ end of showroom_sel_jewelry}
							<div class="name_color_container">
								<div class="showroom_jewelry_name">
									<a href="{$product.link}" class="showroom_jewelry_name" title="{$product.name|escape:html:'UTF-8'}">{$product.name|truncate:27:'...'|escape:'htmlall':'UTF-8'}</a>
								</div>
								<div class = "showroom_product_price">
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
							</div>{ name_color_container}
							{<span class="fblike">
								<fb:like href="{$product.link|urlencode}" send="false" layout="button_count" width="160" show_faces="false"></fb:like>
							</span>}
							</div>
						</div>{ end of showroom_sel_cntnr}
					</li>
				{/foreach}
				</ul>
			</div>
		</div>
		{/if*}
		{*end jewelry*}

	</div>{* end of showroom_box showroom_products*}

	{* Quickview response container *}
	<div style="display:none"><div id="qv_fbox_cont"></div></div>
{$HOOK_BEFORE_FOOTER}
</div>{* end of showroom_contents *}

{if isset($all_products_on_page) AND $all_products_on_page}
	{* NanoInteractive Integration *}
	<script type="text/javascript">
		(function(d){ldelim}
		    var HEIAS_PARAMS = [],
		    productIds = [];
		    HEIAS_PARAMS.push(['type', 'ppx'], ['ssl', 'auto'], ['n', '6451'], ['cus', '17201']);
		    HEIAS_PARAMS.push(['pb', '1']);

		    {*{foreach from=$all_products_on_page item=product}
			    productIds.push({$product.id_product});
		    {/foreach}
		    HEIAS_PARAMS.push(['order_article', productIds]);*}
			HEIAS_PARAMS.push(['order_article','{foreach name="showroom_products" item=product from=$all_products_on_page}{$product.id_product}{if ! $smarty.foreach.showroom_products.last},{/if}{/foreach}']);
		    (window.HEIAS = window.HEIAS || []).push(HEIAS_PARAMS);

		    var scr = d.createElement('script');
		    scr.async = true;
		    scr.src = (d.location.protocol === 'https:' ? 'https:' : 'http:') + '//ads.heias.com/x/heias.async/p.min.js';
		    var elem = d.getElementsByTagName('script')[0];
		    elem.parentNode.insertBefore(scr, elem);
		{rdelim}(document));
	</script>
{/if}

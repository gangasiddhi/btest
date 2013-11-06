
{if isset($customer_shoes) AND $customer_shoes}
	{*<div class="hdr_personal_selection">*}
		{*<img src="{$img_dir}showroom/yourpersonalselection.gif" alt="{l s='Your_personal_selection'}"/>*}
	{*</div>*}
	<div id="original_selection_shoes">
		{foreach from=$customer_shoes item=product name=CustomerShoe}
		<div class="showroom_sel_cntnr">
			<div class="showfb_{$product.id_product}">
				<div class="showroom_sel_shoe {if $product.quantity <= 0} sold_out{elseif $product.quantity > 0}{if $product.on_sale && $product.reduction} special-discount{elseif $product.quantity < $last_qties} low_stock{/if}{/if}"
                     {*if isset($showroom_with_size)*} onmouseover="$('#shoe_size_button_{$product.id_product}{if $product.product_combination > 0}_{$product.id_product_attribute}{/if}').toggle()" onmouseout="$('#shoe_size_button_{$product.id_product}{if $product.product_combination > 0}_{$product.id_product_attribute}{/if}').hide()"{*/if*}>

					<a href="{$product.link}" title="{$product.name|escape:html:'UTF-8'}" class="product_image">
						<img class="hover_state prod_img2{$product.id_image}" src="{$link->getImageLink($product.link_rewrite, $product.mouseover_image, 'prodsmall')}" alt="{$product.name|escape:html:'UTF-8'}" style="display:none"/>
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
				</div>{* end of showroom_sel_shoe*}
				<div class="name_color_container" {*if isset($showroom_with_size)*} onmouseover="$('#shoe_size_button_{$product.id_product}{if $product.product_combination > 0}_{$product.id_product_attribute}{/if}').toggle()" onmouseout="$('#shoe_size_button_{$product.id_product}{if $product.product_combination > 0}_{$product.id_product_attribute}{/if}').hide()" {*/if*}>
					{*Favourite Button*}
					{if isset($is_my_fav_active) && $is_my_fav_active}
						{if $product.product_combination > 0}
							{assign var=ipa value=$product.id_product_attribute}
						{else}
							{assign var=ipa value=$product.default_combination}
						{/if}
					{/if}
					{*Favourite Button*}
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
            {*if isset($showroom_with_size)*}
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
		{/foreach}{*$more_shoes_ids|@print_r*}
	</div>{*end of original_selection_shoes *}
{else}
	<div class="out-of-stock"></div>
{/if}


{if isset($more_shoes) AND $more_shoes}
<div class="see_more" id="more_shoes">
	<span>{l s='come to other shoes boutique Want to see?'}</span>
	<a href="" class = "buttonmedium blue" title="{l s='see more'}">{l s='see more'}</a>
</div>
<div id="sh_hidden"></div>
<div class="see_more_products" style="display:none;" id="more_shoes_products">
	<div class="more_container">
		<ul id="more_shoes_list">
		{foreach from=$more_shoes item=product name=MoreShoe}
		<li>
			<div class="showroom_sel_cntnr">
				<div class="showfb_{$product.id_product}">
					<div class="showroom_sel_shoe {if $product.quantity <= 0} sold_out{elseif $product.quantity > 0}{if $product.on_sale && $product.reduction} special-discount{elseif $product.quantity < $last_qties} low_stock{/if}{/if}"
                         {*if isset($showroom_with_size)*} onmouseover="$('#shoe_size_button_{$product.id_product}{if $product.product_combination > 0}_{$product.id_product_attribute}{/if}').toggle()" onmouseout="$('#shoe_size_button_{$product.id_product}{if $product.product_combination > 0}_{$product.id_product_attribute}{/if}').hide()"{*/if*}>

						<a href="{$product.link}" title="{$product.name|escape:html:'UTF-8'}" class="product_image">
							<img class="hover_state prod_img2{$product.id_image}" src="{$link->getImageLink($product.link_rewrite, $product.mouseover_image, 'prodsmall')}" alt="{$product.name|escape:html:'UTF-8'}" style="display:none"/>
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
					</div>{* end of showroom_sel_shoe*}
					<div class="name_color_container" {*if isset($showroom_with_size)*} onmouseover="$('#shoe_size_button_{$product.id_product}{if $product.product_combination > 0}_{$product.id_product_attribute}{/if}').toggle()" onmouseout="$('#shoe_size_button_{$product.id_product}{if $product.product_combination > 0}_{$product.id_product_attribute}{/if}').hide()" {*/if*}>
						{*Favourite Button*}
						{if isset($is_my_fav_active) && $is_my_fav_active}
							{if $product.product_combination > 0}
								{assign var=ipa value=$product.id_product_attribute}
							{else}
								{assign var=ipa value=$product.default_combination}
							{/if}
						{/if}
						{*Favourite Button*}
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
            {*if isset($showroom_with_size)*}
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
		</li>
		{/foreach}
		</ul>
	</div>
</div>

{/if}

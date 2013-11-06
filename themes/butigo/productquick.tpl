<script type="text/javascript">
//<![CDATA[
	var color_flag = {if $color_flag}true{else}false{/if};
	var url_product_attribute = 'undefined';
var color_shoe_combination = {$color_shoe_combination}; 
{if $color_flag}
	var color_ids = new Array();
	{foreach from=$color_combination key='id_color' item='combination'  name='f_defaultImages'}
		color_ids[{$smarty.foreach.f_defaultImages.index}] =  {$id_color};
	{/foreach}

	var jqZoomEnabled = {if $jqZoomEnabled}true{else}false{/if};
	{if $url_product_attribute}
		var url_product_attribute = {$url_product_attribute};
		var customerShoeSize = {$customer_shoe_size};
		
		var prod_data = new Array();
		var ipa = new Array();

		{foreach from=$color_combination key=id_color item=color_comb}
			{if isset($color_comb.product_data)}
				prod_data[{$id_color|intval}]= new Array();
				{*ipa[{$id_color|intval}]= new Array();
				{foreach from=$color_comb.ipa item=ipa name=ipaLoop}
					ipa[{$id_color|intval}]= {$ipa};
				{/foreach}*}
				{foreach from=$color_comb.product_data key=id_size item=size}
					prod_data[{$id_color|intval}].push({ldelim}'id' : '{$id_size}','size' : '{$size.size}','quantity' : '{$size.quantity}','ipa' : '{$size.ipa}'{rdelim});
				{/foreach}
			{/if}
		{/foreach}
		{foreach from=$groups key=id_attribute_group item=group}
			{if $id_attribute_group != 2 && $group.attributes|@count}
				var default_shoesize = {$group.default};
				var id_attribute_group = {$id_attribute_group};
			{/if}
		{/foreach}
	{/if}
{/if}

{if isset($groups)}
	// Combinations
	var combinations = new Array();
	var comb_attribute = new Array();
	{foreach from=$combinations key=idCombination item=combination}
		{*addCombination({$idCombination|intval}, new Array({$combination.list}), {$combination.quantity}, {$combination.price}, {$combination.ecotax}, {$combination.id_image}, '{$combination.reference|addslashes}', {$combination.unit_impact}, {$combination.minimal_quantity});*}
		comb_attribute = new Array({$combination.list})	;//alert(comb_attribute);
		combinations.push({ldelim}
						'idCombination' : '{$idCombination}',
						'quantity'		: '{$combination.quantity}',
						'idsAttributes' :  comb_attribute,
						'price'			: '{$combination.price}',
						'ecotax'		: '{$combination.ecotax}',
						'image'			: '{$combination.id_image}',
						'reference'		: '{$combination.reference|addslashes}'
					{rdelim});

	{/foreach}
{/if}


	{literal}
	function  showSizeSelection(msg)
	{
			if(!$('.product_choices li').hasClass('picked'))
			{
				alert(msg);
				return false;
			}
			else
				return true;

	}
{/literal}
//]]>
</script>

<script src="{$js_dir}tools.js" type="text/javascript"></script>
<script src="{$js_dir}productquick.js" type="text/javascript"></script>
<script src="{$content_dir}js/jquery/slides.min.jquery.js" type="text/javascript"></script>

<script type="text/javascript">
//<![CDATA[
{foreach from=$color_combination key='id_color' item='combination'}
{literal}
	$(function(){
		$('#slides_{/literal}{$id_color}{literal}').slides({
			generatePagination: false,
			start: 1
		});
	});
{/literal}
{/foreach}
{literal}
	$(function(){
		$('#slides').slides({
			generatePagination: false,
			start: 1
		});
	});
{/literal}

//]]>
</script>


{* start - multi color product *}
{if $color_flag}
{foreach from=$color_combination key='id_color' item='combination'}
<div id="fancy-quick-{$id_color}" class="fancy-quick {if $url_product_attribute|in_array:$combination.ipa}fancy{else}hidden{/if}">
<div id="left-col">
	<div class="prod_img_slides" id="slides_{$id_color}" >
		<div class="slides_container">
			{foreach from=$combination.images item=image name=thumbnails}
			{if $image.id_image}
				{assign var=imageIds value="`$product->id`-`$image.id_image`"}
			
			<div class="slide" id="prod_img_{$image.id_image}">
				<p>
					<img src="{$link->getImageLink($product->link_rewrite, $imageIds, 'medium')}" alt="{$image.legend|htmlspecialchars}" height="435" width="435" />
				</p>
			</div>
			{/if}
			{/foreach}
		</div>
		<a href="#" class="prev"></a>
		<a href="#" class="next"></a>
	</div>
</div>
<div id="rightcol">
	<h5>{$product->name|escape:'htmlall':'UTF-8'}</h5>
	<p>{$product->description_short}</p>
	<a class="view_details track_area_trigger" href="{$link->getProductLink($product->id, $combination.default_ipa, $product->link_rewrite, $product->category)|escape:'htmlall':'UTF-8'}">
		<span>{l s='View Details'}</span>
	</a>
	<div id="color_size_container">
		{*start - color *}
		{if isset($colors) && $colors}
		<div id="color_picker">
			<label>{l s='Select color' js=1}</label>
			<ul id="color_to_pick_list">
			{foreach from=$colors key='id_attribute' item='color'}
				<li {if $color.attributes_quantity>0} class ="chover"{/if}>{*$id_attribute} {$color.id_product_attribute*}
					<a id="color_{$id_attribute|intval}_{$id_color}" class="color_pick" style="background: {$color.value};" onclick="updateColor({$id_attribute|intval}, {$color.id_product_attribute});" title="{$color.name}">{if file_exists($col_img_dir|cat:$id_attribute|cat:'.jpg')}<img src="{$img_col_dir}{$id_attribute}.jpg" alt="{$color.name}" width="20" height="20" />{/if}</a>
				</li>
			{/foreach}
			</ul>
		</div>{*end of color_picker*}
		{/if}
		{*end- color *}

		{* start -shoesize*}
		{if isset($groups)}
			{foreach from=$groups key=id_attribute_group item=group}
			{if $id_attribute_group != 2 && $group.attributes|@count}
			<div class="choices_group" id="choices_group_{$id_attribute_group|intval}_{$id_color}">
				<label>{$group.name|escape:'htmlall':'UTF-8'}</label>
				<ul class="product_choices">
					{foreach from=$combination.product_data key=id_size item=size}
						<li class ="{if $size.quantity>0}stock{else}no_stock{/if}">
							{if $size.quantity>0}
							<a id="choice_{$id_size|intval}_{$id_color}" class="choice" onclick="updateProductChoiceSelect({$id_size|intval}, {$id_attribute_group|intval}, {$id_color});" title="{$size.size|escape:'htmlall':'UTF-8'}">
								{$size.size}
							</a>
							{else}
								{$size.size}
							{/if}
						</li>
					{/foreach}
				</ul>
			</div>
			{/if}
			{/foreach}
		{/if}
		{*end - shoesize*}
	</div>{*end - color_size_container*}

	{* start - form *}
	<div id="order_options">
		<form id="buy_block" {if $PS_CATALOG_MODE AND !isset($groups) AND $product->quantity > 0}class="hidden"{/if} action="{$link->getPageLink('cart.php')}" method="post" {if $color_shoe_combination == 1} onsubmit="return showSizeSelection('{l s='Please select shoe size'}');" {/if}>
			{* hidden datas *}
			<p class="hidden">
				<input type="hidden" name="token" value="{$static_token}" />
				<input type="hidden" name="id_product" value="{$product->id|intval}" id="product_page_product_id" />
				<input type="hidden" name="add" value="1" />
				<input type="hidden" name="id_product_attribute" id="idCombination_{$id_color}" value="" />
			</p>
			{if isset($groups)}
			{* attributes *}
			<div id="attributes_{$id_color}" class="hidden">
			{foreach from=$groups key=id_attribute_group item=group}
			{if $group.attributes|@count}
			<p>
				<label for="group_{$id_attribute_group|intval}">{$group.name|escape:'htmlall':'UTF-8'} :</label>
				{assign var="groupName" value="group_$id_attribute_group"}
				<select name="{$groupName}" id="group_{$id_attribute_group|intval}_{$id_color}"> {* onchange="javascript:findCombination();{if $colors|@count > 0}$('#wrapResetImages').show('slow');{/if};"*}
					{foreach from=$group.attributes key=id_attribute item=group_attribute}
						<option value="{$id_attribute|intval}"{if (isset($smarty.get.$groupName) && $smarty.get.$groupName|intval == $id_attribute) || $customerShoeSizeDefault == $group_attribute || $id_attribute == $id_color} selected="selected"{/if} title="{$group_attribute|escape:'htmlall':'UTF-8'}">{$group_attribute|escape:'htmlall':'UTF-8'}</option>
					{/foreach}
				</select>
			</p>
			{/if}
			{/foreach}
			</div>
			{/if}

			
			{if $combination.qty == 0}
				<div class="out-of-stock"></div>
			{else}
				<div id="check_out_btn">
					<input type="submit" name="Submit" class ="addto cart" value="{l s='Add_To_cart'}"/>
				</div>
			{/if}
			{* start - Social Links *}
			<div id="fb_like">
				{*Favourite Button*}
				{if isset($is_my_fav_active) && $is_my_fav_active}
				<span id="qv_ajax_response_{$product->id}_{$combination.default_ipa}">
					{if isset($my_fav_ipa) && isset($my_fav_ids) && $combination.default_ipa|in_array:$my_fav_ipa && $product->id|intval|in_array:$my_fav_ids}
						<a href="javascript:;" id="qv_faved_{$product->id}_{$combination.default_ipa}"  class="qv_favorite_flag in_myfavorite"  onclick="FavProductRemove('qv_mutiple_products', 'delete', '{$product->id|intval}',{$combination.default_ipa}),true">
							<span id="fav_text">{l s='Remove From Favourites'}</span>
						</a>
					{else}
						<a href="javascript:;" id="qv_to_fav_{$product->id}_{$combination.default_ipa}" class="qv_favorite_flag"  onclick="FavlistCart('qv_mutiple_products', 'add', '{$product->id|intval}',{$combination.default_ipa}, document.getElementById('quantity_wanted').value,true); return false;">
							<span id="fav_text">{l s='Add to Favourites'}</span>
						</a>
					{/if}
				</span>
				{/if}
				{*Favourite Button*}
		        <span id="fblike">
					<fb:like href="{$base_dir}" layout="button_count" show_faces="false" width="125" font="arial"></fb:like>
				</span>
				{*<div id="fb_count"></div>*}
			</div>
			{* end - Social Links *}
		</form>
	</div>{* end - form *}
</div>{* end- right col *}
</div>{* end- fancy-quick*}
{/foreach}
{* end - multi color product *}

{else}
{* start - single product *}
	<div id="fancy-quick">
	<div id="left-col">
		<div id="slides">
			<div class="slides_container">
				{foreach from=$images item=image name=thumbnails}
				{assign var=imageIds value="`$product->id`-`$image.id_image`"}
				<div class="slide" id="prod_img_{$image.id_image}">
					<p>
						<img src="{$link->getImageLink($product->link_rewrite, $imageIds, 'medium')}" alt="{$image.legend|htmlspecialchars}" height="435" width="435" />
					</p>
				</div>
				{/foreach}
			</div>
			<a href="#" class="prev"></a>
			<a href="#" class="next"></a>
		</div>
	</div>
	<div id="rightcol">
		<h5>{$product->name|escape:'htmlall':'UTF-8'}</h5>
		<p>{$product->description_short}</p>
		<a class="view_details track_area_trigger" href="{$link->getProductLink($product->id, 0 , $product->link_rewrite, $product->category)|escape:'htmlall':'UTF-8'}">
			<span>{l s='View Details'}</span>
		</a>
		{* start -  size code *}
		<div id="color_size_container">
		{if isset($groups)}
				{foreach from=$groups key=id_attribute_group item=group}
					{if $id_attribute_group != 2 && $group.attributes|@count}
					<div class="choices_group" id="choices_group_{$id_attribute_group|intval}">
						<label>{$group.name|escape:'htmlall':'UTF-8'}</label>
						{assign var='groupName' value='group_'|cat:$id_attribute_group}
						<ul class="product_choices">
						  {foreach from=$group.attributes key=id_attribute item=group_attribute}
								<li class ="{if $group_attribute|in_array:$attr_q}no_stock{else}stock{/if}{if $customerShoeSizeDefault == $group_attribute} picked{/if}">
									{if $group_attribute|in_array:$attr_q}
										{$group_attribute|escape:'htmlall':'UTF-8'}
									{else}
									<a id="choice_{$id_attribute|intval}" class="choice" onclick="updateProductChoiceSelectSingle({$id_attribute|intval}, {$id_attribute_group|intval}, 0 );" title="{$group_attribute|escape:'htmlall':'UTF-8'}">
										{$group_attribute|escape:'htmlall':'UTF-8'}
									</a>
									{/if}
								</li>
											
							{/foreach}
						</ul>
					</div>
					{/if}
				{/foreach}
			{/if}
		</div>
		{* end - size code *}

		{* start - form *}
		<div id="order_options">
		<form id="buy_block" {if $PS_CATALOG_MODE AND !isset($groups) AND $product->quantity > 0}class="hidden"{/if} action="{$link->getPageLink('cart.php')}" method="post" {if $color_shoe_combination == 1} onsubmit="return  showSizeSelection('{l s='Please select shoe size'}');" {/if}>
			{* hidden datas *}
			<p class="hidden">
				<input type="hidden" name="token" value="{$static_token}" />
				<input type="hidden" name="id_product" value="{$product->id|intval}" id="product_page_product_id" />
				<input type="hidden" name="add" value="1" />
				<input type="hidden" name="id_product_attribute" id="idCombination" value="" />
			</p>
			{if isset($groups)}
			{* attributes *}
			<div id="attributes" class="hidden">
			{foreach from=$groups key=id_attribute_group item=group}
			{if $group.attributes|@count}
			<p>
				<label for="group_{$id_attribute_group|intval}">{$group.name|escape:'htmlall':'UTF-8'} :</label>
				{assign var="groupName" value="group_$id_attribute_group"}
				<select name="{$groupName}" id="group_{$id_attribute_group|intval}"> {* onchange="javascript:findCombination();{if $colors|@count > 0}$('#wrapResetImages').show('slow');{/if};"*}
					{foreach from=$group.attributes key=id_attribute item=group_attribute}
						<option value="{$id_attribute|intval}"{if (isset($smarty.get.$groupName) && $smarty.get.$groupName|intval == $id_attribute) || $customerShoeSizeDefault == $group_attribute || $id_attribute == $default_color} selected="selected"{/if} title="{$group_attribute|escape:'htmlall':'UTF-8'}">{$group_attribute|escape:'htmlall':'UTF-8'}</option>
					{/foreach}
				</select>
			</p>
			{/if}
			{/foreach}
			</div>
			{/if}

			{if $product->quantity == 0}
				<div class="out-of-stock"></div>
			{else}
				<div id="check_out_btn">
					<input type="submit" name="Submit" class ="addto cart" value="{l s='Add To cart'}"/>
				</div>
			{/if}
			{* start - Social Links *}
			<div id="fb_like">
				{*Favourite Button*}
				{if isset($is_my_fav_active) && $is_my_fav_active}
				<div id="qv_ajax_response">
					{if isset($my_fav_ids) && $product->id|intval|in_array:$my_fav_ids}
						<a href="javascript:;" id="qv_faved"  class="qv_favorite_flag in_myfavorite"  onclick="FavProductRemove('qv_single_product','delete', '{$product->id|intval}',{if isset($single_product_default_ipa) && $single_product_default_ipa} {$single_product_default_ipa} {else} document.getElementById('idCombination').value {/if}, true)">
							<span id="fav_text">{l s='Remove From Favourites'}</span>
						</a>
					{else}
						<a href="#" id="qv_to_fav" class="qv_favorite_flag"  onclick="FavlistCart('qv_single_product', 'add', '{$product->id|intval}',{if isset($single_product_default_ipa) && $single_product_default_ipa} {$single_product_default_ipa} {else} document.getElementById('idCombination').value {/if}, document.getElementById('quantity_wanted').value, true); return false;">
							<span id="fav_text">{l s='Add to Favourites'}</span>
						</a>
					{/if}
				</div>
				{/if}
				{*Favourite Button*}
				<span id="fblike">
					<fb:like href="{$base_dir}" layout="button_count" show_faces="false" width="110" font="arial"></fb:like>
				</span>
			</div>

			{* end - Social Links *}
		</form>
		</div> {* end - form *}
	</div>{* end- right col *}
	</div>{* end- fancy-quick*}
{/if}
{* end - multi color product *}

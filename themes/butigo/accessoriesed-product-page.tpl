
{*
* 2007-2011 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2011 PrestaShop SA
*  @version  Release: $Revision: 7164 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{*include file="$tpl_dir./errors.tpl"*}
{if $errors|@count == 0}

<script type="text/javascript">
// <![CDATA[

// PrestaShop internal settings
{*var currencySign = '{$currencySign|html_entity_decode:2:"UTF-8"}';
var currencyRate = '{$currencyRate|floatval}';
var currencyFormat = '{$currencyFormat|intval}';
var currencyBlank = '{$currencyBlank|intval}';
var taxRate = {$tax_rate|floatval};
*}
var url_product_attribute = 'undefined';
var jqZoomEnabled = {if $jqZoomEnabled}true{else}false{/if};
	{*var color_shoe_combination = {$color_shoe_combination}; alert(color_shoe_combination);*}
{if $url_product_attribute}
	var url_product_attribute = {$url_product_attribute};
	var color_shoe_combination = {$color_shoe_combination};//alert("hai"); alert(color_shoe_combination);
	{*{if $color_shoe_combination == 1}
	{var customerShoeSizeDefault = {$customerShoeSizeDefault};}
	var shoesizes = new Array();
	var ipa = new Array();
	{foreach from=$color_combination key=id_color item=color_comb}
		{if isset($color_comb.shoesize)}
		shoesizes[{$id_color|intval}]= new Array();
		{ipa[{$id_color|intval}]= new Array();
		{foreach from=$color_comb.ipa item=ipa name=ipaLoop}
			ipa[{$id_color|intval}]= {$ipa};
		{/foreach}}
		{foreach from=$color_comb.shoesize key=id_size item=size}
			shoesizes[{$id_color|intval}].push({ldelim}'id' : '{$id_size}','size' : '{$size.size}','quantity' : '{$size.quantity}','ipa' : '{$size.ipa}'{rdelim});
		{/foreach}
		{/if}
	{/foreach}
	{foreach from=$groups key=id_attribute_group item=group}
		{if ($group.name != 'Color') && $group.attributes|@count}
			var default_shoesize = {$group.default};
			var id_attribute_group = {$id_attribute_group};
		{/if}
	{/foreach}
	{/if}*}
{/if}
//alert(url_product_attribute);
//JS Hook
var oosHookJsCodeFunctions = new Array();

// Parameters
var id_product = '{$product->id|intval}';
var productHasAttributes = {if isset($groups)}true{else}false{/if};
var quantitiesDisplayAllowed = {if $display_qties == 1}true{else}false{/if};
var quantityAvailable = {if $display_qties == 1 && $product->quantity}{$product->quantity}{else}0{/if};
var allowBuyWhenOutOfStock = {if $allow_oosp == 1}true{else}false{/if};
var availableNowValue = '{$product->available_now|escape:'quotes':'UTF-8'}';
var availableLaterValue = '{$product->available_later|escape:'quotes':'UTF-8'}';
{*
var productPriceTaxExcluded = {$product->getPriceWithoutReduct(true)|default:'null'} - {$product->ecotax};
var reduction_percent = {if $product->specificPrice AND $product->specificPrice.reduction AND $product->specificPrice.reduction_type == 'percentage'}{$product->specificPrice.reduction+100}{else}0{/if}; //replace + with star
var reduction_price = {if $product->specificPrice AND $product->specificPrice.reduction AND $product->specificPrice.reduction_type == 'amount'}{$product->specificPrice.reduction}{else}0{/if};
var specific_price = {if $product->specificPrice AND $product->specificPrice.price}{$product->specificPrice.price}{else}0{/if};
var specific_currency = {if $product->specificPrice AND $product->specificPrice.id_currency}true{else}false{/if};
var group_reduction = '{$group_reduction}';
var default_eco_tax = {$product->ecotax};
var ecotaxTax_rate = {$ecotaxTax_rate};
*}
var currentDate = '{$smarty.now|date_format:'%Y-%m-%d %H:%M:%S'}';
var maxQuantityToAllowDisplayOfLastQuantityMessage = {$last_qties};
{*
var noTaxForThisProduct = {if $no_tax == 1}true{else}false{/if};
var displayPrice = {$priceDisplay};
*}
var productReference = '{$product->reference|escape:'htmlall':'UTF-8'}';
{*
var productAvailableForOrder = {if (isset($restricted_country_mode) AND $restricted_country_mode) OR $PS_CATALOG_MODE}'0'{else}'{$product->available_for_order}'{/if};
var productShowPrice = '{if !$PS_CATALOG_MODE}{$product->show_price}{else}0{/if}';
var productUnitPriceRatio = '{$product->unit_price_ratio}';
var idDefaultImage = {if isset($cover.id_image_only)}{$cover.id_image_only}{else}0{/if};
*}

// Customizable field
var img_ps_dir = '{$img_ps_dir}';
var customizationFields = new Array();
{assign var='imgIndex' value=0}
{assign var='textFieldIndex' value=0}
{foreach from=$customizationFields item='field' name='customizationFields'}
	{*assign var="key" value="pictures_`$product->id`_`$field.id_customization_field`"*}
	{assign var='key' value='pictures_'|cat:$product->id|cat:'_'|cat:$field.id_customization_field}
	customizationFields[{$smarty.foreach.customizationFields.index|intval}] = new Array();
	customizationFields[{$smarty.foreach.customizationFields.index|intval}][0] = '{if $field.type|intval == 0}img{$imgIndex++}{else}textField{$textFieldIndex++}{/if}';
	customizationFields[{$smarty.foreach.customizationFields.index|intval}][1] = {if $field.type|intval == 0 && isset($pictures.$key) && $pictures.$key}2{else}{$field.required|intval}{/if};
{/foreach}

// Images
var img_prod_dir = '{$img_prod_dir}';
var combinationImages = new Array();

{*if isset($combinationImages)*}
	{foreach from=$combinationImages item='combination' key='combinationId' name='f_combinationImages'}
		combinationImages[{$combinationId}] = new Array();
		{foreach from=$combination item='image' name='f_combinationImage'}
			combinationImages[{$combinationId}][{$smarty.foreach.f_combinationImage.index}] = {$image.id_image|intval};
		{/foreach}
	{/foreach}
{*/if*}

combinationImages[0] = new Array();
{*if isset($images)*}
	{foreach from=$images item='image' name='f_defaultImages'}
		combinationImages[0][{$smarty.foreach.f_defaultImages.index}] = {$image.id_image};
	{/foreach}
{*/if*}

// Translations

var doesntExist = '{l s='The product does not exist in this model. Please choose another.' js=1}';
var doesntExistNoMore = '{l s='This product is no longer in stock' js=1}';
var doesntExistNoMoreBut = '{l s='with those attributes but is available with others' js=1}';

var uploading_in_progress = '{l s='Uploading in progress, please wait...' js=1}';
var fieldRequired = '{l s='Please fill in all required fields, then save the customization.' js=1}';

{if isset($groups)}
	// Combinations
	{foreach from=$combinations key=idCombination item=combination}
		addCombination({$idCombination|intval}, new Array({$combination.list}), {$combination.quantity}, {$combination.price}, {$combination.ecotax}, {$combination.id_image}, '{$combination.reference|addslashes}', {$combination.unit_impact}, {$combination.minimal_quantity});
	{/foreach}
	// Colors
	{if $colors|@count > 0}
		{if $product->id_color_default}var id_color_default = {$product->id_color_default|intval};{/if}
	{/if}
{/if}



{literal}
	function showSizeSelection(msg , accessoryMsg)
	{
			if(!$('.product_choices li').hasClass('picked'))
			{
				alert(msg);
				return false;
			}else if (! $('input[name="accessorieProduct"]:checked').val()) {
                            alert(accessoryMsg);
                            return false;
                        }

			else
				return true;


	}
{/literal}

//]]>
</script>
{*<script type="text/javascript" src="{$js_dir}shipping.js"></script>
{include file="$tpl_dir./breadcrumb.tpl"}
<div id="primary_block" class="clearfix">
	<h1>{$product->name|escape:'htmlall':'UTF-8'}</h1>

	{if isset($adminActionDisplay) && $adminActionDisplay}
	<div id="admin-action">
		<p>{l s='This product is not visible to your customers.'}
		<input type="hidden" id="admin-action-product-id" value="{$product->id}" />
		<input type="submit" value="{l s='Publish'}" class="exclusive" onclick="submitPublishProduct('{$base_dir}{$smarty.get.ad}', 0)"/>
		<input type="submit" value="{l s='Back'}" class="exclusive" onclick="submitPublishProduct('{$base_dir}{$smarty.get.ad}', 1)"/>
		</p>
		<div class="clear" ></div>
		<p id="admin-action-result"></p>

	</div>
	{/if}

	{if isset($confirmation) && $confirmation}
	<p class="confirmation">
		{$confirmation}
	</p>
	{/if}


	<!-- right infos-->
	<div id="pb-right-column">
*}
{* deal - start *}
{if (isset($deal_id) && $deal_id == $product->id && isset($deal_time) && $deal_time > 0)}
<script type="text/javascript">
{literal}
// <![CDATA[
	 $(document).ready(function() {
		var untilTime = $("div#discount_clock").attr("class");
		$("div#discount_clock").countdown({
						until: untilTime,
						format: 'HMS',
						compact: true,
						description: ''
         });
	});
//]]>
{/literal}
</script>


<div id="product_deal">
	<img src="{$img_dir}product/dailydealhead.png" id="deal_img"  alt="{l s='Deal image'}" />
	<div id="product_deal_time">
		<div id="product_deal_img">
			{if $deal_time != 0}
				<div id="discount_clock" class="{$deal_time}" >{$deal_time}</div>
			{/if}
			<img src="{$img_dir}product/timeleftpink.gif" id="deal_time"  alt="{l s='Deal image'}" />
		</div>
	</div>
</div>
{/if}
{* deal - start *}

{* displays each product with the attributes*}
<div id="left-col">
{*<div id="pb-left-column">*}
	{*include file=$tpl_dir./errors.tpl*}
	<div class="product_images_container">
                {if isset($images) && count($images) > 0}
		{* thumbnails *}
		<div id="views_block" {if isset($images) && count($images) < 2}class="hidden"{/if} style="margin:0 15px 0 0">
            <div id="thumbs_list" style="margin:0 15px 0 0">
                <ul style="width:{math equation="width * nbImages" width=69 nbImages=$images|@count}px" id="thumbs_list_frame">
                    {foreach from=$images item=image name=thumbnails}
                        {if $smarty.foreach.thumbnails.first || $smarty.foreach.thumbnails.last}
                            {assign var=imageIds value="`$product->id`-`$image.id_image`"}
                            <li id="thumbnail_{$image.id_image}">
                                    <a href="{$link->getImageLink($product->link_rewrite, $imageIds, 'large')}" rel="useZoom: 'cloudzoom1', smallImage: '{$link->getImageLink($product->link_rewrite, $imageIds, 'medium')}'" class="{if $jqZoomEnabled}cloud-zoom-gallery{/if}" title="{$image.legend|htmlspecialchars}">
                                            <img id="thumb_{$image.id_image}" src="{$link->getImageLink($product->link_rewrite, $imageIds, 'prodthumb')}" alt="{$image.legend|htmlspecialchars}" height="{$thumbSize.height}" width="{$thumbSize.width}" />
                                    </a>
                            </li>
                        {else}
                            {assign var="imagePosition" value=$smarty.foreach.thumbnails.index-1}
                            {assign var="accessory" value=$accessories[$imagePosition]}
                            {assign var=imageIds value="`$product->id`-`$image.id_image`"}
                            <li id="thumbnail_{$image.id_image}" class="accessories-products" productId="{$accessory.id_product}" productAttributeId="{$accessory.id_product_attribute}">
                                <a href="{$link->getImageLink($product->link_rewrite, $imageIds, 'large')}" rel="useZoom: 'cloudzoom1', smallImage: '{$link->getImageLink($product->link_rewrite, $imageIds, 'medium')}'" class="{if $jqZoomEnabled}cloud-zoom-gallery{/if}" title="{$image.legend|htmlspecialchars}">
                                    <img id="thumb_{$image.id_image}" src="{$link->getImageLink($product->link_rewrite, $imageIds, 'prodthumb')}" alt="{$image.legend|htmlspecialchars}" height="{$thumbSize.height}" width="{$thumbSize.width}" />
                                </a>
                            </li>
                        {/if}
                    {/foreach}
                </ul>
            </div>{*end of thumbs_list*}
            <div id="thumbs_list2" style="float:left; width:75px">
                <ul style="width:69px; float:left" id="thumbs_list2_frame">
                    {if isset($product_with_still_life)}
                    <li style="width:60px; height:60px; margin:10px 5px 0 0; cursor:pointer" onclick="$('#medium_image').show()">
                        <img src="{$img_dir}product/thumb_sl_{$product->id}.jpg" alt="{$img_dir}product/thumb_sl_{$product->id}.jpg" height="{$thumbSize.height}" width="{$thumbSize.width}" />
                    </li>
                    {elseif isset($product_with_model)}
                    <li style="width:60px; height:60px; margin:10px 5px 0 0; cursor:pointer" onclick="$('#medium_image').show()">
                        <img src="{$img_dir}product/thumb_wm_{$product->id}.jpg" alt="{$img_dir}product/thumb_wm_{$product->id}.jpg" height="{$thumbSize.height}" width="{$thumbSize.width}" />
                    </li>
                    {elseif isset($product_with_video)}
                    <li style="width:60px; height:60px; margin:10px 5px 0 0; cursor:pointer" onclick="$('#medium_image').show()">
                        <img src="{$img_dir}product/thumb_vid.gif" alt="{$img_dir}product/thumb_vid.gif" height="{$thumbSize.height}" width="{$thumbSize.width}" />
                    </li>
                    {elseif isset($product_with_celebrity)}
                    <li style="width:60px; height:60px; margin:10px 5px 0 0; cursor:pointer" onclick="$('#medium_image').show()">
                        <img src="{$img_dir}product/thumb_wc_{$product->id}.JPG" alt="{$img_dir}product/thumb_wc_{$product->id}.jpg" height="{$thumbSize.height}" width="{$thumbSize.width}" />
                    </li>
                    {/if}
                </ul>
            </div>{* end of thumbs_list2*}
		</div>{*end of views_block*}
		{/if}

		{if ($product->quantity <= 0 && $url_product_attribute==0) || (isset($color_id_quantity) && $color_id_quantity==0 && $url_product_attribute>0) || ($product->quantity <= 0 && $url_product_attribute>0 && $deal_id== $product->id) || (isset($color_id_quantity) && $color_id_quantity==0 && $url_product_attribute>0 && $deal_id== $product->id)}
			<div id="disc-circle"><div id="disc-no-stock"></div></div>
		{elseif ($product->quantity > 0 && $product->quantity < $last_qties && $url_product_attribute==0) || (isset($color_id_quantity) && $color_id_quantity > 0 && $color_id_quantity < $last_qties && $url_product_attribute>0) || ($product->quantity > 0 && $product->quantity < $last_qties && $url_product_attribute>0 && $deal_id== $product->id) || (isset($color_id_quantity) && $color_id_quantity>0 && $color_id_quantity < $last_qties && $url_product_attribute>0 && $deal_id== $product->id)}
			<div id="disc-circle"><div id="disc-low-stock"></div></div>
		{/if}

		<div id="image-block">
            {if isset($product->out_of_stock) && $product->out_of_stock == 1}
                <div id="back-order"></div>
            {elseif !(($product->quantity <= 0 && $url_product_attribute==0) ||($product->quantity <= 0 && $url_product_attribute==0) || (isset($color_id_quantity) && $color_id_quantity==0 && $url_product_attribute>0) || ($product->quantity <= 0 && $url_product_attribute>0 && $deal_id== $product->id) || (isset($color_id_quantity) && $color_id_quantity==0 && $url_product_attribute>0 && $deal_id== $product->id))}
                <div id="qty-available"{if $color_shoe_combination == 1}style="display:none"{/if}><div>{l s='SON'}&nbsp;<span></span><br />{l s='ÜRÜN'}</div></div>
            {/if}
			<a href="{$link->getImageLink($product->link_rewrite, $cover.id_image, 'large')}" {if $jqZoomEnabled}class="cloud-zoom" id="cloudzoom1" rel="adjustX:-2, adjustY:-2, position:'inside', zoomWidth:{$mediumSize.width}, zoomHeight:{$mediumSize.height}, showTitle:false" title="{$product->name|escape:'htmlall':'UTF-8'}"{/if}>
				{if $have_image}
					<img src="{$link->getImageLink($product->link_rewrite, $cover.id_image, 'medium')}"
						{if $jqZoomEnabled}class="jqzoom" alt="{$link->getImageLink($product->link_rewrite, $cover.id_image, 'medium')}"{else} title="{$product->name|escape:'htmlall':'UTF-8'}" alt="{$product->name|escape:'htmlall':'UTF-8'}" {/if} id="bigpic" width="{$mediumSize.width}" height="{$mediumSize.height}" />
				{else}
					<img src="{$img_prod_dir}{$lang_iso}-default-medium.jpg" id="bigpic" alt="" title="{$product->name|escape:'htmlall':'UTF-8'}" width="{$mediumSize.width}" height="{$mediumSize.height}" />
				{/if}
			</a>
		</div>{* end of image-block*}

		{*<ul id="usefull_link_block">
			{if $HOOK_EXTRA_LEFT}{$HOOK_EXTRA_LEFT}{/if}
			<li><a href="javascript:print();">{l s='Print'}</a><br class="clear" /></li>
			{if $have_image && !$jqZoomEnabled}
			<li><span id="view_full_size" class="span_link">{l s='View full size'}</span></li>
			{/if}
		</ul>*}
	</div>{* end of product_images_container *}

    {* Stilsos Intergation *}
    {if $stilsos_hash}
        <img style="width:1px;height:1px;visibility:hidden;position:absolute;" alt="StilSOS Tracking"
            src="//api.stilsos.com/track/click/?retailer_id=123&product_id={$product->id}&hash={$stilsos_hash}&time={$smarty.now}" />
    {/if}

    {* NanoInteractive Integration *}
    <script type="text/javascript">
        {literal}
        (function(d){
            var HEIAS_PARAMS = [];
            HEIAS_PARAMS.push(['type', 'ppx'], ['ssl', 'auto'], ['n', '6451'], ['cus', '17201']);
            HEIAS_PARAMS.push(['pb', '1']);
            HEIAS_PARAMS.push(['product_id', {/literal}'{$product->id}'{literal}]);

            if (typeof window.HEIAS === 'undefined'){ window.HEIAS = []; }
            window.HEIAS.push(HEIAS_PARAMS);

            var scr = d.createElement('script');
            scr.async = true;
            scr.src = (d.location.protocol === 'https:' ? 'https:' : 'http:') + '//ads.heias.com/x/heias.async/p.min.js';
            var elem = d.getElementsByTagName('script')[0];
            elem.parentNode.insertBefore(scr, elem);
        }(document));
        {/literal}
    </script>

	{* Social Links *}
	<div id="product_sharing">
		<span>{l s='PAYLAŞ'}</span>
		<div id="fb_share">
			<a  href="http://www.facebook.com/share.php"
				onclick='window.open("http://www.facebook.com/sharer.php?u={$link->getProductLink($product, $url_product_attribute)|urlencode}", "{l s='Facebook Share'}", "toolbar=0, status=0, width=626, height=536"); return false;'
				target="_blank" rel="nofollow" title="{l s='Share on Facebook'}">
				{l s='Share on Facebook'}
			</a>
		</div>
		<div id="tt_share">
			<a href="http://twitter.com/share?url={$link->getProductLink($product, $url_product_attribute)|urlencode}&text={l s='Butigo\'da Son Keşfim, Ivana Sert de önermiş: '}&count=none"
			   onclick='window.open("http://twitter.com/share?url={$link->getProductLink($product, $url_product_attribute)|urlencode}&text={l s='Butigo\'da Son Keşfim, Ivana Sert de önermiş: '}&count=none", "{l s='Twitter share'}", "height=450, width=550, resizable=1"); return false;'
			   target="_blank" rel="nofollow" title="{l s='Share on Twitter'}">
				{l s='Share on Twitter'}
			</a>
		</div>
		{*<div id="fb_count">
			<img src= "{$img_dir}buttons/fblike_count.png"alt=""/>
		</div>*}
	</div> {*end of product sharing *}

	{if $logged} {*display these only to logged in users*}
	<div id="fb_comments">
		<fb:comments class="fb-comments" href="{$link->getProductLink($product, $url_product_attribute)|urlencode}" num_posts="3" width="585"></fb:comments>
	</div>
	{/if}{*end if not logged*}
</div> {*end of  left-col *}

{*{if $product->description_short OR $packItems|@count > 0}
<div id="short_description_block">
	{if $product->description_short}
		<div id="short_description_content" class="rte align_justify">{$product->description_short}</div>
	{/if}
	{if $product->description}
	<p class="buttons_bottom_block"><a href="javascript:{ldelim}{rdelim}" class="button">{l s='More details'}</a></p>
	{/if}
	{if $packItems|@count > 0}
		<h3>{l s='Pack content'}</h3>
		{foreach from=$packItems item=packItem}
			<div class="pack_content">
				{$packItem.pack_quantity} x <a href="{$link->getProductLink($packItem.id_product, $packItem.link_rewrite, $packItem.category)}">{$packItem.name|escape:'htmlall':'UTF-8'}</a>
				<p>{$packItem.description_short}</p>
			</div>
		{/foreach}
	{/if}
</div>
{/if}*}

<div id="rightcol">
	<div id="showroom_item_right_container">
		<div class="product_name_wrapper">
			<h1>{$product->name|escape:'htmlall':'UTF-8'}</h1>
			{*favourite a product*}
				{if isset($HOOK_PRODUCT_ACTIONS) && $HOOK_PRODUCT_ACTIONS}{$HOOK_PRODUCT_ACTIONS}{/if}
			{*favourite a product*}
		</div>
		{if !$logged && !$show_site}{*display when logged out*}
		<div id="fb_loggeout">
			<fb:like href="{$link->getProductLink($product, $url_product_attribute)|urlencode}" send="false" layout="button_count" show_faces="false" width="125"></fb:like>
		</div>
		<div id="pin_it">
			<a href="http://pinterest.com/pin/create/button/?url={$link->getProductLink($product, $url_product_attribute)|urlencode}&media={$fb_image_host}{$link->getImageLink($product->link_rewrite, $cover.fb_image, 'prodsmall')}&description={l s='Merhaba, Butigo\'da Ivana Sert benim için bu ayakkabıyı seçti. Sizce bana nasıl gider?'}" class="pin-it-button" count-layout="horizontal"><img src="//assets.pinterest.com/images/PinExt.png" title="Pin It" /></a>
		</div>
		{/if}{* end of if *}
{if $logged || $show_site} {*display these only to logged in users*}
		<div id="product_price">
		{* prices *}
		{if $product->show_price AND !isset($restricted_country_mode) AND !$PS_CATALOG_MODE}
			<p class="price">

				{if !$priceDisplay || $priceDisplay == 2}
					{assign var='productPrice' value=$product->getPrice(true, $smarty.const.NULL, 2)}
					{assign var='productPriceWithoutRedution' value=$product->getPriceWithoutReduct(false, $smarty.const.NULL)}
				{elseif $priceDisplay == 1}
					{assign var='productPrice' value=$product->getPrice(false, $smarty.const.NULL, 2)}
					{assign var='productPriceWithoutRedution' value=$product->getPriceWithoutReduct(true, $smarty.const.NULL)}
				{/if}
				{if $product->on_sale}
					<img src="{$img_dir}onsale_{$lang_iso}.gif" alt="{l s='On sale'}" class="on_sale_img"/>
					<span class="on_sale">{l s='On sale!'}</span>
				{*elseif $product->specificPrice AND $product->specificPrice.reduction AND $productPriceWithoutRedution > $productPrice}
					<span class="discount">{l s='Reduced price!'}</span>*}
				{/if}
				<span class="our_price_display">
				{if $priceDisplay >= 0 && $priceDisplay <= 2}
					{if (isset($deal_id) && $deal_id == $product->id && isset($deal_time) && $deal_time > 0)}<span id="price_without_reduction">{convertPrice price=$productPriceWithoutRedution}</span>{/if}
					<span id="our_price_display" class="{if (isset($deal_id) && $deal_id == $product->id && isset($deal_time) && $deal_time > 0)} deal_price {else} our_price {/if}">{convertPrice price=$productPrice}</span>
						{if isset($tax_enabled) && $tax_enabled  && ((isset($display_tax_label) && $display_tax_label == 1) OR !isset($display_tax_label))}
							{if $priceDisplay == 1}{l s='tax excl.'}{else}{l s='tax incl.'}{/if}
						{/if}
				{/if}
				</span>
				{if $priceDisplay == 2}
					<span id="pretaxe_price"><span id="pretaxe_price_display">{convertPrice price=$product->getPrice(false, $smarty.const.NULL, 2)}</span>&nbsp;{l s='tax excl.'}</span>
				{/if}
				<span id="sub_text">{l s='Design Product.'}</br>{l s='A Limited Number.'}</span>
			</p>
			{*{if $product->specificPrice AND $product->specificPrice.reduction}
				<p id="old_price"><span class="bold">
				{if $priceDisplay >= 0 && $priceDisplay <= 2}
					{if $productPriceWithoutRedution > $productPrice}
						<span id="old_price_display">{convertPrice price=$productPriceWithoutRedution}</span>
							{if $tax_enabled && $display_tax_label == 1}
								{if $priceDisplay == 1}{l s='tax excl.'}{else}{l s='tax incl.'}{/if}
							{/if}
					{/if}
				{/if}
				</span>
				</p>
			{/if}
			{if $product->specificPrice AND $product->specificPrice.reduction_type == 'percentage'}
				<p id="reduction_percent">{l s='(price reduced by'} <span id="reduction_percent_display">{$product->specificPrice.reduction+100}</span> %{l s=')'}</p> // replace star instead of + i
			{/if}
			{if $packItems|@count}
				<p class="pack_price">{l s='instead of'} <span style="text-decoration: line-through;">{convertPrice price=$product->getNoPackPrice()}</span></p>
				<br class="clear" />
			{/if}
			{if $product->ecotax != 0}
				<p class="price-ecotax">{l s='include'} <span id="ecotax_price_display">{if $priceDisplay == 2}{$ecotax_tax_exc|convertAndFormatPrice}{else}{$ecotax_tax_inc|convertAndFormatPrice}{/if}</span> {l s='for green tax'}
					{if $product->specificPrice AND $product->specificPrice.reduction}
					<br />{l s='(not impacted by the discount)'}
					{/if}
				</p>
			{/if}
			{if !empty($product->unity) && $product->unit_price_ratio > 0.000000}
			    {math equation="pprice / punit_price"  pprice=$productPrice  punit_price=$product->unit_price_ratio assign=unit_price}
				<p class="unit-price"><span id="unit_price_display">{convertPrice price=$unit_price}</span> {l s='per'} {$product->unity|escape:'htmlall':'UTF-8'}</p>
			{/if*}
			{*<span style="font-size:12px;color:#7d7d7d;float:right;width:257px;">HER YONE GONDERI UCRETSIZ</span>*}
		{/if}

{*if $quantity_discounts}
<!-- quantity discount -->
<ul class="idTabs">
	<li><a style="cursor: pointer" class="selected">{l s='Quantity discount'}</a></li>
</ul>
<div id="quantityDiscount">
	<table class="std">
		<tr>
			{foreach from=$quantity_discounts item='quantity_discount' name='quantity_discounts'}
				<th>{$quantity_discount.quantity|intval}
				{if $quantity_discount.quantity|intval > 1}
					{l s='quantities'}
				{else}
					{l s='quantity'}
				{/if}
				</th>
			{/foreach}
		</tr>
		<tr>
			{foreach from=$quantity_discounts item='quantity_discount' name='quantity_discounts'}
				<td>
				{if $quantity_discount.price != 0 OR $quantity_discount.reduction_type == 'amount'}
					-{convertPrice price=$quantity_discount.real_value|floatval}
				{else}
    				-{$quantity_discount.real_value|floatval}%
				{/if}
				</td>
			{/foreach}
		</tr>
	</table>
</div>
{/if*}
		</div>{*end of product_price*}

		<div class="rate_it">
		   {$HOOK_EXTRA_RIGHT}
			<div id="user_like">
				<div id="fb_like">
					<fb:like href="{$link->getProductLink($product, $url_product_attribute)}" send="false" layout="button_count" show_faces="false" width="90"></fb:like>
				</div>
				<div id="pin_it">
					<a href="http://pinterest.com/pin/create/button/?url={$link->getProductLink($product, $url_product_attribute)|urlencode}&media={$fb_image_host}{$link->getImageLink($product->link_rewrite, $cover.fb_image, 'prodsmall')}&description={l s='Merhaba, Butigo\'da Ivana Sert benim için bu ayakkabıyı seçti. Sizce bana nasıl gider?'}" class="pin-it-button" count-layout="horizontal"><img src="//assets.pinterest.com/images/PinExt.png" title="Pin It" /></a>
				</div>

			</div>
		</div>
{else}
		<hr>
{/if}{*end if not logged*}
            {*<span class="view_scroll_spacer">
				<a id="view_scroll_left" title="{l s='Other images'}" href="javascript:{ldelim}{rdelim}">{l s='Previous'}</a>
			</span>*}
	<div id="color_size_container">
		{if isset($colors) && $colors}
		<div id="color_picker">
			<label>{l s='Select color' js=1}</label>
			<ul id="color_to_pick_list">
			{foreach from=$colors key='id_attribute' item='color'}
				<li {if $color.attributes_quantity>0} class ="chover" {else} class="color_no_stock" {/if}>
					{*if $color.attributes_quantity>0*}
						{*<a id="color_{$id_attribute|intval}" class="color_pick" style="background: {$color.value};" onclick="updateColorSelect({$id_attribute|intval});$('#wrapResetImages').fadeIn('500');" title="{$color.name}">{if file_exists($col_img_dir|cat:$id_attribute|cat:'.jpg')}<img src="{$img_col_dir}{$id_attribute}.jpg" alt="{$color.name}" width="20" height="20" />{/if}</a>*}
						<a href="{$link->getPageLink('product.php')}?id_product={$product->id}&id_product_attribute={$color.id_product_attribute}" id="color_{$id_attribute|intval}" class="color_pick" {if file_exists($col_img_dir|cat:$id_attribute|cat:'.jpg')} style="background: url({$img_col_dir}{$id_attribute}.jpg);" {else} style="background: {$color.value};" {/if} title="{$color.name}">
							{*if file_exists($col_img_dir|cat:$id_attribute|cat:'.jpg')}
								<img src="{$img_col_dir}{$id_attribute}.jpg" alt="{$color.name}" width="20" height="20" />
							{/if*}
						</a>
					{*else}
						<span style="float:left;height: 28px;width: 28px;background: {$color.value};" >{if file_exists($col_img_dir|cat:$id_attribute|cat:'.jpg')}<img src="{$img_col_dir}{$id_attribute}.jpg" alt="{$color.name}" width="20" height="20" />{/if}</span>
					{/if*}
				</li>
			{/foreach}
			</ul>
		</div>{*end of color_picker*}
		{/if}


{* start - original code *}
		{*{if isset($groups)}
			{foreach from=$groups key=id_attribute_group item=group}
				{if ($group.name != 'Color') && $group.attributes|@count}
				<div class="choices_group" id="choices_group_{$id_attribute_group|intval}">
					<label>{l s='Select'} {$group.name|escape:'htmlall':'UTF-8'}</label>
					{assign var='groupName' value='group_'|cat:$id_attribute_group}
					<ul class="product_choices">
					  {foreach from=$group.attributes key=id_attribute item=group_attribute}
								<li class ="{if $group_attribute|in_array:$attr_q}no_stock{else}stock{/if}{if $customerShoeSizeDefault == $group_attribute} picked{/if}">
									{if $group_attribute|in_array:$attr_q}
											{$group_attribute}
									{else}
									<a id="choice_{$id_attribute|intval}" class="choice" onclick="updateProductChoiceSelect({$id_attribute|intval}, {$id_attribute_group|intval});" title="{$group_attribute|escape:'htmlall':'UTF-8'}">
										{$group_attribute|escape:'htmlall':'UTF-8'}
									</a>
									{/if}
								</li>
						{/foreach}
					</ul>
				</div>
				{/if}
			{/foreach}
		{/if}*}
{* end - original code *}

{* start - changed code *}
	{if isset($groups)}
			{foreach from=$groups key=id_attribute_group item=group}
				{if $id_attribute_group != 2 && $group.attributes|@count}
				<div class="choices_group" id="choices_group_{$id_attribute_group|intval}">
					<h3>{l s='Select'} {$group.name|escape:'htmlall':'UTF-8'}</h3>
					{assign var='groupName' value='group_'|cat:$id_attribute_group}
					<ul class="product_choices">
					  {foreach from=$group.attributes key=id_attribute item=group_attribute}
								{*<li class ="{if $group_attribute|in_array:$attr_q}no_stock{else}stock{/if}{if $customerShoeSizeDefault == $group_attribute} picked{/if}">
									{if $group_attribute|in_array:$attr_q}
											 {$group_attribute}
									{else}*}
										{if $url_product_attribute>0 }{* && $color_shoe_combination == 1*}
											 {foreach from=$color_combination key=id_color item=color_comb}
												{foreach from=$color_comb.shoesize key=id_size item=shoesize}
													{if $id_color==$id_attribute_color && $id_attribute==$id_size }
														<li class ="{if $shoesize.quantity>0}stock{else}no_stock{/if}{if $customerShoeSizeDefault == $group_attribute && $shoesize.quantity>0} picked{/if}">
															{if $shoesize.quantity > 0}
																<a id="choice_{$id_attribute|intval}" class="choice" onclick="updateProductChoiceSelect({$id_attribute|intval}, {$id_attribute_group|intval});" title="{$group_attribute|escape:'htmlall':'UTF-8'}">
																	{$group_attribute}
																</a>
															{else}
																{$group_attribute}
															{/if}
														</li>
													{/if}
												{/foreach}
											  {/foreach}
										{else}
											<li class ="{if $group_attribute|in_array:$attr_q}no_stock{else}stock{/if}{if $customerShoeSizeDefault == $group_attribute} picked{/if}">
												{if $group_attribute|in_array:$attr_q}
													{$group_attribute|escape:'htmlall':'UTF-8'}
												{else}
												<a id="choice_{$id_attribute|intval}" class="choice" onclick="updateProductChoiceSelect({$id_attribute|intval}, {$id_attribute_group|intval});" title="{$group_attribute|escape:'htmlall':'UTF-8'}">
													{$group_attribute|escape:'htmlall':'UTF-8'}
												</a>
												{/if}
											</li>
										{/if}
									{*{/if}
								</li>*}
						{/foreach}
					</ul>

				</div>
				{/if}
			{/foreach}
		{/if}
		{*<span class="view_scroll_spacer">
			<a id="view_scroll_right" title="{l s='Other views'}" href="javascript:{ldelim}{rdelim}">{l s='Next'}</a>
		</span>*}
{* end - changed code *}
    {*<div id="media_product_1"  class="tabs12">*}
					<div class="ui-tab-content-details">
						{if isset($features)}
						{*<h6>{l s='About the style'}</h6>*}
						{foreach from=$features item=feature}
							<li><h3>{$feature.name|escape:'htmlall':'UTF-8'}:</h3> {$feature.value|escape:'htmlall':'UTF-8'}</li>
						{/foreach}
						{/if}
					</div>
				 </div>
	{*</div>*}{* end of color_size_container *}



		{*{if ($product->show_price AND !isset($restricted_country_mode)) OR isset($groups) OR $product->reference OR (isset($HOOK_PRODUCT_ACTIONS) && $HOOK_PRODUCT_ACTIONS)}*}
		{* add to cart form *}
		<div id="order_options">
		<form id="buy_block" {if $PS_CATALOG_MODE AND !isset($groups) AND $product->quantity > 0}class="hidden"{/if} action="{$link->getPageLink('cart.php')}" method="post" {if $color_shoe_combination == 1} onsubmit="return showSizeSelection('{l s='Please select shoe size'}','{l s='Please select any one accessory'}');" {/if}>
			{* hidden datas *}
			<p class="hidden">
				<input type="hidden" name="token" value="{$static_token}" />
				<input type="hidden" name="id_product" value="{$product->id|intval}" id="product_page_product_id" />
				<input type="hidden" name="add" value="1" />
				<input type="hidden" name="id_product_attribute" id="idCombination" value="" />
				<input type="hidden" name="color_shoe_combination" value="{$color_shoe_combination}" />
                                <input type="hidden" name="accessoryProductId" value="{$accessories.0.id_product}" />
                                <input type="hidden" name="accessoryProductAttributeId" value="{$accessories.0.id_product_attribute}" />
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

			{*pq={$product->quantity}-- cq={$color_id_quantity}-- url={$url_product_attribute}-- deal={$deal_id}*}
			{*if ($product->quantity <= 0 && $url_product_attribute==0 ) || ($color_id_quantity==0 && $url_product_attribute>0)*}
			{if ($product->quantity <= 0 && $url_product_attribute==0) || (isset($color_id_quantity) && $color_id_quantity==0 && $url_product_attribute>0) || ($product->quantity <= 0 && $url_product_attribute>0 && $deal_id== $product->id) || (isset($color_id_quantity) && $color_id_quantity==0 && $url_product_attribute>0 && $deal_id== $product->id)}
				<div class="out-of-stock"></div>
			{elseif $product->quantity > 0 && ($product->showing_status == 0 || $product->active == 0)}
					<div class="do_not_buy_btn">
						<input type="button" name="donot_buy" class="buttonlarge pink" value="{l s='SATIŞTA DEĞİL'}" />
					</div>
			{else}

                {if isset($accessories) AND count($accessories) > 0}
                <div class="accessoriesed-products">
                    <label class="accessory-heading">{l s='Accessories'}</label>
                    <ul class="accessoriesed-products-list">
                        {foreach from=$images item=image name=thumbnails}
                            {if $smarty.foreach.thumbnails.first || $smarty.foreach.thumbnails.last}
                            {else}
                                {assign var="imagePosition" value=$smarty.foreach.thumbnails.index-1}
                                {assign var="accessory" value=$accessories[$imagePosition]}
                                {assign var=imageIds value="`$product->id`-`$image.id_image`"}

                                {if $accessory.quantity > 0}
                                    <li class="accessories-products" productId="{$accessory.id_product}" productAttributeId="{$accessory.id_product_attribute}">
                                        <input type="radio" name="accessorieProduct" value="{$accessory.id_product}" />
                                        <label>{$accessory.name|truncate:22:'...':true|escape:'htmlall':'UTF-8'}</label>
                                        <a href="{$link->getImageLink($product->link_rewrite, $imageIds, 'large')}" rel="useZoom: 'cloudzoom1', smallImage: '{$link->getImageLink($product->link_rewrite, $imageIds, 'medium')}'" class="{if $jqZoomEnabled}cloud-zoom-gallery{/if}" title="{$image.legend|htmlspecialchars}"></a>
                                    </li>
                                {/if}
                            {/if}
                        {/foreach}
                    </ul>
                </div>
                {/if}

				{if $logged || !$logged && $show_site}
				<div id="check_out_btn">
					<input type="submit" name="Submit" {if isset($product->out_of_stock) && $product->out_of_stock == 1}class="buttonmedium gray" value="{l s='Back Order'}"{else}class="buttonlarge blue" value="{l s='Add To cart'}"{/if}/>
				</div>
                    {*if isset($pre_order_product)*}
                    {*<div id="backorder_info">*}
                        {*<p>{l s='Estimated Delivery Time:'}&nbsp;&nbsp;<span style="font-weight: bold">{l s='10 days'}</span></p>
                    </div>*}
                    {*else*}
                    <div id="bestfit_guarantee">
                        <p>{l s='Best Fit Guarantee'}&nbsp;<span class="tooltip"><img src="{$img_dir}product/tooltip.gif" alt="{l s='tooltip'}" /><span>{l s='100% best fit guarantee'}</span></span></p>
                    </div>
                    {*/if*}
				{/if}

			{/	if}
		</form>
		</div>{* end of order options*}
        <div class="clear"></div>
		<div id="newbankoption">
			<img src="{$img_dir}product/payment_details_new2.gif" alt="{l s='Bank Options'}"/>
		</div>

			{* availability *}
			{*<p id="availability_statut"{if ($product->quantity <= 0 && !$product->available_later && $allow_oosp) OR ($product->quantity > 0 && !$product->available_now) OR !$product->available_for_order OR $PS_CATALOG_MODE} style="display: none;"{/if}>
				<span id="availability_label">{l s='Availability:'}</span>
				<span id="availability_value"{if $product->quantity <= 0} class="warning_inline"{/if}>
					{if $product->quantity <= 0}{if $allow_oosp}{$product->available_later}{else}{l s='This product is no longer in stock'}{/if}{else}{$product->available_now}{/if}
				</span>
			</p>

			<p class="warning_inline" id="last_quantities"{if ($product->quantity > $last_qties OR $product->quantity <= 0) OR $allow_oosp OR !$product->available_for_order OR $PS_CATALOG_MODE} style="display: none;"{/if} >{l s='Warning: Last items in stock!'}</p>

			<p id="product_reference" {if isset($groups) OR !$product->reference}style="display: none;"{/if}><label for="product_reference">{l s='Reference :'} </label><span class="editable">{$product->reference|escape:'htmlall':'UTF-8'}</span></p>

			<!-- quantity wanted -->
			<p id="quantity_wanted_p"{if (!$allow_oosp && $product->quantity <= 0) OR $virtual OR !$product->available_for_order OR $PS_CATALOG_MODE} style="display: none;"{/if}>
				<label>{l s='Quantity :'}</label>
				<input type="text" name="qty" id="quantity_wanted" class="text" value="{if isset($quantityBackup)}{$quantityBackup|intval}{else}{if $product->minimal_quantity > 1}{$product->minimal_quantity}{else}1{/if}{/if}" size="2" maxlength="3" {if $product->minimal_quantity > 1}onkeyup="checkMinimalQuantity({$product->minimal_quantity});"{/if} />
			</p>

			<!-- minimal quantity wanted -->
			<p id="minimal_quantity_wanted_p"{if $product->minimal_quantity <= 1 OR !$product->available_for_order OR $PS_CATALOG_MODE} style="display: none;"{/if}>{l s='You must add '}<b id="minimal_quantity_label">{$product->minimal_quantity}</b>{l s=' as a minimum quantity to buy this product.'}</p>
			{if $product->minimal_quantity > 1}
			<script type="text/javascript">
				checkMinimalQuantity();
			</script>
			{/if}

			<!-- availability -->
			<p id="availability_statut"{if ($product->quantity <= 0 && !$product->available_later && $allow_oosp) OR ($product->quantity > 0 && !$product->available_now) OR !$product->available_for_order OR $PS_CATALOG_MODE} style="display: none;"{/if}>
				<span id="availability_label">{l s='Availability:'}</span>
				<span id="availability_value"{if $product->quantity <= 0} class="warning_inline"{/if}>
					{if $product->quantity <= 0}{if $allow_oosp}{$product->available_later}{else}{l s='This product is no longer in stock'}{/if}{else}{$product->available_now}{/if}
				</span>
			</p>

			<!-- number of item in stock -->
			{if ($display_qties == 1 && !$PS_CATALOG_MODE && $product->available_for_order)}
			<p id="pQuantityAvailable"{if $product->quantity <= 0} style="display: none;"{/if}>
				<span id="quantityAvailable">{$product->quantity|intval}</span>
				<span {if $product->quantity > 1} style="display: none;"{/if} id="quantityAvailableTxt">{l s='item in stock'}</span>
				<span {if $product->quantity == 1} style="display: none;"{/if} id="quantityAvailableTxtMultiple">{l s='items in stock'}</span>
			</p>
     		{/if}
			<!-- Out of stock hook -->
			<p id="oosHook"{if $product->quantity > 0} style="display: none;"{/if}>
				{$HOOK_PRODUCT_OOS}
			</p>

			<p class="warning_inline" id="last_quantities"{if ($product->quantity > $last_qties OR $product->quantity <= 0) OR $allow_oosp OR !$product->available_for_order OR $PS_CATALOG_MODE} style="display: none;"{/if} >{l s='Warning: Last items in stock!'}</p>

			{if $product->online_only}
				<p>{l s='Online only'}</p>
			{/if}

			<p{if (!$allow_oosp && $product->quantity <= 0) OR !$product->available_for_order OR (isset($restricted_country_mode) AND $restricted_country_mode) OR $PS_CATALOG_MODE} style="display: none;"{/if} id="add_to_cart" class="buttons_bottom_block"><input type="submit" name="Submit" value="{l s='Add to cart'}" class="exclusive" /></p>
			{if isset($HOOK_PRODUCT_ACTIONS) && $HOOK_PRODUCT_ACTIONS}{$HOOK_PRODUCT_ACTIONS}{/if}
			<div class="clear"></div>
		</form>
		{/if}
		{if $HOOK_EXTRA_RIGHT}{$HOOK_EXTRA_RIGHT}{/if}*}
	</div> {*showroom_item_right_container*}

	{if $product->description || $features || $accessories || $HOOK_PRODUCT_TAB || $attachments}
	{* description and features *}
	<div id="media">
			<div id="media_tabs">
				{if $product->description}
				<div id="media_button_0" class="media_active" onclick="toggle_tabs('media',0,1)">
					{*<span>{l s='How to wear it'}</span>*}
					<h2>{l s='Açıklama'}</h2>
				</div>
				{/if}
				{*if $features}
				<div id="media_button_1" onclick="toggle_tabs('media',1,1)">
					<span class="clip">{l s='About the style'}</span>
				</div>
				{/if*}
			</div>
			<div id ="media_middle">
				<div id="media_product_0" class="tabs12" style="">
					<div class="ui-tab-content">
						{if $product->description}
						{*<h6>{l s='How to wear it'}</h6>*}
						{$product->description}
						{/if}
					</div>
				</div>

			</div>
	</div>{*end of media *}

	{if $logged} {*display these only to logged in users*}
	<div class="stylist_recommendation">
	{if $attachments}
		{foreach from=$attachments item=attachment}
			<img src="{$img_ps_dir}stylists/recomend/{$attachment.file|escape:'htmlall':'UTF-8'}"  alt="{l s='Recommended for you by'}{$attachment.name|escape:'htmlall':'UTF-8'}"/>
			<p>{$attachment.name|escape:'htmlall':'UTF-8'}</p><br/>
			<span>{l s='Stylist'}</span>
		{/foreach}
	{/if}
	</div>
	{/if}
	{/if}{*end of if product->description *}


{*<div id="more_info_block" class="clear">
	<ul id="more_info_tabs" class="idTabs idTabsShort">
		{if $product->description}<li><a id="more_info_tab_more_info" href="#idTab1">{l s='More info'}</a></li>{/if}
		{if $features}<li><a id="more_info_tab_data_sheet" href="#idTab2">{l s='Data sheet'}</a></li>{/if}
		{if $attachments}<li><a id="more_info_tab_attachments" href="#idTab9">{l s='Download'}</a></li>{/if}
		{if isset($accessories) AND $accessories}<li><a href="#idTab4">{l s='Accessories'}</a></li>{/if}
		{$HOOK_PRODUCT_TAB}
	</ul>
	<div id="more_info_sheets" class="sheets align_justify">
	{if $product->description}
		<!-- full description -->
		<div id="idTab1" class="rte">{$product->description}</div>
	{/if}
	{if $features}
		<!-- product's features -->
		<ul id="idTab2" class="bullet">
		{foreach from=$features item=feature}
			<li><span>{$feature.name|escape:'htmlall':'UTF-8'}</span> {$feature.value|escape:'htmlall':'UTF-8'}</li>
		{/foreach}
		</ul>
	{/if}
	{if $attachments}
		<ul id="idTab9" class="bullet">
		{foreach from=$attachments item=attachment}
			<li><a href="{$link->getPageLink('attachment.php', true)}?id_attachment={$attachment.id_attachment}">{$attachment.name|escape:'htmlall':'UTF-8'}</a><br />{$attachment.description|escape:'htmlall':'UTF-8'}</li>
		{/foreach}
		</ul>
	{/if}
	{if isset($accessories) AND $accessories}
		<!-- accessories -->
		<ul id="idTab4" class="bullet">
			<div class="block products_block accessories_block clearfix">
				<div class="block_content">
					<ul>
					{foreach from=$accessories item=accessory name=accessories_list}
						{assign var='accessoryLink' value=$link->getProductLink($accessory.id_product, $accessory.link_rewrite, $accessory.category)}
						<li class="ajax_block_product {if $smarty.foreach.accessories_list.first}first_item{elseif $smarty.foreach.accessories_list.last}last_item{else}item{/if} product_accessories_description">
							<h5><a href="{$accessoryLink|escape:'htmlall':'UTF-8'}">{$accessory.name|truncate:22:'...':true|escape:'htmlall':'UTF-8'}</a></h5>
							<div class="product_desc">
								<a href="{$accessoryLink|escape:'htmlall':'UTF-8'}" title="{$accessory.legend|escape:'htmlall':'UTF-8'}" class="product_image"><img src="{$link->getImageLink($accessory.link_rewrite, $accessory.id_image, 'medium')}" alt="{$accessory.legend|escape:'htmlall':'UTF-8'}" width="{$mediumSize.width}" height="{$mediumSize.height}" /></a>
								<a href="{$accessoryLink|escape:'htmlall':'UTF-8'}" title="{l s='More'}" class="product_description">{$accessory.description_short|strip_tags|truncate:70:'...'}</a>
							</div>
							<p class="product_accessories_price">
								{if $accessory.show_price AND !isset($restricted_country_mode) AND !$PS_CATALOG_MODE}<span class="price">{if $priceDisplay != 1}{displayWtPrice p=$accessory.price}{else}{displayWtPrice p=$accessory.price_tax_exc}{/if}</span>{/if}
								<a class="button" href="{$accessoryLink|escape:'htmlall':'UTF-8'}" title="{l s='View'}">{l s='View'}</a>
								{if ($accessory.allow_oosp || $accessory.quantity > 0) AND $accessory.available_for_order AND !isset($restricted_country_mode) AND !$PS_CATALOG_MODE}
									<a class="exclusive button ajax_add_to_cart_button" href="{$link->getPageLink('cart.php')}?qty=1&amp;id_product={$accessory.id_product|intval}&amp;token={$static_token}&amp;add" rel="ajax_id_product_{$accessory.id_product|intval}" title="{l s='Add to cart'}">{l s='Add to cart'}</a>
								{else}
									<span class="exclusive">{l s='Add to cart'}</span>
									<span class="availability">{if (isset($accessory.quantity_all_versions) && $accessory.quantity_all_versions > 0)}{l s='Product available with different options'}{else}{l s='Out of stock'}{/if}</span>
								{/if}
							</p>
						</li>

					{/foreach}
					</ul>
				</div>
			</div>
		</ul>
	{/if}
	{$HOOK_PRODUCT_TAB_CONTENT}
	</div>
</div>
*}
</div> {*end of rightcol*}
{if $logged}
{$HOOK_PRODUCT_FOOTER}
{/if}

{if !$logged}
<div class="collection_container">
  <div class="collection_single_hover go_button">
    <a href="http://www.youtube.com/watch?v=VN5NQbnCI8A&autoplay=1&rel=0{*$link_hiw_slideshow*}" class="fbox-hiw-button iframe collection_link">
      <img src="{$img_ps_dir}home/box_video.jpg" class="collection_link" alt="{l s='HOW IT WORKS'}"/>
      <p>{l s='HOW IT WORKS'}<span></span></p>
    </a>
  </div>

  <div class="collection_single_hover go_button" id="auto_padding">
    <a href="{$link->getPageLink('stylists.php')}" class="collection_link">
      <img src="{$img_ps_dir}home/box_stylists.jpg" class="collection_link" alt="{l s='FEATURED STYLES'}"/>
      <p>{l s='FEATURED STYLES'}<span></span></p>
    </a>
  </div>

  <div class="collection_single_hover">
    <a href="{$link->getPageLink('testimonials.php')}" class="collection_link">
      <img src="{$img_ps_dir}home/box_testimonials.jpg" class="collection_link" alt="{l s='WATCH CLIENT REVIEWS'}"/>
      <p>{l s='WATCH CLIENT REVIEWS'}<span></span></p>
    </a>
  </div>
</div>

{$HOOK_PRODUCT_FOOTER_BLOCK}
{/if}{*end if not logged*}

{/if}{*end if no $errors*}

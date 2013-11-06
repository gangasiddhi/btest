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
    function showSizeSelection(msg) {
        if(!$('.product_choices li').hasClass('picked')) {
            alert(msg);
            return false;
        }
        else
            return true;
    }

	function accessorySizeSelection(msg) {
        if(!$('.product_choices li').hasClass('picked')) {
            alert(msg);
            return false;
        }
        else
            return true;
    }
{/literal}
//]]>
</script>

<div id="primary_block" class="clearfix">

{* displays each product with the attributes*}
<div id="left-col">

    {*include file=$tpl_dir./errors.tpl*}
    <div id="bread-crumbs">
        {if $breadcrumb_path != ''}
            {if $breadcrumb_path == 'butigim'}
                <a href="{$link->getPageLink('showroom.php')}" title="{l s='My Showroom'}">{l s='My Showroom'}</a>
            {elseif $breadcrumb_path == 'koleksiyon'}
                <a href="{$link->getPageLink('lookbook.php')}" title="{l s='LookBooks'}">{l s='LookBooks'}</a>
            {elseif ($breadcrumb_path == 'handbags' || $breadcrumb_path == 'canta')}
                <a href="{$link->getCategoryLink($hand_link_rewrite)}" title="{l s='Handbags'}">{l s='Handbags'}</a>
            {elseif ($breadcrumb_path == 'jewelry' || $breadcrumb_path == 'taki')}
                <a href="{$link->getCategoryLink($jewelry_link_rewrite)}" title="{l s='Jewelry'}">{l s='Jewelry'}</a>
            {elseif $breadcrumb_path == 'lowheels'}
                <a href="{$link->getCategoryLink($low_heels_link_rewrite)}" title="{l s='Low Heels'}">{l s='Low Heels'}</a>
            {elseif $breadcrumb_path == 'accesories'}
                <a href="{$link->getCategoryLink($accessories_link_rewrite)}" title="{l s='Accessories'}">{l s='Accessories'}</a>
            {elseif $breadcrumb_path == 'accessories'}
                <a href="{$link->getPageLink('accessoriesed-products.php')}" title="{l s='Accessoriesed Products'}">{l s='Accessoriesed Products'}</a>
            {elseif $breadcrumb_path == '' || $breadcrumb_path_categories !== ''}
                <a href="{$link->getCategoryLink($shoe_link_rewrite)}" title="{l s='Shoes'}">{l s='Shoes'} </a>
                <span class="bread-crumbs-slash">&nbsp;/</span>&nbsp;
                <a href="{$link->getCategoryLink($shoe_link_rewrite)}" title="{l s='$breadcrumb_path_categories}">{l s= $breadcrumb_path_categories} </a>
            {/if}
            <span class="bread-crumbs-slash">&nbsp;/</span>&nbsp;
        {/if}
        <span>{$product->name|escape:'htmlall':'UTF-8'}</span>
    </div>

    <div class="product_images_container">
        {if isset($images) && count($images) > 0}
        {* thumbnails *}
        <div id="thumbnails_block">
            {if isset($images) && count($images) > 5}
            <a id="btn_scroll_up" title="{l s='Previous'}" href="javascript:{ldelim}{rdelim}">{l s='Previous'}</a>
            {/if}
            <div id="thumbs_list">
                <ul id="thumbs_list_frame">
                {if isset($images)}
                    {foreach from=$images item=image name=thumbnails}
                    {assign var=imageIds value="`$product->id`-`$image.id_image`"}
                    <li id="thumbnail_{$image.id_image}">
                        <a href="{$link->getImageLink($product->link_rewrite, $imageIds, 'large')}" rel="useZoom: 'cloudzoom1', smallImage: '{$link->getImageLink($product->link_rewrite, $imageIds, 'medium')}'" class="{if $jqZoomEnabled}cloud-zoom-gallery{/if}" title="{$image.legend|htmlspecialchars}">
                            <img id="thumb_{$image.id_image}" src="{$link->getImageLink($product->link_rewrite, $imageIds, 'prodthumb')}" alt="{$image.legend|htmlspecialchars}" height="{$thumbSize.height}" width="{$thumbSize.width}" />
                        </a>
                    </li>
                    {/foreach}
                {/if}
                </ul>
            </div>{* end of thumbs_list*}
            {if isset($images) && count($images) > 5}
            <a id="btn_scroll_down" title="{l s='Next'}" href="javascript:{ldelim}{rdelim}">{l s='Next'}</a>
            {/if}
        </div>{*end of views_block*}
        {/if}

        <div id="image-block">
            {if isset($product->out_of_stock) && $product->out_of_stock == 1}
                <div id="back-order"></div>
            {*elseif !(($product->quantity <= 0 && $url_product_attribute==0) ||($product->quantity <= 0 && $url_product_attribute==0) || (isset($color_id_quantity) && $color_id_quantity==0 && $url_product_attribute>0) || ($product->quantity <= 0 && $url_product_attribute>0) || (isset($color_id_quantity) && $color_id_quantity==0 && $url_product_attribute>0))}
                <div id="qty-available" {if $color_shoe_combination == 1}style="display:none"{/if}><div>{l s='SON'}&nbsp;<span></span><br />{l s='ÜRÜN'}</div></div>*}
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

    {if $logged} {*display these only to logged in users*}
    <div id="fb_comments">
        <fb:comments class="fb-comments" href="{$link->getProductLink($product, $url_product_attribute)|urlencode}" num_posts="3" width="585"></fb:comments>
    </div>
    {/if}{*end if not logged*}
</div> {*end of  left-col *}

<div id="rightcol">
    <div id="showroom_item_right_container">
        <div class="product_name_wrapper">
            <h1>{$product->name|escape:'htmlall':'UTF-8'}</h1>
            {*favourite a product*}
                {if isset($HOOK_PRODUCT_ACTIONS) && $HOOK_PRODUCT_ACTIONS}{$HOOK_PRODUCT_ACTIONS}{/if}
            {*favourite a product*}
            {if $logged || $show_site} {*display these only to logged in users*}
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
                        <span class="our_price_display">
                        {if $priceDisplay >= 0 && $priceDisplay <= 2}
                            <span id="our_price_display" class="our_price">{convertPrice price=$productPrice}</span>
                                {if isset($tax_enabled) && $tax_enabled  && ((isset($display_tax_label) && $display_tax_label == 1) OR !isset($display_tax_label))}
                                    {if $priceDisplay == 1}{l s='tax excl.'}{else}{l s='tax incl.'}{/if}
                                {/if}
                        {/if}
                        </span>
                        {if $priceDisplay == 2}
                            <span id="pretaxe_price"><span id="pretaxe_price_display">{convertPrice price=$product->getPrice(false, $smarty.const.NULL, 2)}</span>&nbsp;{l s='tax excl.'}</span>
                        {/if}
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
                {/if}
                <div class="rate_it">{$HOOK_EXTRA_RIGHT}</div>
            {/if}
        </div>

        <div id="user_like">
            <div id="fb_like">
                <fb:like href="{$link->getProductLink($product, $url_product_attribute)}" send="false" layout="button_count" show_faces="false" width="90"></fb:like>
            </div>
            <div id="tt_like">
                <a href="https://twitter.com/share" class="twitter-share-button" data-lang="tr" data-related="Butigocom" data-hashtags="Butigo">Tweet</a>
                <script>{literal}!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");{/literal}</script>
            </div>

            <div id="pin_it">
                <a href="http://pinterest.com/pin/create/button/?url={$link->getProductLink($product, $url_product_attribute)|urlencode}&media={$fb_image_host}{$link->getImageLink($product->link_rewrite, $cover.fb_image, 'prodsmall')}&description={l s='Merhaba, Butigo\'da Ivana Sert benim için bu ayakkabıyı seçti. Sizce bana nasıl gider?'}" class="pin-it-button" count-layout="horizontal"><img src="//assets.pinterest.com/images/PinExt.png" title="Pin It" /></a>
            </div>
        </div>

        <div id="color_size_container">
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
                    <h3>{l s='Select'} {$group.name|escape:'htmlall':'UTF-8'}<h3>
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
                                        <li class="{if $shoesize.quantity>0}stock{else}no_stock{/if}{if $customerShoeSizeDefault == $group_attribute && $shoesize.quantity>0} picked{/if}">
                                            {if $shoesize.quantity > 0}
                                                <a id="choice_{$id_attribute|intval}" class="choice" onclick="updateProductChoiceSelect({$id_attribute|intval}, {$id_attribute_group|intval});" title="{$group_attribute|escape:'htmlall':'UTF-8'}">
                                                    {$group_attribute|escape:'htmlall':'UTF-8'}
                                                </a>
                                            {else}
                                                {$group_attribute|escape:'htmlall':'UTF-8'}
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
        {if isset($colors) && $colors}
            <div id="color_picker">
                <label>{l s='Select color' js=1}</label>
                <ul id="color_to_pick_list">
                {foreach from=$colors key='id_attribute' item='color'}
                    <li {if $color.attributes_quantity>0} class ="chover" {else} class="color_no_stock" {/if}>
                        {*if $color.attributes_quantity>0*}
                            <a href="{$link->getPageLink('product.php')}?id_product={$product->id}&id_product_attribute={$color.id_product_attribute}" id="color_{$id_attribute|intval}" class="color_pick" {if file_exists($col_img_dir|cat:$id_attribute|cat:'.jpg')} style="background: url({$img_col_dir}{$id_attribute}.jpg);" {else} style="background: {$color.value};" {/if} title="{$color.name}">
                            </a>
                        {*else}
                            <span style="float:left;height: 28px;width: 28px;background: {$color.value};" >{if file_exists($col_img_dir|cat:$id_attribute|cat:'.jpg')}<img src="{$img_col_dir}{$id_attribute}.jpg" alt="{$color.name}" width="20" height="20" />{/if}</span>
                        {/if*}
                    </li>
                {/foreach}
                </ul>
            </div>{*end of color_picker*}
        {/if}
{* end - changed code *}
        </div>{* end of color_size_container *}

        {* add to cart form *}
        <div id="order_options">
            <form id="buy_block" {if $PS_CATALOG_MODE AND !isset($groups) AND $product->quantity > 0}class="hidden"{/if} action="{$link->getPageLink('cart.php')}" method="post" {if $color_shoe_combination == 1} onsubmit="return showSizeSelection('{l s='Please select shoe size'}');" {/if} {if $accessory_size_combination == 1} onsubmit="return accessorySizeSelection('{l s='Please select size'}');" {/if}>
                {* hidden datas *}
                <p class="hidden">
                    <input type="hidden" name="token" value="{$static_token}" />
                    <input type="hidden" name="id_product" value="{$product->id|intval}" id="product_page_product_id" />
                    <input type="hidden" name="add" value="1" />
                    <input type="hidden" name="id_product_attribute" id="idCombination" value="" />
                    <input type="hidden" name="color_shoe_combination" value="{$color_shoe_combination}" />
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

                {*if ($product->quantity <= 0 && $url_product_attribute==0 ) || ($color_id_quantity==0 && $url_product_attribute>0)*}
                {if ($product->quantity <= 0 && $url_product_attribute==0) || (isset($color_id_quantity) && $color_id_quantity==0 && $url_product_attribute>0) || ($product->quantity <= 0 && $url_product_attribute>0) || (isset($color_id_quantity) && $color_id_quantity==0 && $url_product_attribute>0)}
                    <div class="out-of-stock"></div>
					<div id="best-and-stock-remainder-container">
						{if ($logged) && (isset($color_shoe_combination) && $color_shoe_combination == 1) && (isset($isAnyofTheProductCombinationIsOutOfStock) && $isAnyofTheProductCombinationIsOutOfStock == 1)}
							<div id="stock-remainder-alarm" data-product-id="{$product->id}">
								<p>{l s='Stock Alarm'}</p>
							</div>
							<div id="stock-remainder-alarm-shoe-sizes"></div>
						{/if}
					</div>
				{elseif $product->quantity > 0 && ($product->showing_status == 0 || $product->active == 0)}
					<div class="do_not_buy_btn">
						<input type="button" name="donot_buy" class="buttonlarge pink" value="{l s='SATIŞTA DEĞİL'}" />
					</div>
                {else}
                    {if $logged || !$logged && $show_site}
                    <div id="check_out_btn">
                        <input type="submit" name="Submit" {if isset($product->out_of_stock) && $product->out_of_stock == 1}class="buttonmedium gray" value="{l s='Back Order'}"{else}class="buttonlarge blue" value="{l s='Add To cart'}"{/if}/>
                    </div>
                    {*if isset($allow_pre_order)}
                    <div id="backorder_info">
                        <p>{l s='Estimated Delivery Time:'}&nbsp;&nbsp;<span style="font-weight: bold">{l s='10 days'}</span></p>
                    </div>
                    {else*}
                    <div id="best-and-stock-remainder-container">
						<div id="bestfit_guarantee">
							<p>{l s='Best Fit Guarantee'}&nbsp;<span class="tooltip"><img src="{$img_dir}product/tooltip.gif" alt="{l s='tooltip'}" /><span>{l s='100% best fit guarantee'}</span></span></p>
						</div>
						{if ($logged) && (isset($color_shoe_combination) && $color_shoe_combination == 1) && (isset($isAnyofTheProductCombinationIsOutOfStock) && $isAnyofTheProductCombinationIsOutOfStock == 1)}
							<div id="stock-remainder-alarm" data-product-id="{$product->id}">
								<p>{l s='Stock Alarm'}</p>
							</div>
							<div id="stock-remainder-alarm-shoe-sizes"></div>
						{/if}
					</div>
                    {*/if*}
                    {/if}
                {/if}
            </form>
        </div>{* end of order options*}
        {if !(($product->quantity <= 0 && $url_product_attribute==0) || (isset($color_id_quantity) && $color_id_quantity==0 && $url_product_attribute>0) || ($product->quantity <= 0 && $url_product_attribute>0) || (isset($color_id_quantity) && $color_id_quantity==0 && $url_product_attribute>0))}
            {if !(isset($product->out_of_stock) && $product->out_of_stock == 1)}
                <div id="time_stamp" >
                    <div id="time_stamp_text">
                        <p><span>{$phrase_text}</span> KARGOYA TESLİM<br/>EDİLEBİLMESİ İÇİN KALAN SÜRE</p>
                    </div>
                    <span id="shipping_time" class="{$shipping_time}"></span>
                </div>
            {/if}
        {/if}

		{*Recommend Products List*}
		{if isset($recommendEnable) && $recommendEnable == 1 && count($recommendProductDetails) > 0}
			<div id="product-recommend-container">
				<div id="product-recommend-heading">
					<p>{l s='Product Recommend For You'}</p>
				</div>
				<div id="product-recommend-thumbnails-block">
					{if isset($recommendProductDetails) && count($recommendProductDetails) > 3}
						<a id="product-recommend-btn-scroll-left" title="{l s='Previous'}" href="javascript:{ldelim}{rdelim}">{l s='Previous'}</a>
					{/if}
					<div id="product-recommend-thumbs-list">
						<ul id="product-recommend-thumbs-list-frame" style="width:{math equation="width * nbImages" width=105 nbImages=$recommendProductDetails|@count}px">
						{if isset($recommendProductDetails)}
							{foreach from=$recommendProductDetails item=recommendProductDetail name=thumbnails}
							{assign var=imageIds value="`$accessorie.id_image`"}
							{if $recommendProductDetail.quantity > 0}
								<li id="thumbnail_{$recommendProductDetail.id_image}" class="accessories-products" productId="{$recommendProductDetail.id_product}" productAttributeId="{$recommendProductDetail.id_product_attribute}">
									<a href="{$recommendProductDetail.link}" title="{$recommendProductDetail.legend|htmlspecialchars}">
										<img id="thumb_{$recommendProductDetail.id_image}" src="{$link->getImageLink($recommendProductDetail.link_rewrite, $recommendProductDetail.id_image, 'prodthumb')}" alt="{$recommendProductDetail.legend|htmlspecialchars}" height="{$thumbSize.height}" width="{$thumbSize.width}" />
										{assign var=addedToCart value=0}
										{foreach from=$cart->getproducts() item=cartProduct name=cartProducts}
											{if $cartProduct.id_product == $recommendProductDetail.id_product}
												{assign var=addedToCart value=1}
											{/if}
										{/foreach}
										<span class="added-to-cart {if $addedToCart != 1} hidden {/if} " value="{$recommendProductDetail.id_product}"><img src="{$img_dir}product/added-to-basket.png" alt="{l s='Added To Cart'}"/></span>
									</a>
									<a href="{$recommendProductDetail.link}" title="{$recommendProductDetail.legend|htmlspecialchars}">
										<span id="product-recommend-name-{$recommendProductDetail.id_product}" class="product-recommend-name">{$recommendProductDetail.name|truncate:22:'.':true|escape:'htmlall':'UTF-8'}</span>
									</a>
										<span id="product-recommend-price-{$recommendProductDetail.id_product}" class="product-recommend-price">{convertPrice price=$recommendProductDetail.price}</span>
										<div id="product-recommend-size-list-{$recommendProductDetail.id_product}" class="product-recommend-size-list" {if $recommendProductDetail.number_of_combinations > 4} style="bottom:83px;"{else} style="bottom:49px;" {/if}></div>
										{if $logged || $show_site}
											<a class="exclusive button {if $recommendProductDetail.number_of_combinations > 1}product_recommend_ajax_add_to_cart_button {else} ajax_add_to_cart_button{/if}" href="#" rel="ajax_id_product_{$recommendProductDetail.id_product|intval}" productId="{$recommendProductDetail.id_product|intval}" productRecommendcombination="{$recommendProductDetail.id_product_attribute|intval}" title="{l s='Add to cart'}">
												<span class="product-recommend-add-to-cart">
													<img src="{$img_dir}/buttons/grey-add-to-cart-button.png" alt="{l s='Add to Cart'}"/>
												</span>
											</a>
										{/if}
								</li>
							{/if}
							{/foreach}
						{/if}
						</ul>
					</div>{* end of thumbs_list*}
					{if isset($recommendProductDetails) && count($recommendProductDetails) > 3}
						<a id="product-recommend-btn-scroll-right" title="{l s='Next'}" href="javascript:{ldelim}{rdelim}">{l s='Next'}</a>
					{/if}
				</div>
			 </div>
		{/if}
		{*Recommend Products List END*}

        <div id="product_info">
            <div id="product_accordion" class="accordion">
                <h6>{l s='BUTIGO SHIPPING GUARANTEE'}<span>&nbsp;</span></h6>
                <div class="first">
                    <div class="accordionShippingInfo">
                        {if !(($product->quantity <= 0 && $url_product_attribute==0) || (isset($color_id_quantity) && $color_id_quantity==0 && $url_product_attribute>0) || ($product->quantity <= 0 && $url_product_attribute>0) || (isset($color_id_quantity) && $color_id_quantity==0 && $url_product_attribute>0))}
                            <span id="accordion-sub-title">Tahmini Teslimat Tarihi:&nbsp;{$delivery_date}</span>
                        {/if}

                        <p>{l s='For any questions regarding this item call us at (216) 418 25 26, or e-mail us at destek@butigo.com'}</p>
                    </div>

                    <a href="http://www.butigo.com/content/7-iadeler-degisimler" target="_blank"
                        class="accordion_links">{l s ='Shipment and Return Policy'}</a>

                    <span id="accordion_links_span">|</span>

                    <a href="http://www.butigo.com/content/8-gizlilik" target="_blank" class="accordion_links">{l s ='Privacy Policy'}</a>
                </div>

                {if isset($features)}
                    <h6>{l s='FEATURES'}<span>&nbsp;</span></h6>
                    <div>
                        <ul>
                            {foreach from=$features item=feature}
                                <li><strong>{$feature.name|escape:'htmlall':'UTF-8'}:</strong> {$feature.value|escape:'htmlall':'UTF-8'}</li>
                            {/foreach}
                        </ul>
                    </div>
                {/if}

                {if $product->description}
                    <h6>{l s='DESCRIPTION'}<span>&nbsp;</span></h6>
                    <div>
                        {$product->description}
                    </div>
                {/if}

                <h6>{l s='PAYMENT OPTIONS'}<span>&nbsp;</span></h6>
                <div class="bottom">
                    <div id="newbankoption">
                        <img src="{$img_dir}product/payment_details_new2.gif" alt="{l s='Bank Options'}"/>
                    </div>
                </div>
                {if $color_shoe_combination == 1}
                <h6>{l s='SHOE SIZE MEASUREMENTS'}<span>&nbsp;</span></h6>
                <div class="bottom1">
                    <div {*id="newbankoption"*}>
                        <img src="{$img_dir}product/new_shoe_size_measurement.jpg" alt="{l s='Shoe Size Measurements'}"/>
                        <p>&#149; Ayak ölçünüzü alabilmek için, uzun parmaktan başlayarak topuk sonuna kadar ölçünüzü almanız gerekmektedir.</p>
                        <p>&#149; Bu ölçüyü alabilmek için, ayağınızı düz bir kağıt üzerine koyarak uzun parmağınız ve topuk ucunu çizgi şeklinde belirleyebilirsiniz.</p>
                        <p>&#149; Her iki çizgi arasını cetvel veya mezurayla ölçerek ayak cm inizi öğrenebilirsiniz.</p>
                        <p>&#149; Her iki ayak numarasının farklı olabileceği düşünülerek, diğer ayağınızın da ölçüsünü alarak, büyük ölçüyü tercih etmenizi öneririz.</p>
                    </div>
                </div>
                {/if}
            </div>
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

{*<div id="more_info_block" class="clear">
    <ul id="more_info_tabs" class="idTabs idTabsShort">
        {if $product->description}<li><a id="more_info_tab_more_info" href="#idTab1">{l s='More info'}</a></li>{/if}
        {if $features}<li><a id="more_info_tab_data_sheet" href="#idTab2">{l s='Data sheet'}</a></li>{/if}
        {if $attachments}<li><a id="more_info_tab_attachments" href="#idTab9">{l s='Download'}</a></li>{/if}
        {if isset($accessories) AND $accessories}<li><a href="#idTab4">{l s='Accessories'}</a></li>{/if}
        {$HOOK_PRODUCT_TAB}
    </ul>
    <div id="more_info_sheets" class="sheets align_justify">
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
{else}
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

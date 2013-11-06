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
*  @version  Release: $Revision: 7616 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<!-- MODULE Home Block best sellers -->

<!--<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.7.2/jquery-ui.min.js"></script>-->
<script type="text/javascript">
{literal}

		$(document).ready(function(){
 setTimeout("trigger_fliping(0)", 3000);
	 setTimeout("trigger_fliping(1)", 4000);
		 setTimeout("trigger_fliping(2)", 5000);
			 setTimeout("trigger_fliping(3)", 6000);
				 setTimeout("trigger_fliping(4)", 7000);

});
	function trigger_fliping(i){
		/*$('.front_side').css({'background-color': '#cccccc'});*/
		var elem = $("#front_side_"+i);
		{
			elem.flip({
				direction:'rl',
				speed: 200,
				color: '#f5f5f5',
				onBefore: function(){
					$('.back_side img').css({'border-bottom': '1px solid #ccc'});
					elem.html(elem.siblings('.back_side').html());
					/* $('.front_side').css({'visibility': 'visible'});*/
				}
			});


			elem.data('flipped',true);
		}
	}


{/literal}
</script>





<div id="best-sellers_block_center" class="block products_block">
	{*<h4>{l s='Top sellers' mod='blockbestsellers'}</h4>*}
	<div id="best_seller_date">
		<div id="seller_date"> {$month} {$today_date}, {$year} </div>
		<div id="best_sellers_img"></div>
	</div>
	{if isset($best_sellers) AND $best_sellers}
		<div class="block_content">
			{assign var='liHeight' value=320}
			{assign var='nbItemsPerLine' value=4}
			{assign var='nbLi' value=$best_sellers|@count}
			{math equation="nbLi/nbItemsPerLine" nbLi=$nbLi nbItemsPerLine=$nbItemsPerLine assign=nbLines}
			{math equation="nbLines*liHeight" nbLines=$nbLines|ceil liHeight=$liHeight assign=ulHeight}
			<ul {*style="height:{$ulHeight}px;"*}>
			{foreach from=$best_sellers item=product name=myLoop}
			<div title="{l s='Click to flip' mod='blockbestsellers'}" class="sponsor">
				<div class="front_side" id="front_side_{$smarty.foreach.myLoop.index}">
					<img alt="More about google" src="{$img_dir}home/bg_flip_panel.png" style="height:157px; width:138px;">
					<span class="big_counter" id="big_counter_{$smarty.foreach.myLoop.index}"></span>
				</div>
				<div class="back_side">
				<li style="border-bottom:0" id="myFlippyBox" class="ajax_block_product {*if $smarty.foreach.homeFeaturedProducts.first}first_item{elseif $smarty.foreach.homeFeaturedProducts.last}last_item{else}item{/if} {if $smarty.foreach.homeFeaturedProducts.iteration%$nbItemsPerLine == 0}last_item_of_line{elseif $smarty.foreach.homeFeaturedProducts.iteration%$nbItemsPerLine == 1}clear{/if} {if $smarty.foreach.homeFeaturedProducts.iteration > ($smarty.foreach.homeFeaturedProducts.total - ($smarty.foreach.homeFeaturedProducts.total % $nbItemsPerLine))}last_line{/if*}">
					<a href="{$product.link}" title="{l s='More' mod='blockbestsellers'}"><span class="small_counter" id="small_counter_{$smarty.foreach.myLoop.total-$smarty.foreach.myLoop.index}" ></span></a>
					{*<div class="product_desc"><a href="{$product.link}" title="{l s='More' mod='blockbestsellers'}">{$product.description_short|strip_tags|truncate:130:'...'}</a></div>*}
					<a href="{$product.link}" title="{$product.name|escape:html:'UTF-8'}" class="product_image"><img src="{$link->getImageLink($product.link_rewrite, $product.id_image, 'home')}"  alt="{$product.name|escape:html:'UTF-8'}" /></a>
					{*<div>
						{if !$PS_CATALOG_MODE}<p class="price_container"><span class="price">{$product.price}</span></p>{else}<div style="height:21px;"></div>{/if}
						<a class="button" href="{$product.link}" title="{l s='View' mod='blockbestsellers'}">{l s='View' mod='blockbestsellers'}</a>
					</div>*}
					<h5 class="name"><a href="{$product.link}" title="{$product.name|truncate:32:'...'|escape:'htmlall':'UTF-8'}">{$product.name|truncate:27:'...'|escape:'htmlall':'UTF-8'}</a></h5>
				</li>
				</div>
			</div>
			{/foreach}
			</ul>
			{*<p class="clearfix" style="padding: 5px;"><a style="float:right;" href="{$link->getPageLink('best-sales.php')}" title="{l s='All best sellers' mod='blockbestsellers'}" class="button_large">{l s='All best sellers' mod='blockbestsellers'}</a></p>*}
		</div>
	{else}
		<p>{l s='No best sellers at this time' mod='blockbestsellers'}</p>
	{/if}
	<br class="clear"/>
</div>

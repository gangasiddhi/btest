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
* Do not edit or add to this file if you fav to upgrade PrestaShop to newer
* versions in the future. If you fav to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2011 PrestaShop SA
*  @version  Release: $Revision: 7046 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<script type="text/javascript">
{literal}
// <![CDATA[

	/* $(document).ready(function() {
		$('.delete_fav_product').hide();
	});*/

	function delOver(ipa)
	{
			$('#del_'+ipa).removeClass('hidden');
	}
		
	function delOut(ipa)
	{
			$('#del_'+ipa).addClass('hidden');
	}
//]]>
{/literal}
</script>
<div id="customer_favourite_shoes">
	{if $products}
		{foreach from=$products item=product name=i}
			<div class="showroom_sel_cntnr" id="fav_delete_{$product.id_product}_{$product.id_product_attribute}">
				<div class="showfb_{$product.id_product}" onmouseover="delOver('{$product.id_product_attribute}');" onmouseout="delOut('{$product.id_product_attribute}')">
				<div class="showroom_sel_shoe">
					<span class="favlist_product_detail hidden">
						<br />{l s='Quantity' mod='blockfavourites'}:<input type="text" id="quantity_{$product.id_product}_{$product.id_product_attribute}" value="{$product.quantity|intval}" size="3"  />
						<br /><br />{l s='Priority' mod='blockfavourites'}: <select id="priority_{$product.id_product}_{$product.id_product_attribute}">
							<option value="0"{if $product.priority eq 0} selected="selected"{/if}>{l s='High' mod='blockfavourites'}</option>
							<option value="1"{if $product.priority eq 1} selected="selected"{/if}>{l s='Medium' mod='blockfavourites'}</option>
							<option value="2"{if $product.priority eq 2} selected="selected"{/if}>{l s='Low' mod='blockfavourites'}</option>
						</select>
					</span>
					<a href="javascript:;" id="del_{$product.id_product_attribute}" class="delete_fav_product hidden	" onclick="FavlistProductManage('fav_delete', 'delete', '{$id_favlist}', '{$product.id_product}', '{$product.id_product_attribute}', $('#quantity_{$product.id_product}_{$product.id_product_attribute}').val(), $('#priority_{$product.id_product}_{$product.id_product_attribute}').val());" title="{l s='Delete' mod='blockfavourites'}">
						<span>{l s='Delete' mod='blockfavourites'}</span>
					</a>
					{*if ($product.quantity > 0 && $product.quantity < $last_qties )}
						 <div class="low_stock_container">
							<span>{l s='low stock'}</span>
						</div>
					{/if}
					{if $product.quantity == 0}
						<div class="sold_out_container">
							<span>{l s='sold out'}</span>
						</div>
					{/if*}
					{*<a href="{$product.link}" title="{$product.name|escape:html:'UTF-8'}" class="product_image">
						<img src="{$img_ps_dir}spacer.gif" data-src="{$link->getImageLink($product.link_rewrite, $product.id_image, 'prodsmall')}" height="{$prodsmallSize.height}" width="{$prodsmallSize.width}" alt="{$product.name|escape:html:'UTF-8'}" />
					</a>
					<a href="{$product.link}" title="{$product.name|escape:html:'UTF-8'}" class="product_image">
						<img class="prod_img1{$product.id_image}" src="{$img_ps_dir}spacer.gif" data-src="{$link->getImageLink($product.link_rewrite, $product.id_image, 'prodsmall')}" height="{$prodsmallSize.height}" width="{$prodsmallSize.width}" alt="{$product.name|escape:html:'UTF-8'}" />
					</a>*}
					{if $product.product_combination > 0}
						<a href="{$link->getProductlink($product.id_product, $product.id_product_attribute, $product.link_rewrite, $product.category_rewrite)}" title="{l s='Product detail' mod='blockfavourites}">
							<img src="{$link->getImageLink($product.link_rewrite, $product.cover, 'prodsmall')}" alt="{$product.name|escape:'htmlall':'UTF-8'}" />
						</a>
					{else}
						<a href="{$link->getProductlink($product.id_product, $product.link_rewrite, $product.category_rewrite)}" title="{l s='Product detail' mod='blockfavourites}">
							<img src="{$link->getImageLink($product.link_rewrite, $product.cover, 'prodsmall')}" alt="{$product.name|escape:'htmlall':'UTF-8'}" />
						</a>
					{/if}
				</div>{* end of showroom_sel_shoe*}
				<div class="name_color_container">
					<div class="showroom_shoe_name" id="ajax_response_{$product.id_product}_{$ipa}">
						<a href="{$link->getProductlink($product.id_product, $product.link_rewrite, $product.category_rewrite)}" class="showroom_shoe_name" title="{$product.name|escape:html:'UTF-8'}">{$product.name|truncate:27:'...'|escape:'htmlall':'UTF-8'}</a>
					</div>
					<div class = "showroom_product_price">
						{displayPrice price = $product.price}
					</div>

					{*if isset($product.product_colors) }
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
					{/if*}
					{*if $product.collection_name}<span class="prod_col_name">{$product.collection_name}</span>{/if*}
				</div>{* name_color_container*}
				</div> {*end of showfb*}
			</div>{* end of showroom_sel_cntnr*}
		{/foreach}
</div>

<img src="{$img_dir}fav_empty.jpg" height="545" width="960" id="fav_empty" class="hidden"/>

{*if !$refresh}
	<br />
	<a href="#" id="hideBoughtProducts" class="button_account"  onclick="FavlistVisibility('wlp_bought', 'BoughtProducts'); return false;">{l s='Hide products' mod='blockfavourites'}</a>
	<a href="#" id="showBoughtProducts" class="button_account"  onclick="FavlistVisibility('wlp_bought', 'BoughtProducts'); return false;">{l s='Show products' mod='blockfavourites'}</a>
	{if count($productsBoughts)}
	<a href="#" id="hideBoughtProductsInfos" class="button_account" onclick="FavlistVisibility('wlp_bought_infos', 'BoughtProductsInfos'); return false;">{l s='Hide bought product\'s info' mod='blockfavourites'}</a>
	<a href="#" id="showBoughtProductsInfos" class="button_account"  onclick="FavlistVisibility('wlp_bought_infos', 'BoughtProductsInfos'); return false;">{l s='Show bought product\'s info' mod='blockfavourites'}</a>
	{/if}
	<a href="#" id="showSendFavlist" class="button_account" onclick="FavlistVisibility('wl_send', 'SendFavlist'); return false;">{l s='Send this favlist' mod='blockfavourites'}</a>
	<a href="#" id="hideSendFavlist" class="button_account" onclick="FavlistVisibility('wl_send', 'SendFavlist'); return false;">{l s='Close send this favlist' mod='blockfavourites'}</a>
	<span class="clear"></span>
	<br />
	Permalink :<br/><input type="text" value="{$base_dir_ssl}modules/blockfavourites/view.php?token={$token_fav|escape:'htmlall':'UTF-8'}" style="width:540px;" readonly/>
{/if*}
	{*<div class="wlp_bought">
	foreach from=$products item=product name=i}
	<ul class="address {if $smarty.foreach.i.index % 2}alternate_{/if}item" style="margin:5px 0 0 5px;border-bottom:1px solid #ccc;" id="wlp_{$product.id_product}_{$product.id_product_attribute}">
		<li class="address_name">
			<a href="{$link->getProductlink($product.id_product, $product.link_rewrite, $product.category_rewrite)}" title="{l s='Product detail' mod='blockfavourites'}">
				<img src="{$link->getImageLink($product.link_rewrite, $product.cover, 'prodsmall')}" alt="{$product.name|escape:'htmlall':'UTF-8'}" />
			</a>
			<li class="address_title">{$product.name|truncate:30:'...'|escape:'htmlall':'UTF-8'}</li>
			if isset($product.attributes_small)}
				<a href="{$link->getProductlink($product.id_product, $product.link_rewrite, $product.category_rewrite)}" title="{l s='Product detail' mod='blockfavourites'}">{$product.attributes_small|escape:'htmlall':'UTF-8'}</a>
			{/if}
			<span class="favlist_product_detail hidden">
				<br />{l s='Quantity' mod='blockfavourites'}:<input type="text" id="quantity_{$product.id_product}_{$product.id_product_attribute}" value="{$product.quantity|intval}" size="3"  />
				<br /><br />{l s='Priority' mod='blockfavourites'}: <select id="priority_{$product.id_product}_{$product.id_product_attribute}">
					<option value="0"{if $product.priority eq 0} selected="selected"{/if}>{l s='High' mod='blockfavourites'}</option>
					<option value="1"{if $product.priority eq 1} selected="selected"{/if}>{l s='Medium' mod='blockfavourites'}</option>
					<option value="2"{if $product.priority eq 2} selected="selected"{/if}>{l s='Low' mod='blockfavourites'}</option>
				</select>
			</span>
			<a href="javascript:;" class="clear button" onclick="FavlistProductManage('wlp_bought', 'delete', '{$id_favlist}', '{$product.id_product}', '{$product.id_product_attribute}', $('#quantity_{$product.id_product}_{$product.id_product_attribute}').val(), $('#priority_{$product.id_product}_{$product.id_product_attribute}').val());" title="{l s='Delete' mod='blockfavourites'}">{l s='Delete' mod='blockfavourites'}</a>
			<a href="javascript:;" class="exclusive" onclick="FavlistProductManage('wlp_bought_{$product.id_product_attribute}', 'update', '{$id_favlist}', '{$product.id_product}', '{$product.id_product_attribute}', $('#quantity_{$product.id_product}_{$product.id_product_attribute}').val(), $('#priority_{$product.id_product}_{$product.id_product_attribute}').val());" title="{l s='Save' mod='blockfavourites'}">{l s='Save' mod='blockfavourites'}</a>
		</li>
	</ul>
	{/foreach}
	</div>
	<div class="clear"></div>
	<br />*}
	{*if !$refresh}
	<form method="post" class="wl_send std hidden" onsubmit="return (false);">
		<fieldset>
			<p class="required">
				<label for="email1">{l s='Email' mod='blockfavourites'}1</label>
				<input type="text" name="email1" id="email1" />
				<sup></sup>
			</p>
			{section name=i loop=11 start=2}
			<p>
				<label for="email{$smarty.section.i.index}">{l s='Email' mod='blockfavourites'}{$smarty.section.i.index}</label>
				<input type="text" name="email{$smarty.section.i.index}" id="email{$smarty.section.i.index}" />
			</p>
			{/section}
			<p class="submit">
				<input class="button" type="submit" value="{l s='Send' mod='blockfavourites'}" name="submitFavlist" onclick="FavlistSend('wl_send', '{$id_favlist}', 'email');" />
			</p>
			<p class="required">
				<sup></sup>
				{l s='Required field'}
			</p>
		</fieldset>
	</form>
	{if count($productsBoughts)}
	<table class="wlp_bought_infos hidden std">
		<thead>
			<tr>
				<th class="first_item">{l s='Product' mod='blockfavourites'}</td>
				<th class="item">{l s='Quantity' mod='blockfavourites'}</td>
				<th class="item">{l s='Offered by' mod='blockfavourites'}</td>
				<th class="last_item">{l s='Date' mod='blockfavourites'}</td>
			</tr>
		</thead>
		<tbody>
		{foreach from=$productsBoughts item=product name=i}
			{foreach from=$product.bought item=bought name=j}
			{if $bought.quantity > 0}
				<tr>
					<td class="first_item">
					<span style="float:left;"><img src="{$link->getImageLink($product.link_rewrite, $product.cover, 'small')}" alt="{$product.name|escape:'htmlall':'UTF-8'}" /></span>			
					<span style="float:left;">{$product.name|truncate:40:'...'|escape:'htmlall':'UTF-8'}
					{if isset($product.attributes_small)}
						<br /><i>{$product.attributes_small|escape:'htmlall':'UTF-8'}</i>
					{/if}</span>
					</td>
					<td class="item align_center">{$bought.quantity|intval}</td>
					<td class="item align_center">{$bought.firstname} {$bought.lastname}</td>
					<td class="last_item align_center">{$bought.date_add|date_format:"%Y-%m-%d"}</td>
				</tr>
			{/if}
			{/foreach}
		{/foreach}
		</tbody>
	</table>
	{/if}
	{/if*}
{else}
	<img src="{$img_dir}fav_empty.jpg" height="545" width="960" id="fav_empty"/>
{/if}

{*<h1 id="cart_title">{l s='Shopping cart summary'}</h1>*}

{assign var='current_step' value='summary'}
{*include file="$tpl_dir./errors.tpl"*}

{if isset($empty)}
<div id="cart_empty">
 	<img src="{$img_dir}cart/empty-cart-message.gif" alt="{l s='Delete'}" width="380" height="34" />
	<a href="{*if (isset($smarty.server.HTTP_REFERER) && strstr($smarty.server.HTTP_REFERER, $link->getPageLink('order.php'))) || !isset($smarty.server.HTTP_REFERER)*}{$link->getPageLink('showroom.php')}{*else}{$smarty.server.HTTP_REFERER|escape:'htmlall':'UTF-8'|secureReferrer}{/if*}" class="buttonmedium blue" title="{l s='Continue shopping'}">{l s='Continue shopping'}</a>
</div>
{elseif $PS_CATALOG_MODE}
<p class="warning">{l s='This store has not accepted your new order.'}</p>
{else}
<script type="text/javascript">
	/* <![CDATA[ */
	var baseDir = '{$base_dir_ssl}';
	var currencySign = '{$currencySign|html_entity_decode:2:"UTF-8"}';
	var currencyRate = '{$currencyRate|floatval}';
	var currencyFormat = '{$currencyFormat|intval}';
	var currencyBlank = '{$currencyBlank|intval}';
	var txtProduct = "{l s='product'}";
	var txtProducts = "{l s='products'}";
    var accessoryErrorMessage = "{l s='You will get Discounts, Please add the accessories : '}";
	/* ]]> */
</script>

{* NanoInteractive Integration *}
<script type="text/javascript">

	{*{foreach from=$products item=product name=productLoop}
		productIds.push({$product.id_product});
	{/foreach}*}

	{literal}
		(function(d){
			var HEIAS_PARAMS = [];
			HEIAS_PARAMS.push(['type', 'ppx'], ['ssl', 'auto'], ['n', '6451'], ['cus', '17201']);
			HEIAS_PARAMS.push(['pb', '1']);

			HEIAS_PARAMS.push(['order_article',{/literal}'{foreach name=productLoop item=product from=$products}{$product.id_product}{if ! $smarty.foreach.productLoop.last},{/if}{/foreach}'{literal}]);

			if (typeof window.HEIAS === 'undefined') { window.HEIAS = []; }
			window.HEIAS.push(HEIAS_PARAMS);

			var scr = d.createElement('script');
			scr.async = true;
			scr.src = (d.location.protocol === 'https:' ? 'https:' : 'http:') + '//ads.heias.com/x/heias.async/p.min.js';
			var elem = d.getElementsByTagName('script')[0];
			elem.parentNode.insertBefore(scr, elem);
		}(document));
	{/literal}
</script>

{if $bu_env=='production'}
<div class="hidden">
	{* Google Remarketing Code for Added Product to Cart *}
	<script type="text/javascript">
	{literal}
		/* <![CDATA[ */
		var google_conversion_id = 1009336416;
		var google_conversion_language = "en";
		var google_conversion_format = "3";
		var google_conversion_color = "ffffff";
		var google_conversion_label = "xWrVCPjk1QIQ4ICl4QM";
		var google_conversion_value = 0;
		/* ]]> */
	{/literal}
	</script>
	<script type="text/javascript" src="https://www.googleadservices.com/pagead/conversion.js"></script>
	<noscript>
		<div style="display:inline;">
			<img height="1" width="1" style="border-style:none;" alt="" src="https://www.googleadservices.com/pagead/conversion/1009336416/?label=xWrVCPjk1QIQ4ICl4QM&amp;guid=ON&amp;script=0"/>
		</div>
	</noscript>
</div>
{/if}

<div id="cart_empty" style="display:none">
	<img src="{$img_dir}cart/empty-cart-message.gif" alt="{l s='Delete'}" class="icon" width="380" height="34" />
	<a href="{if (isset($smarty.server.HTTP_REFERER) && strstr($smarty.server.HTTP_REFERER, $link->getPageLink('order.php'))) || !isset($smarty.server.HTTP_REFERER)}{$link->getPageLink('showroom.php')}{else}{$smarty.server.HTTP_REFERER|escape:'htmlall':'UTF-8'|secureReferrer}{/if}" class="buttonmedium blue" title="{l s='Continue shopping'}">{l s='Continue shopping'}</a>
	{*<a href="{$link->getPageLink('showroom.php')}" class="btn_cont_shopping" title="{l s='Continue shopping'}"></a>*}
</div>

<div class = "shopping-cart" id="center_column">
	<div id="order-detail-content" class="table_block">
		<table id="cart_summary" border="0">
			<thead>
				<tr>
					<th class="cart_delete first_item">{l s='Delete'}</th>
					<th class="cart_product first_item">{l s='Product'}</th>
					{*<th class="cart_description item">{l s='Description'}</th>
					<th class="cart_ref item">{l s='Ref.'}</th>
					<th class="cart_availability item">{l s='Avail.'}</th>*}
					<th class="cart_quantity item">{l s='Qty'}</th>
					<th class="cart_unit item">{l s='Unit price'}</th>
					<th class="cart_total last_item">{l s='Total'}</th>
				</tr>
			</thead>
			<tfoot>
				<tr class="cart_total_voucher">
					<td colspan="6" id="cartVoucherTotal">
						{if isset($errors_discount) && $errors_discount}
							<ul class="error">
							{foreach from=$errors_discount key=k item=error}
								<li>{$error|escape:'htmlall':'UTF-8'}</li>
							{/foreach}
							</ul>
						{/if}
						<form action="{if $opc}{$link->getPageLink('order-opc.php', true)}{else}{$link->getPageLink('order.php', true)}{/if}" method="post">
							<div id = "voucher">
								<div  id="cart_voucher">
									<span>{l s='İNDİRİM KODU'}</span>
									<div id = "voucher_code">
										<label for="discount_name">{l s='İndirim kodun varsa buraya girebilirsin'}</label>
										<input type="text" value="" name="discount_name" id="discount_name">
									</div>
								</div>
								<p class = "zero_mar" id="submitVoucher">
									<input type="hidden" name="submitDiscount">
									<input type="submit" class="buttonsmall blue" value="{l s='Send'}" name="submitAddDiscount">
								</p>
							</div>
						</form>
						<div id="cart_total">
							{if $use_taxes}
							{*<tr class="cart_total_price">
								<td colspan="6">{l s='Total (tax excl.):'}</td>
								<td class="price">{displayPrice price=$total_price_without_tax}</td>
							</tr>
							<tr class="cart_total_voucher">
								<td colspan="6">{l s='Total tax:'}</td>
								<td class="price">{displayPrice price=$total_tax}</td>
							</tr>*}
								<div  class="cart_total_label">
									{if $display_tax_label}
										{l s='TOTAL'}&nbsp;<span>{l s='TAX INCL'}</span>
									{else}
										{l s='Total:'}
									{/if}
								</div>
								<div class="cart_total_price" id="total_price"><span>{displayPrice price=$total_price}</span></div>
							{else}
								<div class="cart_total_label" id="total_price">{l s='Total:'}</div>
								<div class="cart_total_price">{displayPrice price=$total_price_without_tax}</div>
							{/if}

						</div>
					</td>
				</tr>
			</tfoot>
			<tbody>
			{foreach from=$products item=product name=productLoop}
				{assign var='productId' value=$product.id_product}
				{assign var='productAttributeId' value=$product.id_product_attribute}
				{assign var='quantityDisplayed' value=0}
				{* Display the product line *}
				{include file="$tpl_dir./shopping-cart-product-line.tpl"}
				{* Then the customized datas ones*}
				{if isset($customizedDatas.$productId.$productAttributeId)}
					{foreach from=$customizedDatas.$productId.$productAttributeId key='id_customization' item='customization'}
						<tr id="product_{$product.id_product}_{$product.id_product_attribute}_{$id_customization}" class="alternate_item cart_item">
							<td colspan="5">
								{foreach from=$customization.datas key='type' item='datas'}
									{if $type == $CUSTOMIZE_FILE}
										<div class="customizationUploaded">
											<ul class="customizationUploaded">
												{foreach from=$datas item='picture'}<li><img src="{$pic_dir}{$picture.value}_small" alt="" class="customizationUploaded" /></li>{/foreach}
											</ul>
										</div>
									{elseif $type == $CUSTOMIZE_TEXTFIELD}
										<ul class="typedText">
											{foreach from=$datas item='textField' name='typedText'}<li>{if $textField.name}{$textField.name}{else}{l s='Text #'}{$smarty.foreach.typedText.index+1}{/if}{l s=':'} {$textField.value}</li>{/foreach}
										</ul>
									{/if}
								{/foreach}
							</td>
							<td class="cart_quantity">
								<div style="float:right">
									<a rel="nofollow" class="cart_quantity_delete" id="{$product.id_product}_{$product.id_product_attribute}_{$id_customization}" href="{$link->getPageLink('cart.php', true)}?delete&amp;id_product={$product.id_product|intval}&amp;ipa={$product.id_product_attribute|intval}&amp;id_customization={$id_customization}&amp;token={$token_cart}"><img src="{$img_dir}icon/delete.gif" alt="{l s='Delete'}" title="{l s='Delete this customization'}" width="11" height="13" class="icon" /></a>
								</div>
								<div id="cart_quantity_button" style="float:left">
								<a rel="nofollow" class="cart_quantity_up" id="cart_quantity_up_{$product.id_product}_{$product.id_product_attribute}_{$id_customization}" href="{$link->getPageLink('cart.php', true)}?add&amp;id_product={$product.id_product|intval}&amp;ipa={$product.id_product_attribute|intval}&amp;id_customization={$id_customization}&amp;token={$token_cart}" title="{l s='Add'}"><img src="{$img_dir}icon/quantity_up.gif" alt="{l s='Add'}" width="14" height="9" /></a><br />
								{if $product.minimal_quantity < ($customization.quantity -$quantityDisplayed) OR $product.minimal_quantity <= 1}
								<a rel="nofollow" class="cart_quantity_down" id="cart_quantity_down_{$product.id_product}_{$product.id_product_attribute}_{$id_customization}" href="{$link->getPageLink('cart.php', true)}?add&amp;id_product={$product.id_product|intval}&amp;ipa={$product.id_product_attribute|intval}&amp;id_customization={$id_customization}&amp;op=down&amp;token={$token_cart}" title="{l s='Subtract'}">
									<img src="{$img_dir}icon/quantity_down.gif" alt="{l s='Subtract'}" width="14" height="9" />
								</a>
								{else}
								<a class="cart_quantity_down" style="opacity: 0.3;" id="cart_quantity_down_{$product.id_product}_{$product.id_product_attribute}_{$id_customization}" href="#" title="{l s='Subtract'}">
									<img src="{$img_dir}icon/quantity_down.gif" alt="{l s='Subtract'}" width="14" height="9" />
								</a>
								{/if}
								</div>
								<input type="hidden" value="{$customization.quantity}" name="quantity_{$product.id_product}_{$product.id_product_attribute}_{$id_customization}_hidden"/>
								<input size="2" type="text" value="{$customization.quantity}" class="cart_quantity_input" name="quantity_{$product.id_product}_{$product.id_product_attribute}_{$id_customization}"/>
							</td>
							<td class="cart_total"></td>
						</tr>
						{assign var='quantityDisplayed' value=$quantityDisplayed+$customization.quantity}
					{/foreach}
					{* If it exists also some uncustomized products *}
					{if $product.quantity-$quantityDisplayed > 0}{include file="$tpl_dir./shopping-cart-product-line.tpl"}{/if}
				{/if}
			{/foreach}
			{if $use_taxes}
				{if $priceDisplay}
					<tr class="cart_total_delivery" {if $shippingCost <= 0} style="display:none;"{/if}>
						<td colspan="4">{l s='Total shipping'}{if $display_tax_label} {l s='(tax excl.)'}{/if}{l s=':'}</td>
						<td class="price" id="total_shipping">{displayPrice price=$shippingCostTaxExc}</td>
					</tr>
				{else}
					<tr class="cart_total_delivery"{if $shippingCost <= 0} style="display:none;"{/if}>
						<td colspan="4">{l s='Total shipping'}{if $display_tax_label} {l s='(tax incl.)'}{/if}{l s=':'}</td>
						<td class="price" id="total_shipping" >{displayPrice price=$shippingCost}</td>
					</tr>
				{/if}
			{else}
				<tr class="cart_total_delivery"{if $shippingCost <= 0} style="display:none;"{/if}>
					<td colspan="4">{l s='Total shipping:'}</td>
					<td class="price" id="total_shipping" >{displayPrice price=$shippingCostTaxExc}</td>
				</tr>
			{/if}

			</tbody>
		{if sizeof($discounts)}
			<tbody>
				{foreach from=$discounts item=discount name=discountLoop}
						{if $show_first_cart_discount && $discount.id_discount_type == 5 && $discount.name == $first_cart_discount_name}
							<tr style="height:32px;" class="cart_1_discount">
								{*<td class="cart_discount_delete">
									<a href="{$base_dir_ssl}order.php?deleteDiscount={$discount.id_discount}" title="{l s='Delete'}"><img src="{$img_dir}cart/delete.gif" alt="{l s='Delete'}"/></a>
								</td>*}
								<td colspan = "6">
									<div>
										<span class="cart_1_desc">{l s='Sürpriz! Merhaba hediyesi olarak önümüzdeki yapacağın ilk alışveriş sana'}
											{if $discount.value_real > 0}
												<strong style="color:inherit">{if !$priceDisplay}{displayPrice price=$discount.value_real}{else}{displayPrice price=$discount.value_tax_exc}{/if}</strong>
											{/if}
											{l s='indirimli. Güle güle kullan.'}</span>
										{*<span id="cart_discount_clock" class ="{$duration}">{$valid_duration}</span>*}
										<span class="cart_1_discount_value">{if $discount.value_real > 0}
											{if !$priceDisplay}{displayPrice price=$discount.value_real*-1}{else}{displayPrice price=$discount.value_tax_exc*-1}{/if}
										{/if}</span>
									</div>
								</td>
							</tr>
						{/if}
						{if $discount.id_discount_type != 5 && $discount.quantity != 0}
							<tr style="height:32px;" class="cart_discount {if $smarty.foreach.discountLoop.last}last_item{elseif $smarty.foreach.discountLoop.first}first_item{else}item{/if}" id="cart_discount_{$discount.id_discount}">
								{*<td class="cart_discount_name" colspan="2">{$discount.name}</td>*}
								<td class="cart_discount_delete"><a href="{if $opc}{$link->getPageLink('order-opc.php', true)}{else}{$link->getPageLink('order.php', true)}{/if}?deleteDiscount={$discount.id_discount}" title="{l s='Delete'}"><img src="{$img_dir}cart/delete.gif" alt="{l s='Delete'}"/></a></td>
								<td colspan="5">
									<span class="cart_discount_description">{$discount.description}</span>
									<span {if $discount.id_discount_type == 4}id="credit_discount"{/if} {if $discount.id_discount_type == 6}id="buy1_get1_discount"{/if} class="price-discount">
										{if  $discount.id_discount_type != 6}
											{if $discount.value_real > 0 }
												{if !$priceDisplay}{displayPrice price=$discount.value_real*-1}{else}{displayPrice price=$discount.value_tax_exc*-1}{/if}
											{/if}
										{else}
											{if !$priceDisplay}{displayPrice price=$discount.value_real*-1}{else}{displayPrice price=$discount.value_tax_exc*-1}{/if}
										{/if}
									</span>
								</td>
							</tr>
						{/if}
					{/foreach}
			</tbody>
		{/if}
			{*<tbody>
				<tr style="height:25px;" id="butigo_info">
					<td colspan="6">
						<span class="cart_1_desc">
							{l s='26 Ağustos-4 Eylül arası vereceğiniz siparişler, bayram dolayısıyla 5 Eylül Pazartesi günü kargolanacaktır.'}
						</span>
					</td>
				</tr>
			</tbody>*}
		</table>
	</div>

	{*{if $voucherAllowed}
	<div id="cart_voucher" class="table_block">
		{if isset($errors_discount) && $errors_discount}
			<ul class="error">
			{foreach from=$errors_discount key=k item=error}
				<li>{$error|escape:'htmlall':'UTF-8'}</li>
			{/foreach}
			</ul>
		{/if}
		<form action="{if $opc}{$link->getPageLink('order-opc.php', true)}{else}{$link->getPageLink('order.php', true)}{/if}" method="post" id="voucher">
			<fieldset>
				<h4>{l s='Vouchers'}</h4>
				<p>
					<label for="discount_name">{l s='Code:'}</label>
					<input type="text" id="discount_name" name="discount_name" value="{if isset($discount_name) && $discount_name}{$discount_name}{/if}" />
				</p>
				<p class="submit"><input type="hidden" name="submitDiscount" /><input type="submit" name="submitAddDiscount" value="{l s='Add'}" class="button" /></p>
			{if $displayVouchers}
				<h4>{l s='Take advantage of our offers:'}</h4>
				<div id="display_cart_vouchers">
				{foreach from=$displayVouchers item=voucher}
					<span onclick="$('#discount_name').val('{$voucher.name}');return false;" class="voucher_name">{$voucher.name}</span> - {$voucher.description} <br />
				{/foreach}
				</div>
			{/if}
			</fieldset>
		</form>
	</div>
	{/if}*}
	{*<div id="HOOK_SHOPPING_CART">{$HOOK_SHOPPING_CART}</div>*}

        {*Accessories Details*}
        {if $Productaccessories}
        <div class="accessories-details-container">
            {assign var="numberOfAccessoryImages" value=$Productaccessories|@count}

            <div class="accessory-heading">{l s='Product Accessories'}</div>

            {if $Productaccessories|@count > 8}
            {*Slide Show*}
                <div id="slider1">
                    <a class="buttons prev" href="#">left</a>
                    <div class="viewport" style="width:{math equation="(width * nbImages)" width=119 nbImages=$numberOfAccessoryImages-$numberOfAccessoryImages%8-1}px">
                            <ul class="overview">
                                {foreach from=$Productaccessories item=Productaccessorie name=accessoryProductLoop}
                                    <li>
                                        <img src="{$link->getImageLink($Productaccessorie.link_rewrite, $Productaccessorie.id_image, 'small')}" alt="{$Productaccessorie.name|escape:'htmlall':'UTF-8'}" />
                                        <span>
                                            <label class="accessory-name">{$Productaccessorie.name|truncate:18:""}</label>
                                            <label class="accessory-price">{displayPrice price=$Productaccessorie.price}</label>
                                            {if $Productaccessorie.available_for_order && $Productaccessorie.quantity > 0}
                                            <a rel="nofollow" href="{$link->getPageLink('cart.php', true)}?add&amp;id_product={$Productaccessorie.id_product|intval}&amp;ipa={$Productaccessorie.id_product_attribute|intval}&amp;token={$token_cart}" title="{l s='Add to Cart'}">
                                                <img src="{$img_dir}cart/accessory-plus.png" alt="{l s='Add to Cart'}" style="width:14px;height:14px"/>
                                            </a>
                                            {/if}
                                        </span>
                                    </li>
                                {/foreach}
                            </ul>
                    </div>
                    <a class="buttons next" href="#">right</a>
                </div>
            {else}
            <div class="accessories-product-list">
                <div class="accessories-products" style="margin: auto {math equation="(960 -(width * nbImages))/2" width=119 nbImages=$numberOfAccessoryImages}px">
                    {foreach from=$Productaccessories item=Productaccessorie name=accessoryProductLoop}
                        {if $smarty.foreach.accessoryProductLoop.index <= 7}
                            {if $Productaccessorie.id_color_default == 2}
                                    {assign var=ipa value= $Productaccessorie.default_ipa_only}
                            {else}
                                    {assign var=ipa value=0}
                            {/if}
                            <div class="accessories-product">
                                {*<a href="{$link->getProductLink($Productaccessorie.id_product, $ipa, $Productaccessorie.link_rewrite, $Productaccessorie.category)|escape:'htmlall':'UTF-8'}">*}
                                    <img src="{$link->getImageLink($Productaccessorie.link_rewrite, $Productaccessorie.id_image, 'small')}" alt="{$Productaccessorie.name|escape:'htmlall':'UTF-8'}" />
                                {*</a>*}
                                <span>
                                    <label class="accessory-name">{$Productaccessorie.name|truncate:18:""}</label>
                                    <label class="accessory-price   q">{displayPrice price=$Productaccessorie.price}</label>
                                    {if $Productaccessorie.available_for_order && $Productaccessorie.quantity > 0}
                                    <a rel="nofollow" href="{$link->getPageLink('cart.php', true)}?add&amp;id_product={$Productaccessorie.id_product|intval}&amp;ipa={$Productaccessorie.id_product_attribute|intval}&amp;token={$token_cart}" title="{l s='Add to Cart'}">
                                        <img src="{$img_dir}cart/accessory-plus.png" alt="{l s='Add to Cart'}"/>
                                    </a>
                                    {/if}
                                </span>
                            </div>
                        {/if}
                    {/foreach}
                </div>
           </div>
          {/if}
        </div>
        {/if}

	<form action="{$link->getPageLink('order.php',true)}?step=2" method="post">
		<div class="cart_navigation">
			{if !$show_site}
				<a href="{*if (isset($smarty.server.HTTP_REFERER) && strstr($smarty.server.HTTP_REFERER, $link->getPageLink('order.php'))) || !isset($smarty.server.HTTP_REFERER)*}{$link->getPageLink('showroom.php')}{*else}{$smarty.server.HTTP_REFERER|escape:'htmlall':'UTF-8'|secureReferrer}{/if*}" class="buttonmedium pink" style="float: left"; id="backaddress" title="{l s='Continue shopping'}">{l s='Continue shopping'}</a>
				<a href="{*if (isset($smarty.server.HTTP_REFERER) && strstr($smarty.server.HTTP_REFERER, $link->getPageLink('order.php'))) || !isset($smarty.server.HTTP_REFERER)*}{$link->getPageLink('showroom.php')}{*else}{$smarty.server.HTTP_REFERER|escape:'htmlall':'UTF-8'|secureReferrer}{/if*}" class="back_showroom" id="backaddress2" title="{l s='Continue shopping'}" style="display:none;"></a>
			{else}
				<a href="{*if (isset($smarty.server.HTTP_REFERER) && strstr($smarty.server.HTTP_REFERER, $link->getPageLink('order.php'))) || !isset($smarty.server.HTTP_REFERER)*}{$link->getPageLink('lookbook.php')}{*else}{$smarty.server.HTTP_REFERER|escape:'htmlall':'UTF-8'|secureReferrer}{/if*}" class="buttonmedium pink" style="float: left"; id="backaddress" title="{l s='Continue'}">{l s='Continue shopping'}</a>
				<a href="{*if (isset($smarty.server.HTTP_REFERER) && strstr($smarty.server.HTTP_REFERER, $link->getPageLink('order.php'))) || !isset($smarty.server.HTTP_REFERER)*}{$link->getPageLink('lookbook.php')}{*else}{$smarty.server.HTTP_REFERER|escape:'htmlall':'UTF-8'|secureReferrer}{/if*}" class="back_showroom" id="backaddress2" title="{l s='Continue'}" style="display:none;"></a>
			{/if}
			{*<a href="{$link->getPageLink('showroom.php',true)}" class="btn_cont_shopping" title="{l s='Continue shopping'}"></a>*}
  			<input type="hidden" name="numberOfAccessorisedProducts" value="{$numberOfAccessorisedProducts}"/>
            <input type="hidden" name="numberOfAccessories" value="{$numberOfAccessories}"/>
            <input type="hidden" class="hidden" name="step" value="2" />
			<input type="submit" name="processAddress" value="{l s='Continue'}" class="buttonmedium blue" style="float: right" id="placeorderbtn"/>
			<input type="submit" name="processAddress" value="" class="place order" id="placeorderbtn2" style="display:none;"/>
		</div>
	 </form>
	{*<p class="cart_navigation">
		{if !$opc}<a href="{$link->getPageLink('order.php', true)}?step=1{if $back}&amp;back={$back}{/if}" class="exclusive" title="{l s='Next'}">{l s='Next'} &raquo;</a>{/if}
		<a href="{if (isset($smarty.server.HTTP_REFERER) && strstr($smarty.server.HTTP_REFERER, $link->getPageLink('order.php'))) || !isset($smarty.server.HTTP_REFERER)}{$link->getPageLink('index.php')}{else}{$smarty.server.HTTP_REFERER|escape:'htmlall':'UTF-8'|secureReferrer}{/if}" class="button_large" title="{l s='Continue shopping'}">&laquo; {l s='Continue shopping'}</a>
	</p>*}

	{include file="$tpl_dir./cart_bottom_footer.tpl"}

	{*<p class="cart_navigation_extra">
		<span id="HOOK_SHOPPING_CART_EXTRA">{$HOOK_SHOPPING_CART_EXTRA}</span>
	</p>*}

</div>
{/if}
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
<script type="text/javascript">
	{literal}
	$(function() {
		$('#placeorderbtn').click(function(e) {

			if ($('.passive-product').length) {
				e.preventDefault();
				alert("{/literal}{l s='Some products in your cart are out of stock. Please modify your order first and try again.' js=1}{literal}");
			}
			if ($('.disabled-product').length) {
				e.preventDefault();
				alert("{/literal}{l s='Some products in your cart are no longer being sold. Please modify your order first and try again.' js=1}{literal}");
			}

		});
		$('.right-arrow-passive').click(function() {
			alert("{/literal}{l s='The number of products you\'d like to buy is beyond our stock.' js=1}{literal}");
		});
	});
	{/literal}
</script>


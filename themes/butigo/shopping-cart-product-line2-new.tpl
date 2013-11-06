<!--[if IE 7]>
    <style type="text/css">
     {literal}
       #continue-button-step1 {
            float: left;
            line-height: 22px;
            text-align: center;
            width: 80%;
        }
        .shipping-details-tool-tip {
            padding: 9px 0 9px 9px;
        }
        #cart-img {
            background-image: none;
            display: none;
            float: none;
        }
      {/literal}
    </style>
<![endif]-->
<!--[if IE 9]>
    <style type="text/css">
     {literal}
        .tooltip {
            left: 220px;
        }
      {/literal}
    </style>
<![endif]-->

    <!--[if IE]>
    <style type="text/css">
     {literal}
      .safe-secure-tool-tip {
        width: 105px;
        }
      {/literal}
    </style>
<![endif]-->
{*cart Details*}
<div id="cart-details">
    <table  class="right-cart-details-table" border="0">


                <div class="right-header">{l s='Order Summary'}</div>

                <tbody>
        {assign var='totalProductsPrice' value=0}
        {foreach from=$products item=product name=productLoop}
            {assign var='productId' value=$product.id_product}
            {assign var='productAttributeId' value=$product.id_product_attribute}
            {assign var='quantityDisplayed' value=0}
            {assign var='productsPrice' value=$product.total_wt}
            {assign var='totalProductsPrice' value=$totalProductsPrice+$productsPrice}
            {*<tr class="{if $smarty.foreach.productLoop.last}last_item{elseif $smarty.foreach.productLoop.first}first_item{/if}{if isset($customizedDatas.$productId.$productAttributeId) AND $quantityDisplayed == 0}alternate_item{/if} cart_item">
                <td class="cart_product td-left">
                        <div class = "product_image">
                                <img src="{$link->getImageLink($product.link_rewrite, $product.id_image, 'prodthumb')}" alt="{$product.name|escape:'htmlall':'UTF-8'}" />
                        </div>
                        <div class="about-product">
                                <span class="cart_product_name">{$product.name|escape:'htmlall':'UTF-8'}</span><br />
                                {if $product.attributes}<span style="font-size:0.85em">{$product.attributes|escape:'htmlall':'UTF-8'|truncate:22:"":true}</span>{/if}</br>
                                {if $quantityDisplayed == 0 AND isset($customizedDatas.$productId.$productAttributeId)}<span style="font-size:0.85em">{l s='Qty: '}{$customizedDatas.$productId.$productAttributeId|@count}</span>{else}<span style="font-size:0.85em">{l s='Qty: '}{$product.cart_quantity-$quantityDisplayed}</span>{/if}
                        </div>
                </td>
                <td class="price">
                        <span id="total_product_price_{$product.id_product}_{$product.id_product_attribute}">
                                {if $quantityDisplayed == 0 AND isset($customizedDatas.$productId.$productAttributeId)}
                                        {if !$priceDisplay}{displayPrice price=$product.total_customization_wt}{else}{displayPrice price=$product.total_customization}{/if}
                                {else}
                                        {if !$priceDisplay}{displayPrice price=$product.total_wt}{else}{displayPrice price=$product.total}{/if}
                                {/if}
                        </span>
                </td>
            </tr>*}
        {/foreach}

        {*Product Total Start*}
            <tr><td class="td-left">{l s='Product Total:'}</td><td class="price">{displayPrice price=$totalProductsPrice}</td></tr>
        {*Product Total End*}

        {*Shipping*}
        {if $use_taxes}
            {if $priceDisplay}
                <tr class="cart_total_delivery" {*if $shippingCost <= 0} style="display:none;"{/if*}>
                        <td colspan="1">{l s='Total shipping'}{if $display_tax_label} {l s='(tax excl.)'}{/if}{l s=':'}</td>
                        <td class="price" id="total_shipping">{displayPrice price=$shippingCostTaxExc}</td>
                </tr>
            {else}
                <tr class="cart_total_delivery"{*if $shippingCost <= 0} style="display:none;"{/if*}>
                        <td colspan="1">{l s='Total shipping'}{if $display_tax_label} {l s='(tax incl.)'}{/if}{l s=':'}</td>
                        <td class="price" id="total_shipping" >{displayPrice price=$shippingCost}</td>
                </tr>
            {/if}
        {else}
            <tr class="cart_total_delivery"{*if $shippingCost <= 0} style="display:none;"{/if*}>
                <td class="td-left" colspan="1">{l s='Total shipping:'}</td>
                <td class="price" id="total_shipping" >{displayPrice price=$shippingCostTaxExc}</td>
            </tr>
        {/if}
        {*Shipping*}

            <tr><td class="hr-rule"></td><td class="hr-rule"></td></tr>

        {*Sub Total*}
            {*<tr><td>{l s='SubTotal:'}</td><td class="price">{displayPrice price=$total_price}</td></tr>*}
        {*Sub Total*}
        {*<tr><td class="hr-rule"></td></tr>*}

        {*Discounts*}
        {if sizeof($discounts)}
            {foreach from=$discounts item=discount name=discountLoop}
                <tr><td>{l s='Discounts / Vouchres:'}</td><td class="price">-{displayPrice price=$discount.value_real}</td></tr>
            {/foreach}
            <tr><td class="hr-rule"></td><td class="hr-rule"></td></tr>
        {/if}
        {*Discounts*}
        {*Installments*}
             {*<tr class="installments-option hidden"><td>{l s='Installments'}: <span id="installments-count">1 </span></td><td></td></tr>*}
             <tr class="installments-option hidden">
                <td>{l s='Interest Amount'}:&nbsp;(<span id="installments-count">1</span>&nbsp;{l s='Installments'}):</td>
                <td id="interest-amount" class="price">{displayPrice price=0.00}</td>
             </tr>
             <tr class="installments-option hidden"><td class="hr-rule"></td><td class="hr-rule"></td></tr>
        {*Installments*}
        {*COD extra shiiping cost of 3TL*}
            <tr class="cod_extra_shipping hidden">
                <td>{l s='COD Extra Shipping:'}</td>
                <td class="price">{displayPrice price=$shippingChargeForCashOnDelivery}</td>
            </tr>
            <tr class="cod_extra_shipping hidden"><td class="hr-rule"></td><td class="hr-rule"></td></tr>
        {*COD extra shiiping cost of 3TL*}
        </tbody>
      <tfoot>
                {if $use_taxes}
                        <tr class="cart_total_label" colspan=2><td>
                                {if $display_tax_label}
                                        {l s='Total Amount'}{*&nbsp;<span>{l s='TAX INCL'*}</span>
                                {else}
                                        {l s='Total Amount'}
                                {/if}
                        </td>
                        <td id="spc-total-price">{displayPrice price=$total_price}</td>
                        </tr>
                        {*<tr class="cart_total_price" colspan=2><td id="spc-total-price">{displayPrice price=$total_price}</td></tr>*}
                {else}
                        <tr class="cart_total_label" colspan=2><td>{l s='Total Amount'}</td></tr>
                        <tr class="cart_total_price" colspan=2>><td id="spc-total-price">{displayPrice price=$total_price_without_tax}</td></tr>
                {/if}
     </tfoot>
    </table>
      <a href="{$link->getPageLink('faqs.php')}" target="_blank" class="shipping-details-tool-tip">{l s='Shipping and Tax Details'}</a>
<div id="shoppingbanners">
<div id="secure-checkout-outer">
            <a rel="nofollow" class="secure-checkout-img" id="{$product.id_product}_{$product.id_product_attribute}_{$id_customization}" href="#">&nbsp;</a>
            <span id="secure-checkout-label">{l s='Secure Checkout'}</span><br/>
            <span id="secure-checkout-details">{l s='Shopping is always'}</span>
            <span class="tooltip"><a href="" class="safe-secure-tool-tip" alt="{l s='tooltip'}">{l s='safe and secure'}</a><span>{l s='Butigo daki her alışverişiniz 256bit SSL güvenliği ile korunmaktadır. Ayrıca tüm sorularınız için bizi 216 418 26 26` dan istediğiniz zaman arayabilirsiniz.'}</span></span>
        </div>

        {if $step1 == 1}
            <a href="{$link->getPageLink('order.php',true)}{if $product.stock_quantity == 0}?step=1{else}?step=2{/if}" class="continue-button"><span id=continue-button-step1>{l s='Continue'}</span>&nbsp;<span id="cart-img">&nbsp;</span></a>
        {elseif $step2 == 2}
            <a href="javascript:void(0)" id="submit-address-payment"><span id="address-checkout">{l s='Submit Address'}</span><span id="payment-checkout">{l s='Checkout'}</span> <span id="payment-process" class="hidden">{l s='Order Processing' mod='mediator'}....</span></a>
        {else}
            <a href="{$link->getPageLink('order.php',true)}{if $product.stock_quantity == 0}?step=1{else}?step=2{/if}" class="continue-button"><span id=continue-button-step1>{l s='Continue'}</span>&nbsp;<span id="cart-img">&nbsp;</span></a>
        {/if}
        <div id="bmg-text-bold">
            <p id="bmg-bold-text">{l s='• BMG (BUTİGO MUTLULUK GARANTİSİ)'}&nbsp;<span class="tool-tip"><img src="{$img_dir}product/tooltip.gif" alt="{l s='tooltip'}" /><span>{l s='Butigo da mutluluğunuz bizim için herşeyden önce gelir. Bunun için aradığınız numarayı bulana kadar sınırsız değişim yapabilirsiniz. Üstelik değişim ile ilgili kargo masraflarınız bize ait olarak.'}</span></span></p>
        </div>
        <span class="bmg-text">{l s='• 3, 6, 12 Financing Facility / Cash on Delivery Option'}</span>
        <span class="bmg-text">{l s='• Free Shipping for 150+ TL Orders'}</span>


        <span class="contact-info-content">{l s='Contact Address:'}</span>
        <span class="contact-info-content-number">{l s='216 418 26 26'}</span>
        <span href="#" id="destek-link">{l s='destek@butigo.com'}</span>

        <a href="{$link7}" target="_blank" class="poilcy-links">{l s='Shipment and Return Policy'}</a>
        <a href="{$link8}" target="_blank" class="poilcy-second-link">{l s='Privacy Policy'}</a>
    </div>

    {*<div id="shoppingbanners">
        <div id="kargo_image"></div>
        <div id="kapida_image"></div>
        <div id="taksit_image"></div>
        <div id="numara_image"></div>
    </div>

    <div class="ykb_joker_vada_campaign_83"></div>*}
</div>
</div>
{*cart Details*}

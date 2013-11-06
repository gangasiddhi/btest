{*cart Details*}
<div id="cart-details">
    <table  class="right-cart-details-table" border="0">
        <thead>
            <tr>
                <th class="left-th">{l s='Order Summary'}</th>
                <th class="right-th"><a class="link-underline" href="{$link->getPageLink('order.php')}?step=1">{l s='View Cart'}</a></th>
            </tr>
        </thead>
        <tbody>
        {assign var='totalProductsPrice' value=0}
        {foreach from=$products item=product name=productLoop}
            {assign var='productId' value=$product.id_product}
            {assign var='productAttributeId' value=$product.id_product_attribute}
            {assign var='quantityDisplayed' value=0}
            {assign var='productsPrice' value=$product.total_wt}
            {assign var='totalProductsPrice' value=$totalProductsPrice+$productsPrice}
            <tr class="{if $smarty.foreach.productLoop.last}last_item{elseif $smarty.foreach.productLoop.first}first_item{/if}{if isset($customizedDatas.$productId.$productAttributeId) AND $quantityDisplayed == 0}alternate_item{/if} cart_item">
                <td class="cart_product td-left">
                        <div class = "product_image">
                                <img src="{$link->getImageLink($product.link_rewrite, $product.id_image, 'prodthumb')}" alt="{$product.name|escape:'htmlall':'UTF-8'}" />
                        </div>
                        <div class="about-product">
                                <h5 class="cart_product_name">{$product.name|escape:'htmlall':'UTF-8'}</h5>
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
            </tr>
        {/foreach}

        {*Product Total Start*}
            <tr><td class="td-left">{l s='Product Total:'}</td><td class="price">{displayPrice price=$totalProductsPrice}</td></tr>
        {*Product Total End*}

            <tr><td class="hr-rule"></td><td class="hr-rule"></td></tr>

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
                <td>{l s='Insterest Amount'}:&nbsp;(<span id="installments-count">1</span>&nbsp;{l s='Installments'}):</td>
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

    <div id="shoppingbanners">
        <div id="kargo_image"></div>
        <div id="kapida_image"></div>
        <div id="taksit_image"></div>
        <div id="numara_image"></div>
    </div>

    {*<div class="ykb_joker_vada_campaign_83"></div>*}
</div>
</div>
{*cart Details*}

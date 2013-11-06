{if isset($two_page_checkout) && ! $two_page_checkout}
    {assign var='current_step' value='confirmation'}
    {include file=$tpl_dir./order-steps.tpl}
{/if}

<div class="shopping-cart">
    <div id="order-conf">
        <div class="order-success-data">
            <h4><span class="pink-text"> TEBRİKLER!</span>&nbsp;&nbsp;<span class="grey-text">ALIŞVERİŞİN BAŞARIYLA TAMAMLANDI.</span></h4>
            <p>Birazdan sana, siparişinle ilgili detayları içeren bir e-posta göndereceğiz.<br/>
            {if $order->isBackOrder() != 1}Eğer vermiş olduğunuz sipariş ön sipariş ise{else}Siparişin ön sipariş olduğu için{/if} en geç 10 gün içersinde kargoya verilecektir.<br/>
            Ardından siparişin 1 gün içerisinde kargoya aktarılacak ve bununla ilgili bir e-posta daha göndereceğiz.<br/>
            Böylece Butigo'ların sana doğru yola çıkmış olacaklar. Bu e-posta'dan ortalama 2-3 gün sonra elinde olmalarını bekleyebilirsin.<br/>
            Bu sırada aklına takılan herhangi bir şey olursa, bize istediğin zaman <span class="blue-text">destek@butigo.com</span> adresinden ve  <span class="blue-text">(216) 418 26 26 </span> numaralı telefonumuzdan ulaşabilirsin.<br/>
            Alışverişin için teşekkürler. Umarız seni daha uzun zaman aramızda görmeye devam ederiz.</p>
            <p>Sevgiler,<br/>Butigo Müşteri Mutluluk Ekibi</p>
        </div>
         {*<div id="social-network" class="order-success-social-link">
            <ul>
                <li><h4>{l s='HAPPY WITH PURCHASE' mod='pgtw'}&nbsp;?&nbsp;&nbsp; <span>{l s='SHARE IT' mod='pgtw'}&nbsp;!</span></h4></li>
                <li id="fb"><a title="Facebook" target="_blank" href="http://www.facebook.com/butigo">Facebook</a></li>
                <li id="tt"><a title="Twitter" target="_blank" href="http://www.twitter.com/butigocom">Twitter</a></li>
            </ul>
        </div>*}
    </div>
    {if isset($HOOK_ETTIKETT)} {$HOOK_ETTIKETT}{/if}
    {if isset($HOOK_TODAY_DISCOUNT)}{$HOOK_TODAY_DISCOUNT}{/if}
    <div id="order-detail-content" class="table_block">
        <table id="order_summary">
            <thead>
                <tr>
                    <th class="order_product item">{l s='Product' mod='pgtw'}</th>
                    <th class="order_quantity item">{l s='Qty' mod='pgtw'}</th>
                    <th class="order_unit item">{l s='Unit price' mod='pgtw'}</th>
                    <th class="order_total last_item">{l s='Total' mod='pgtw'}</th>
                </tr>
            </thead>

            <tfoot>
                <tr class="order_total_price">
                    <td colspan="3" class="order_total_label">
                        {l s='Total' mod='pgtw'}
                        &nbsp;
                        <span>({l s='Tax Incl.' mod='pgtw'})</span>
                    </td>

                    <td class="order_total">
                        <span class="price">{displayWtPriceWithCurrency price=$order->total_paid currency=$currency convert=0}</span>
                    </td>
                </tr>
            </tfoot>

            <tbody>
                {foreach from=$products item=product name=productLoop}
                    {assign var='productId' value=$product.id_product}
                    {assign var='productAttributeId' value=$product.id_product_attribute}
                    {assign var='quantityDisplayed' value=0}

                    {* Display the product line *}

                    <tr class="{if $smarty.foreach.productLoop.last}last_item{elseif $smarty.foreach.productLoop.first}first_item{/if}{if isset($customizedDatas.$productId.$productAttributeId) AND $quantityDisplayed == 0}alternate_item{/if} order_item">
                        <td class="order_product">
                            <div class="product_image" style="height:60px">
                                <img src="{$link->getImageLink($product.link_rewrite, $product.id_image, 'prodthumb')}"
                                    alt="{$product.name|escape:'htmlall':'UTF-8'}" />
                            </div>

                            <div class="about-product">
                                <h5 class="order_product_name">
                                    {$product.product_name|escape:'htmlall':'UTF-8'}
                                </h5>
                            </div>
                        </td>

                        <td class="order_quantity" style="text-align: center;">
                            <div class="quantity_order">
                                {$product.product_quantity}
                            </div>
                        </td>

                        <td class="order_unit">
                            <span class="price">
                                {displayPrice price=$product.product_price_wt}
                            </span>
                        </td>

                        <td class="order_total">
                            <span class="price">
                                {displayPrice price=$product.total_wt}
                            </span>
                        </td>
                    </tr>

                    {* Then the customized datas ones *}
                    {if isset($customizedDatas.$productId.$productAttributeId)}
                        {foreach from=$customizedDatas.$productId.$productAttributeId key='id_customization' item='customization'}
                            <tr class="alternate_item cart_item">
                                <td colspan="5">
                                    {foreach from=$customization.datas key='type' item='datas'}
                                        {if $type == $CUSTOMIZE_FILE}
                                            <div class="customizationUploaded">
                                                <ul class="customizationUploaded">
                                                    {foreach from=$datas item='picture'}
                                                        <li>
                                                            <img src="{$pic_dir}{$picture.value}_small" alt=""
                                                                class="customizationUploaded" width="{$smallSize.width}"
                                                                height="{$smallSize.height}"/>
                                                        </li>
                                                    {/foreach}
                                                </ul>
                                            </div>
                                        {elseif $type == $CUSTOMIZE_TEXTFIELD}
                                            <ul class="typedText">
                                                {foreach from=$datas item='textField' name='typedText'}
                                                    <li>
                                                        {if $textField.name}
                                                            {$textField.name}
                                                        {else}
                                                            {l s='Text #' mod='pgtw'}
                                                            {$smarty.foreach.typedText.index+1}
                                                        {/if}
                                                        : {$textField.value}
                                                    </li>
                                                {/foreach}
                                            </ul>
                                        {/if}
                                    {/foreach}
                                </td>

                                <td class="cart_quantity">
                                    <a class="cart_quantity_delete"
                                        href="{$base_dir_ssl}cart.php?delete&amp;id_product={$product.id_product|intval}&amp;ipa={$product.id_product_attribute|intval}&amp;id_customization={$id_customization}&amp;token={$token_cart}">
                                        <img src="{$img_dir}icon/delete.gif" alt="{l s='Delete' mod='pgtw'}" title="{l s='Delete this customization' mod='pgtw'}" class="icon" width="11" height="13" />
                                    </a>

                                    <p>{$customization.quantity}</p>

                                    <a class="cart_quantity_up"
                                        href="{$base_dir_ssl}cart.php?add&amp;id_product={$product.id_product|intval}&amp;ipa={$product.id_product_attribute|intval}&amp;id_customization={$id_customization}&amp;token={$token_cart}" title="{l s='Add' mod='pgtw'}">
                                        <img src="{$img_dir}icon/quantity_up.gif" alt="{l s='Add' mod='pgtw'}" width="14" height="9" />
                                    </a>

                                    <br />

                                    <a class="cart_quantity_down"
                                        href="{$base_dir_ssl}cart.php?add&amp;id_product={$product.id_product|intval}&amp;ipa={$product.id_product_attribute|intval}&amp;id_customization={$id_customization}&amp;op=down&amp;token={$token_cart}" title="{l s='Subtract' mod='pgtw'}">
                                        <img src="{$img_dir}icon/quantity_down.gif" alt="{l s='Subtract' mod='pgtw'}" width="14" height="9" />
                                    </a>
                                </td>

                                <td class="cart_total"></td>
                            </tr>

                            {assign var='quantityDisplayed' value=$quantityDisplayed+$customization.quantity}
                        {/foreach}

                        {* If it exists also some uncustomized products *}

                        {if $product.quantity-$quantityDisplayed > 0}
                            {include file=$tpl_dir./shopping-cart-product-line.tpl}
                        {/if}
                    {/if}
                {/foreach}

                {*Shipping Details*}
                {if $order->total_shipping != 0}
                    <tr class="shipping_cost">
                        <td colspan="3" class="shipping-label">{l s='Shipping Cost' mod='pgtw'}</td>
                        <td class="shipping-price order_total"><span class="price">{displayPrice price=$order->total_shipping}</span></td>
                    </tr>
                {/if}

                {*Installment Details*}
                {if $order->installment_count > 1}
                    <tr class="installment-details">
                        <td colspan="3" class="installment-label">{l s='Interest Amount' mod='pgtw'}&nbsp;({$order->installment_count}&nbsp;{l s='Installments' mod='pgtw'})</td>
                        <td class="order_total"><span class="price">{displayWtPriceWithCurrency price=$installment_amount currency=$currency convert=0}</span></td>
                    </tr>
                {/if}

                {*Discounts*}
                {if $discounts}
                    {foreach from=$discounts item=discount name=discountLoop}
                        <tr class="order_discount {if $smarty.foreach.discountLoop.last}last_item{elseif $smarty.foreach.discountLoop.first}first_item{else}item{/if}">
                            <td class="discount-description" colspan="3">{l s='Discount' mod='pgf'}&nbsp;({$discount.name})</td>
                            <td class="order_total">
                                <span class="price">
                                    {if $discount.value > 0}
                                        {if ! $priceDisplay}
                                            {displayPrice price=$discount.value*-1}
                                        {else}
                                            {displayPrice price=$discount.value_tax_exc*-1}
                                        {/if}
                                    {/if}
                                </span>
                            </td>
                        </tr>
                    {/foreach}
                {/if}
            </tbody>
        </table>
    </div>

    {*Footer Banner*}
    {include file="$tpl_dir./cart_bottom_footer.tpl"}
</div>
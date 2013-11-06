{if $bu_env=='production'}
    <div class="hidden">
        {* Google Remarketing Code for Payment Page *}
        <script type="text/javascript">
            var google_conversion_id = 1009336416;
            var google_conversion_language = "en";
            var google_conversion_format = "3";
            var google_conversion_color = "ffffff";
            var google_conversion_label = "bDhrCOjm1QIQ4ICl4QM";
            var google_conversion_value = 0;
        </script>

        <script type="text/javascript" src="https://www.googleadservices.com/pagead/conversion.js"></script>

        <noscript>
            <div style="display:inline;">
                <img height="1" width="1" style="border-style:none;" alt=""
                    src="https://www.googleadservices.com/pagead/conversion/1009336416/?label=bDhrCOjm1QIQ4ICl4QM&amp;guid=ON&amp;script=0"/>
            </div>
        </noscript>
    </div>
{/if}

<div class="hidden">
    <span class="ajax-js" jsfile="{$modules_dir}/mediator/scripts/bank.js"></span>
    <script type="text/javascript">
        var totalPrice = '{$totalPrice}';
        var sign = '{$currencyType}' ;
    </script>
</div>

<div class="shopping-cart">
    <form action="{$this_path_ssl}validation.php" method="post" id="turkcell-form">
        <div id ="payment_details">
            <div class="formContainer" id="payment_method">
                <div id="credit-top-left">
                        {*<h3>Turkcell Safety Logo Here</h3>*}
                        <img id="turkcell-logo" src="{$media_server}{$modules_dir}/pgtw/img/Turkcell-Cuzdan-Badge.png" alt=""/>
                </div>
                <div id="credit-top-right">
                        <span id="siteseal">
                                <script type="text/javascript" src="https://seal.godaddy.com/getSeal?sealID=a8HAiCV0mPmfwR2K6r1hCfL4bRoZm9o2qm1oZ0w0sXGH7JfHEHlSyvKdiBT"></script>
                        </span>
                        {*<a href="#popup-instal" id="show-instal-popup">{l s='Taksit Seçenekleri' mod="pgtw"}</a>*}
                        <a href="#" id="show-instal-popup">{l s='Taksit Seçenekleri' mod="pgtw"}</a>
                        <a href="http://www.turkcell.com.tr/bireysel/yardim/Sayfalar/Duyurular/Turkcell-Cuzdan-ile-internette-guvenli-ve-hizli-alisveris.aspx"
                                target="_blank"><img id="turkcell-banner" src="{$media_server}{$modules_dir}/pgtw/img/Turkcell-Cuzdan-Banner.png" alt="" /></a>
                </div>
                <div id="credit-card-details">
                    <fieldset class="medium clearAfter">
                        <div class="credit-card-entries">
                            <div class="credit-label">
                                    <span>{l s='Turkcell Cell Phone Number' mod='pgtw'}<em>*</em></span>
                                    <span class="label-bottom-text">{l s='Ex:' mod='pgtw'}5321234567</span>
                            </div>
                            <div class="credit-input">
                                    <input autocomplete="off" name="cell_phone" size="24" maxlength="10" value="" type="text" id="id_cell_phone"/>
                            </div>
                        </div>

                        <div class="credit-card-entries" id="turkcell-cards" {*style="display:none;"*}>
                            <div class="credit-label">
                                        <span>{l s='Choice of Card' mod='pgtw'}</span>
                            </div>
                            <select class="turkcell-card" name="card_choice">
                                    <option value="0">{l s='Select your card option' mod='pgtw'}</option>
                                    <option value="1">{l s='Garanti Param' mod='pgtw'}</option>
                                    <option value="2">{l s='Garanti Bonus' mod='pgtw'}</option>
                                    <option value="3">{l s='Garanti Miles & Smiles' mod='pgtw'}</option>
                                    <option value="4">{l s='Garanti Paracard' mod='pgtw'}</option>
                                    <option value="5">{l s='Garanti CepT Paracard' mod='pgtw'}</option>
                            </select>
                        </div>

                        <div class="credit-card-entries" id="turkcell-installments" style="display:none;">
                            <div class="credit-label">
                                <span>{l s='Choice of Installment' mod='pgtw'}</span>
                                <span class="label-bottom-text">
                                    {l s='Amount Per Installment & Total Amount' mod='pgtw'}
                                </span>
                            </div>

                            <select name="installment_choice" id="turkcell-installment">
                                {foreach from=$installmentsOptions item=installmentInterestRate key=intsallmentOption}
                                    {assign var=totalAmount value="totalAmountFor"|cat:$intsallmentOption|cat:"Installments"} 
                                    {assign var=eachInstallAmount value="eachInstallmentAmountFor"|cat:$intsallmentOption|cat:"Installments"}
                                    {assign var=interestAmount value="interestAmountFor"|cat:$intsallmentOption|cat:"Installments"}
                                    <option {if $intsallmentOption == 1}selected="selected"{/if} value="{$intsallmentOption}" total="{$installmentsAmount.$totalAmount}" instal_amt="{$installmentsAmount.$eachInstallAmount}" interest_amt="{$installmentsAmount.$interestAmount}"> {if $intsallmentOption == 1}{l s='No Installments' mod='pgtw'}{else}{$intsallmentOption} ({$installmentsAmount.$eachInstallAmount} {$currencyType} - {$installmentsAmount.$totalAmount} {$currencyType}){/if}</option>
                                    {$installmentsAmount.$totalAmount}{$installmentsAmount.$eachInstallAmount}<br>
                                {/foreach}
                            </select>
                        </div>

                        <div class="credit-card-entries" id="turkcell-bonus" style="display:none;">
                            <div class="credit-label">
                                <span>{l s='Bonus Amount to Use' mod='pgtw'}</span>
                                <span class="label-bottom-text">{l s='Optional' mod='pgtw'} </span>
                            </div>
                            <div class="credit-input">
                                <input autocomplete="off" name="bonus" size="24" maxlength="10" value="" type="text" id="id_bonus"/>
                            </div>
                        </div>

                        <div class="check-agree" id="turkcell-agree">
                            <input type="checkbox" name="check_agree" id="agree_sales" class="sales_agreemnt"/>
                            <input type="hidden" id="display-installment" name="display_installment" value="0"/>
                            <a class="agree" id="agreement1" title="Pre Sales Agreement" href="{$base_dir_ssl}agreements.php?id_cms=20&content_only=1">{l s='Ön-satış sözleşmesini' mod='mediatior'}</a>
                            {if $is_member == 1}
                                {l s='ve' mod='pgtw'}&nbsp;<a class="agree" id="agreement2" title="Sales Agreement"  href="{$base_dir_ssl}agreements.php?id_cms=21&content_only=1">{l s='satış sözleşmesini' mod='pgtw'}</a>{l s=' onaylıyorum.' mod='pgtw'}
                            {elseif $is_member == 2}
                                {l s='ve' mod='pgtw'}&nbsp;<a class="agree" id="agreement3" title="Sales Agreement"  href="{$base_dir_ssl}agreements.php?id_cms=22&content_only=1">{l s='satış sözleşmesini' mod='pgtw'}</a>{l s=' onaylıyorum.' mod='pgtw'}
                            {/if}
                        </div>
                    </fieldset>
                </div>
            </div>

             {* <hr style=" margin: 8px 0 8px 0;"/>*}
            <input type="hidden" id="finalTotal" name="finalTotal" value="{$totalPrice}"/>

            <div id="display-total">
                <div id ="final_total">
                    {l s='Toplam' mod='pgtw'} <span class="cart_total_label">{l s='KDV Dahil' mod='pgtw'}</span>
                    <span id="submit_total">{displayPrice price=$totalPrice}</span>
                </div>
                 {* <hr style="margin:10px 0"/>*}
                <div id="submit-payment" class="turkcell-pay-button">
                    <input type="hidden" name="id_address_delivery" value="142"/>
                    <input disabled type="submit"  name="paymentSubmit" value="{l s='CheckOut' mod='pgtw'}" class="buttonmedium blue checkOut" id="pay_button"/>
                </div>
            </div>
        </div>

    {*popup installments start*}
    {*<div id="popup-installment" style="display:none;">
        {Data will be attached via javascript}
        <div id="popup-instal">
        </div>
    </div>*}
    {*popup installments end*}

    </form>
    {if !$twoStepCheckout}
    {include file="$tpl_dir./cart_bottom_footer.tpl"}
    {/if}
</div>

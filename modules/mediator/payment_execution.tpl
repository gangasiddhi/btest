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
                    src="https://www.googleadservices.com/pagead/conversion/1009336416/?label=bDhrCOjm1QIQ4ICl4QM&amp;guid=ON&amp;script=0" />
            </div>
        </noscript>
    </div>
{/if}

<div class="hidden">
    <span class="ajax-js" jsfile="{$media_server}{$this_path}scripts/bank.js"></span>

    <script type="text/javascript">
        var totalPrice = {$totalPrice};
        var sign = "{$currencyType}";
        var installmentsOptions = {$installmentsOptions|@json_encode};
        var installmentsAmount = {$installmentsAmount|@json_encode};
        var installment = "{l s=' Installments' mod='mediator' js=1}";
        var monthly_payment = "{l s='Monthly Payment' mod='mediator' js=1}";
        var total_amount = "{l s='Total Amount' mod='mediator' js=1}";
        var one_payment = "{l s='No Installment' mod='mediator' js=1}";
        var bank_credit_error = "{l s='Please use a correct credit card number' mod='mediator' js=1}";
        var error_msg = "{l s='Please accept the terms of service before the next step.' mod='mediator' js=1}";
        var installment_error_msg = "{l s='Please select an installment before you proceed.' mod='mediator' js=1}";
        var missing_field_error = "{l s='Please fill in all the fields on the payment form.' mod='mediator' js=1}";
    </script>
</div>

<div class="shopping-cart">
    <form action="{$this_path_ssl}validation.php" method="post">
        {* Payment Details Start *}
        <div id ="payment_details">
            <div class="formContainer" id="payment_method">
                <div id="credit-top-left">
                    <img src="{$img_dir}cart/secure-lock.gif" alt="{l s='Payment Secure' mod='mediator'}" style="float:left;"/>

                    <div id="credit-header">
                        <h3>{l s='Payment Card Details' mod='mediator'}</h3>
                        <span class="label-bottom-text">{l s='Your payments are secured with 256 bits SSL security.' mod='mediator'}</span>
                    </div>
                </div>

                <div id="credit-top-right">                    
                    <a href="#" id="show-instal-popup">{l s='Installment Options' mod='mediator'}</a>
                </div>

                <div id="credit-card-details">
                    <fieldset class="medium clearAfter">
                        <div class="credit-card-entries">
                            <div class="credit-label">
                                <span>{l s='Credit Card Number' mod='mediator'}<em>*</em></span>
                                <span class="label-bottom-text">{l s='16 Digits Credit Card' mod='mediator'}</span>
                                
                            </div>

                            <div class="credit-input">
                                <input autocomplete="off" name="ccnum" size="24" maxlength="16" value="" type="text" id="ccnum"/>
                            </div>
                            <div class="credit-card-icon-con">
                                <i class="icon-item visa passive"></i>
                                <i class="icon-item master-card passive"></i>
                                <i class="icon-item amex passive"></i>
                            </div>
                            {*<img src="{$img_dir}cart/credit_cards.gif" alt="{l s='Types of credit cards' mod='mediator'}" />*}
                        </div>

                        <div class="credit-card-entries">
                            <div class="credit-label">
                                <span>{l s='Expiration' mod='mediator'}<em>*</em></span>
                                <span class="label-bottom-text">{l s='Expiration Date at the front of your card' mod='mediator'}</span>
                            </div>

                            <select id="cc-exp-month" name="ccexp_Month">
                                <option value="00" label="{l s='Month' mod='mediator'}">{l s='Month' mod='mediator'}</option>
                                <option value="01" label="01">01</option>
                                <option value="02" label="02">02</option>
                                <option value="03" label="03">03</option>
                                <option value="04" label="04">04</option>
                                <option value="05" label="05">05</option>
                                <option value="06" label="06">06</option>
                                <option value="07" label="07">07</option>
                                <option value="08" label="08">08</option>
                                <option value="09" label="09">09</option>
                                <option value="10" label="10">10</option>
                                <option value="11" label="11">11</option>
                                <option value="12" label="12">12</option>
                            </select>

                            <select id="cc-exp-year" name="ccexp_Year">
                                <option value="00" label="{l s='Year' mod='mediator'}">{l s='Year' mod='mediator'}</option>
                                {foreach from=$years item=year}
                                    <option value="{$century}{$year}" label="{$year}">{$year}</option>
                                {/foreach}
                            </select>
                        </div>

                        <div class="credit-card-entries">
                            <div class="credit-label">
                                <span>{l s='CVV' mod='mediator'}<em>*</em></span>
                                <span class="label-bottom-text">Kartın arka yüzündeki <br/> 3 haneli güvenlik kodu</span>
                            </div>

                            <div class="credit-input">
                                <input autocomplete="off" name="ccvv2" value="" maxlength="3" size="5" type="text" id="ccvv2"/>
                            </div>

                            <img alt="CVV example" src="{$img_dir}cart/cvv.gif" style="margin-top:-8px;"/>
                            
                            <a id="sslseal" href="#"><img src="https://seal.godaddy.com/images/3/siteseal_gd_3_h_l_m.gif" /></a>
                        </div>
                    </fieldset>
                </div>
            </div>

            {* Agreements start *}
            <div class="check-agree">
                <input type="checkbox" name="check_agree" id="agree_sales" class="sales_agreemnt"/>
                <input type="hidden" id="display-installment" name="display_installment" value="0"/>

                <a class="agree" id="agreement1" title="{l s='Pre Sales Agreement' mod='mediator'}"
                    href="{$base_dir_ssl}agreements.php?id_cms=20&content_only=1">{l s='Ön-satış sözleşmesini' mod='mediatior'}</a>

                {if $is_member == 1}
                    {l s=' ve ' mod='mediator'}&nbsp;<a class="agree" id="agreement2" title="{l s='Sales Agreement' mod='mediator'}"
                        href="{$base_dir_ssl}agreements.php?id_cms=21&content_only=1">{l s='satış sözleşmesini' mod='mediator'}</a>
                        {l s=' onaylıyorum.' mod='mediator'}
                {elseif $is_member == 2}
                    {l s=' ve ' mod='mediator'}&nbsp;<a class="agree" id="agreement3" title="{l s='Sales Agreement' mod='mediator'}"
                        href="{$base_dir_ssl}agreements.php?id_cms=22&content_only=1">{l s='satış sözleşmesini' mod='mediator'}</a>
                        {l s=' onaylıyorum.' mod='mediator'}
                {/if}
            </div>
            {* Agreements End *}

            {* Installment Part Start *}
            <div class="step hidden" id="step_3">
                <table id="installment_details">
                    <thead>
                        <tr>
                            <td></td>
                            <td>{l s='Installment' mod='mediator'}</td>
                            <td>{l s='Monthly Payment' mod='mediator'}</td>
                            <td>{l s='Total Amount' mod='mediator'}</td>
                        </tr>
                    </thead>

                    {* Installments will be displayed via javascript *}
                    <tbody class="with-install hidden"></tbody>
                    </table>

                <input type="hidden" id="instlmnt" name="instlmnt" value="1"/>

                <hr style="margin: 25px 0 0"/>
            </div>
            {* Installment part End *}

            <div id="display-total">
                <input type="hidden" id="finalTotal" name="finalTotal" value="{$totalPrice}"/>

                <div id ="final_total">
                    {l s='Total Amount' mod='mediator'}
                    <span class="cart_total_label">({l s='Tax Inclusive' mod='mediator'})</span>
                    <span id="submit_total">{displayPrice price=$totalPrice}</span>
                </div>

                <div id="submit-payment">
                    <input type="hidden" name="id_address_delivery" value="142"/>
                    <input type="submit" name="paymentSubmit" value="{l s='CheckOut' mod='mediator'}" class="buttonmedium blue checkOut" id="pay_button"/>
                    <input type="text" name="orderProcessing" readonly="readonly" class="buttonmedium blue checkOut hidden" value="{l s='Order Processing' mod='mediator'}...."/>
                </div>
            </div>
        </div>
        {* Payment Details End *}
    </form>

    {if isset($two_page_checkout) && !$two_page_checkout}
        {include file="$tpl_dir./cart_bottom_footer.tpl"}
    {/if}
</div>

{assign var='current_step' value='payment'}
<div class="shopping-cart">
    <div class="hidden">
        <span class="ajax-js" jsfile="{$js_dir}/bank.js"></span>
    </div>
    <form action="{$this_path_ssl}validation.php" id="formx" method="post" onsubmit="return acceptTCPP('{l s='Please accept the terms of service before the next step.' mod='pga' js=1}');">
        <div class="step" id="step_3">
                <span class="payment_steps">{l s='2. Adım' mod='pga'}<br/></span>
                <span class="label-bottom-text">{l s='Ödeme Şekli:' mod='pga'}</span>
                <table id="installment_details">
                    <thead>
                        <tr><td></td><td>{l s="Taksit"}</td><td>{l s="Aylık Ödeme"}</td><td>{l s="Toplam"}</td></tr>
                    </thead>
                    <tbody>
                        <tr><td><input class="instal" type="radio" name="instal" value="1" total="{$total_price}" each_instal_amt="{$total_price}"/><td>{l s="Tek Çekim"}</td><td>{$total_price}</td><td>{$total_price}</td></tr>
                        <tr><td><input class="instal" type="radio" name="instal" value="3" total="{$three_instlmnt_total}" each_instal_amt="{$three_each_instlmnt}"/></td><td>{l s="3"}</td><td>{$three_each_instlmnt}</td><td>{$three_instlmnt_total}</td></tr>
                        <tr><td><input class="instal" type="radio" name="instal" value="6" total="{$six_instlmnt_total}" instal_amt="{$six_each_instlmnt}"/></td><td>{l s="6"}</td><td>{$six_each_instlmnt}</td><td>{$six_instlmnt_total}</td></tr>
                        <tr><td><input class="instal" type="radio" name="instal" value="12" total="{$twelve_instlmnt_total}" instal_amt="{$twelve_each_instlmnt}"/></td><td>{l s="12"}</td><td>{$twelve_each_instlmnt}</td><td>{$twelve_instlmnt_total}</td></tr>
                    </tbody>
                </table>
        </div>

        <div class="step" id="step_4" style="display:none">
                <hr style="margin: 8px 0 20px 0;"/>
                <span class="payment_steps">{l s='3. Adım' mod='pga'}<br/></span>
                <span class="label-bottom-text">{l s='satis sozlesmeleri' mod='pga'}</span>
                <div class ="sales_agreement">
                    <span id="agreement_1"></span>
                </div>
                <div class ="check-agree">
                    <fieldset style="margin: 3px 0 0">
                        <input type="checkbox" id="agree_pre_sales" class="sales_agreemnt" name="sales_agreemnt"/>
                        <label for="user_opt_in_tos" class="no_bold">&nbsp;{l s='Ön-satış sözleşmesini onaylıyorum.' mod='pga'}</label>
                    </fieldset>
                </div>
                {if $is_member == 1}
                    <div class ="sales_agreement">
                        <span id="agreement_2"></span>
                    </div>
               {elseif $is_member == 2}
                     <div class ="sales_agreement">
                        <span id="agreement_3"></span>
                    </div>
                {/if}
                <div class = "check-agree">
                    <fieldset style="margin: 3px 0 0">
                        <input type="checkbox" id="agree_sales" class="sales_agreemnt" name="sales_agreemnt"/>
                        <label for="user_opt_in_tos" class="no_bold">&nbsp;{l s='Satış sözleşmesini onaylıyorum.' mod='pga'}</label>
                    </fieldset>
                </div>
            </div>
            {* Step 5 *}
            <div class="step" id="step_5" style="display:none">
                <hr style="margin: 8px 0 20px 0;"/>
                    <span class="payment_steps">{l s='Son Adım' mod='pga'}</br></span>
                    <div class="formContainer" id="payment_method">
                        <div id="credit-top-left">
                            <img src="{$img_dir}cart/secure-lock.gif" alt="{l s='Payment Secure' mod='pga'}" style="float:left;"/>
                            <div id="credit-header">
                                <h3>{l s='Kredi Kartı Bilgileri' mod='pga'}</h3>
                                <span class="label-bottom-text">{l s='Ödemeniz 256bit SSL güvenliği ile gerçekleşmektedir' mod='pga'}</span>
                            </div>
                        </div>
                        <div id="credit-top-right">
                            <span id="siteseal">
                                <script type="text/javascript" src="https://seal.godaddy.com/getSeal?sealID=a8HAiCV0mPmfwR2K6r1hCfL4bRoZm9o2qm1oZ0w0sXGH7JfHEHlSyvKdiBT"></script>
                            </span>
                        </div>
                        <div id="credit-card-details">
                            <fieldset class="medium clearAfter">
                                <div class="credit-card-entries">
                                    <div class="credit-label">
                                        <span>{l s='Kart üzerindeki Ad-Soyad' mod='pga'}<em>*</em></span>
                                    </div>
                                    <div class="credit-input">
                                        <input name="ccname" size="30" maxlength="20" value="" type="text" id="ccname"/>
                                    </div>
                                </div>
                                <div class="credit-card-entries">
                                    <div class="credit-label">
                                        <span>{l s='Kredi Kartı Numaranız' mod='pga'}<em>*</em></span>
                                        <span class="label-bottom-text">{l s='16 haneli Kredi Kartı Numaranız' mod='pga'}</span>
                                    </div>
                                    <div class="credit-input">
                                        <input name="ccnum" size="24" maxlength="16" value="" type="text" id="ccnum"/>

                                    </div>
                                    <img src="{$img_dir}cart/credit_cards.gif" alt="{l s='Types of credit cards' mod='pga'}"/>
                                </div>
                                <div class="credit-card-entries">
                                    <div class="credit-label">
                                         <span>{l s='Son Kullanma Tarihi' mod='pga'}<em>*</em></span>
                                         <span class="label-bottom-text">{l s=' Kartın ön yüzündeki Son Kullanma Tarihi' mod='pga'}</span>
                                    </div>
                                    <select name="ccexp_Month">
                                        <option value="" label="Ay">Ay</option>
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
                                   <select name="ccexp_Year">
                                        <option value="" label="Yıl">Yıl</option>
                                        {foreach from=$years item=year}
                                            <option value="{$century}{$year}" label="{$year}">{$year}</option>
                                        {/foreach}
                                   </select>
                                </div>
                                <div class="credit-card-entries">
                                    <div class="credit-label">
                                        <span>{l s='CVV' mod='pga'}<em>*</em></span>
                                        <span class="label-bottom-text" style="">{l s="Kartın arka yüzündeki" mod='pga'}<br/>{l s="3 haneli güvenlik kodu" mod='pga'}</span>
                                    </div>
                                    <div class="credit-input">
                                        <input name="ccvv2" value="" maxlength="3" size="5" type="text" id="ccvv2"/>
                                    </div>
                                    <img alt="CVV example" src="{$img_dir}cart/cvv.gif" style="margin-top:-8px;">
                                </div>
                             </fieldset>
                        </div>
                    </div>
                <input type="hidden" id="instlmnt" name="instlmnt"  value=""/>
                <input type="hidden" id="finalTotal" name="finalTotal" value=""/>
                <input type="image" src="/site_media/cache-198/htmlv2/images/checkout/gonder-cc.png" class="ccc" id="gonder-cc"  style="display:none;" value=""/>
                <div id ="final_total">
                    {l s='Toplam' mod='pga'} <span class="cart_total_label">{l s='KDV Dahil' mod='pga'}</span>
                    <span id="submit_total"></span>
                </div>
            </div>
            <hr style="margin:15px 0"/>
            {* Step 6 *}
            <div id ="step_6" style="display:none">
                <div id="submit-payment">
                    <input type="submit" name="paymentSubmit" value="{l s='CheckOut' mod='pga'}" class="buttonmedium blue" id="pay_button" style="margin: 0 12px 0 0" />
                </div>
            </div>
   </form>
    {include file=$tpl_dir./cart_bottom_footer.tpl}
</div>

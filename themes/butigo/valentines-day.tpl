    <script type="text/javascript" src="{$smarty.const._PS_JS_DIR_}jquery.validation.js"></script>
    <script type="text/javascript" src="{$smarty.const._PS_JS_DIR_}valentines-day.js"></script>
    <link rel="stylesheet" type="text/css" media="all" href="{$smarty.const._PS_CSS_DIR_}valentines-day.css" />
    <style>
        #style-profile > div { margin:auto !important; float:none !important; }
    </style>
<div id="second-header">
    <h2>{l s='Valentines Day'}</h2>
</div>
{if $status == 'succesful'}
    <div>
        <p class="successMessageHeader">Tebrikler!</p>
        <p class="successMessageDescription">Sevgililer Günü'nü Butigo'ların ile geçirmeye bir adım daha yaklaştın.  Daha da güzeli bu hediyeyi sana getiren En Sevdiğin Kişi olacak.<span class="special-word"> Ona aşık olmak için bir sebebin daha var :)</span></p>
    </div>
        {else}
<div id="style-profile">

    <div class="valentines-day-form-wrapper">
        
        {if $error }
            <div class="error message">
		<a id="error-close"></a>
		<div id="err">{$error}</div>
	</div>
        {/if}
        {*include file="$tpl_dir./errors.tpl"*}

        <form method="post" id="new_user" class="new_user" action="{$link->getPageLink('valentines-day.php')}" {*onsubmit="return acceptCGV('{l s='Please accept the terms of service before the next step.' js=1}');"*}>

            {$HOOK_CREATE_ACCOUNT_TOP}
            <fieldset class="medium clearAfter valentines-day-form-fieldset">
                <p>
                    <label for="customer_name">{l s='Gönderen Adı'}</label>
                    <input type="text" class="sstext" id="customer_name" name="customer_name" value="Bir Dost" />
                </p>
                <p>
                    <label for="boyFriendOrPartnerName">{l s='Gönderilecek Kişi'}</label>
                    <input type="text" class="sstext" name="boyFriendOrPartnerName" id="boyFriendOrPartnerName" />
                </p>
                <p>
                    <label for="boyFriendOrPartnerEmail">{l s='Gönderilecek E-Posta'}</label>
                    <input type="text" class="sstext" name="boyFriendOrPartnerEmail" id="boyFriendOrPartnerEmail" />
                </p>
                <p>
                    <label for="emailSubject">{l s='E-Posta Başlığı'}</label>
                    <input type="text" class="sstext" name="emailSubject" id="emailSubject" value="14 Şubat'ı onunla geçirmek istiyor" />
                </p>
                <p>
                    <label for="giftUrl">{l s='Hediyenin Adresi'}</label>
                    <input type="text" class="sstext" name="giftUrl" id="giftUrl" />
                    <span class="comment">(Örnek http://www.butigo.com/1609-cannel-siyah.html)</span>
                </p>
                
            </fieldset>
            <div style="float:left; width:100%">
                <input type="hidden" name="qqa_1000" value="{$smarty.post.qqa_1000}" />
                <input type="hidden" name="qqa_1001" value="{$smarty.post.qqa_1001}" />
                <input type="hidden" name="qqa_1002" value="{$smarty.post.qqa_1002}" />
                <input type="hidden" name="qqa_1003" value="{$smarty.post.qqa_1003}" />
                <input type="hidden" name="qqa_1004" value="{$smarty.post.qqa_1004}" />
                <input type="hidden" name="qqa_1005" value="{$smarty.post.qqa_1005}" />
                <input type="hidden" name="qqa_1006" value="{$smarty.post.qqa_1006}" />
                <input type="hidden" name="qqa_1007" value="{$smarty.post.qqa_1007}" />
                <input type="hidden" name="qqa_1008" value="{$smarty.post.qqa_1008}" />
                {*<input type="hidden" name="qqa_1008_1" value="{$smarty.post.qqa_1008_1}" />
                <input type="hidden" name="qqa_1008_2" value="{$smarty.post.qqa_1008_2}" />
                <input type="hidden" name="qqa_1008_3" value="{$smarty.post.qqa_1008_3}" />
                <input type="hidden" name="qqa_1008_4" value="{$smarty.post.qqa_1008_4}" />
                <input type="hidden" name="qqa_1008_5" value="{$smarty.post.qqa_1008_5}" />
                <input type="hidden" name="qqa_1008_6" value="{$smarty.post.qqa_1008_6}" />
                <input type="hidden" name="qqa_1008_7" value="{$smarty.post.qqa_1008_7}" />
                <input type="hidden" name="qqa_1008_8" value="{$smarty.post.qqa_1008_8}" />
                <input type="hidden" name="qqa_1008_9" value="{$smarty.post.qqa_1008_9}" />
                <input type="hidden" name="qqa_1008_10" value="{$smarty.post.qqa_1008_10}" />
                <input type="hidden" name="qqa_1008_11" value="{$smarty.post.qqa_1008_11}" />
                <input type="hidden" name="qqa_1008_12" value="{$smarty.post.qqa_1008_12}" />*}
                <input type="hidden" name="qqa_1009_1" value="{$smarty.post.qqa_1009_1}" />
                <input type="hidden" name="qqa_1009_2" value="{$smarty.post.qqa_1009_2}" />
                <input type="hidden" name="qqa_1009_3" value="{$smarty.post.qqa_1009_3}" />
                <input type="hidden" name="qqa_1009_4" value="{$smarty.post.qqa_1009_4}" />
                <input type="hidden" name="qqa_1009_5" value="{$smarty.post.qqa_1009_5}" />
                <input type="hidden" name="qqa_1009_6" value="{$smarty.post.qqa_1009_6}" />
                <input type="hidden" name="qqa_1009_7" value="{$smarty.post.qqa_1009_7}" />
                <input type="hidden" name="qqa_1009_8" value="{$smarty.post.qqa_1009_8}" />
                <input type="hidden" name="qqa_1009_9" value="{$smarty.post.qqa_1009_9}" />
                <input type="hidden" name="qqa_1009_10" value="{$smarty.post.qqa_1009_10}" />
                <input type="hidden" name="qqa_1009_11" value="{$smarty.post.qqa_1009_11}" />
                <input type="hidden" name="qqa_1009_12" value="{$smarty.post.qqa_1009_12}" />
                {*<input type="hidden" name="qqa_1009_13" value="{$smarty.post.qqa_1009_13}" />
                <input type="hidden" name="qqa_1009_14" value="{$smarty.post.qqa_1009_14}" />
                <input type="hidden" name="qqa_1009_15" value="{$smarty.post.qqa_1009_15}" />
                <input type="hidden" name="qqa_1009_16" value="{$smarty.post.qqa_1009_16}" />*}
                <input type="hidden" name="qqa_1010" value="{$smarty.post.qqa_1010}" />
                <input type="hidden" name="qqa_1011" value="{$smarty.post.qqa_1011}" />
                <input type="hidden" name="qqa_1012" value="{$smarty.post.qqa_1012}" />

                <input type="hidden" name="ref_by" value="{if isset($smarty.post.ref_by)}{$smarty.post.ref_by|escape:'htmlall':'UTF-8'}{/if}" />
                <input type="submit" name="submitAccount" id="submitAccount" class="buttonlarge submitButton" style="background:#da000c; color:white;font-weight: normal" value="{l s='EMAİL GÖNDER'}" />

                </div>
                {$HOOK_CREATE_ACCOUNT_FORM}

            </form>
        </div>
    </div>
                {/if}

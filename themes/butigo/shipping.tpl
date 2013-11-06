 {if isset($two_page_checkout) && $two_page_checkout == 2}
    <script src="{$js_dir}addressFormValidation.js" type="text/javascript"></script>
    <script type="text/javascript">
        var aliasEmpty = "{l s='Alias Is Epmty' js=1}";
        var aliasIncorrect = "{l s='Alias Incorrect' js=1}";

        var firstnameEmpty = "{l s='firstname Epmty' js=1}";
        var firstnameIncorrect = "{l s='firstname Incorrect' js=1}";

        var lastnameEmpty = "{l s='lastname Epmty' js=1}";
        var lastnameIncorrect = "{l s='lastnameIncorrect' js=1}";

        var address1Error = "{l s='Address Is Empty or is too long' js=1}";
        var address2Error = "{l s='Address Is Incorrect' js=1}";

        var stateEmpty = "{l s='Please Select State' js=1}";

        var cityIncorrect = "{l s='City Incorrect' js=1}";

        var phoneEmpty = "{l s='Phone Epmty' js=1}";
        var phoneIncorrect = "{l s='Phone Incorrect' js=1}";

        {literal}
            $('.pressEnter').keypress(function(event) {
                if(event.keyCode == 13)
                {
                    event.preventDefault();
                }
           });
        {/literal}

    </script>
 {/if}
{* Will be deleted for 1.5 version and more *}
{* If ordered_adr_fields doesn't exist, it's a PrestaShop older than 1.4.2 *}
{if !isset($ordered_adr_fields)}
	{if isset($address)}
		{counter start=0 skip=1 assign=address_key_number}
		{foreach from=$address key=address_key item=address_value}
			{$ordered_adr_fields.$address_key_number = $address_key}
			{counter}
		{/foreach}
	{else}
		{$ordered_adr_fields.0 = 'company'}
		{$ordered_adr_fields.1 = 'firstname'}
		{$ordered_adr_fields.2 = 'lastname'}
		{$ordered_adr_fields.3 = 'address1'}
		{$ordered_adr_fields.4 = 'address2'}
		{$ordered_adr_fields.5 = 'postcode'}
		{$ordered_adr_fields.6 = 'city'}
		{$ordered_adr_fields.7 = 'country'}
		{$ordered_adr_fields.8 = 'state'}
	{/if}
{/if}

<script type="text/javascript">
/* <![CDATA[ */
	var baseDir = '{$base_dir_ssl}';
/* ]]> */
</script>
{if !isset($two_page_checkout)}
<script type="text/javascript">
/* <![CDATA[ */
idSelectedCountry = {if isset($smarty.post.id_state)}{$smarty.post.id_state|intval}{else}{if isset($address->id_state)}{$address->id_state|intval}{else}false{/if}{/if};
idSelectedState= {if isset($smarty.post.id_province)}{$smarty.post.id_province|intval}{elseif isset($address->id_province)}{$address->id_province|intval}{else}false{/if};
countries = new Array();
states = new Array();
countriesNeedIDNumber = new Array();
countriesNeedZipCode = new Array();
{foreach from=$countries item='country'}
	{if isset($country.states) && $country.contains_states}
		countries[{$country.id_country|intval}] = new Array();
		{foreach from=$country.states item='state' name='states'}
			countries[{$country.id_country|intval}].push({ldelim}'id' : '{$state.id_state}', 'name' : '{$state.name|escape:'htmlall':'UTF-8'}'{rdelim});
		{/foreach}
	{/if}
	{if $country.need_identification_number}
		countriesNeedIDNumber.push({$country.id_country|intval});
	{/if}
	{if isset($country.need_zip_code)}
		countriesNeedZipCode[{$country.id_country|intval}] = {$country.need_zip_code};
	{/if}
{/foreach}

{*{foreach from=$states item='state'}
	{if isset($state.provinces)}
		states[{$state.id_state|intval}] = new Array();
		{foreach from=$state.provinces item='province' name='provinces'}
			states[{$state.id_state|intval}].push({ldelim}'id' : '{$province.id_province}', 'name' : '{$province.name|escape:'htmlall':'UTF-8'}'{rdelim});
		{/foreach}
	{/if}
{/foreach}*}

$(function(){ldelim}
	$('.id_state option[value={if isset($smarty.post.id_state)}{$smarty.post.id_state}{else}{if isset($address->id_state)}{$address->id_state|escape:'htmlall':'UTF-8'}{/if}{/if}]').attr('selected', 'selected');
	$('.province option[value={if isset($smarty.post.id_province)}{$smarty.post.id_province}{else} {if isset($address->id_province)}{$address->id_province|escape:'htmlall':'UTF-8'}{/if}{/if}]').attr('selected', 'selected');
{rdelim});
/*]]>*/
</script>
{/if}

{*the page displayed when user checks out to add address and personal information*}
{if !isset($two_page_checkout)}
    {assign var='current_step' value='address'}
    {include file="$tpl_dir./order-steps.tpl"}
{/if}
<div {if !isset($two_page_checkout)}class="shopping-cart" {else} class="new_addrss_layout"{/if}>
	<h3 class = "left"><img src="{$img_dir}cart/shipping_adress_title.gif" alt="{l s='Shipping Address'}"   width="133" height="16" /></h3>
	<div id="shipping-billing-container">
		{*<div id="note">
			<p style="font-size:1.1em">
				<span style="font-weight:bold; color:#ac1f61; font-size:1.1em">{l s='A Note from Butigo:'}</span>
				{l s=' With your first purchase, you become an official member of ShoeDazzle and will enjoy all the benefits and perks of membership!'}
			</p>
		</div>*}
		{*include file="$tpl_dir./errors.tpl"*}
		<form action="{$link->getPageLink('shipping.php',true)}" method="post" class="std">
			<input type="hidden" name="token" value="{$token}" />
			{*
			<div class="formContainer">
				<h3>{l s='Billing Address'}</h3>
				<div id="address-options" class="clearAfter">
					<fieldset>
						<input type="radio" name="use_shipping" id="use_shipping" value="true" checked="checked"/>
						<label for="billing_same" class="auto_width">{l s='Same as Shipping Address'}</label>
						<input type="radio" id="use_billing" name="use_shipping" value="false"/>
						<label for="billing_different" class="auto_width">{l s='Different Billing Address'}</label>
					</fieldset>
				</div>
				<div id="billing-address-form" style="display:none; float:right; margin:15px 195px 0;">
					<a href="{$base_dir_ssl}address.php?back=shipping.php&amp;select_address=1{if $back}&mod={$back}{/if}" title="{l s='Add a new address'}" class="buttons">{l s='Add a new address'}</a>
				</div>
			</div>
			*}
			<div class="formContainer" id="shipping-address">

				{*<span style="float:left; color:#ff54a4; font-size:2em; font-family:Museo100Reg; margin:8px 0 0;">{l s='Free Shipping'}</span>
				<em style="float:left; font-weight:bold;"></em>
				<img src="{$img_dir}shipping-car.gif" alt="{l s='Free Shipping'}"/>*}
				<fieldset>
					<p id="adress_alias" class="alias pressEnter">
						<label for="alias">{l s='Address Nickname'}ddd<em>*</em></label>
						<input type="text" id="alias" name="alias" value="{if isset($smarty.post.alias)}{$smarty.post.alias}{elseif $address->alias}{$address->alias|escape:'htmlall':'UTF-8'}{elseif isset($select_address)}{else}{l s='My address'}{/if}" />
					</p>
					{assign var="stateExist" value="false"}
					{foreach from=$ordered_adr_fields item=field_name}
						{if $field_name eq 'firstname'}
						<p class="firstname pressEnter">
							<label for="firstname">{l s='First name'}<em>*</em></label>
							<input type="text" name="firstname" id="firstname" value="{if isset($smarty.post.firstname)}{$smarty.post.firstname}{else}{$address->firstname|escape:'htmlall':'UTF-8'}{/if}" />
						</p>
						{/if}
						{if $field_name eq 'lastname'}
						<p class="lastname pressEnter">
							<label for="lastname">{l s='Last name'}<em>*</em></label>
							<input type="text" id="lastname" name="lastname" value="{if isset($smarty.post.lastname)}{$smarty.post.lastname}{else}{$address->lastname|escape:'htmlall':'UTF-8'}{/if}" />
						</p>
						{/if}
						{if $field_name eq 'address1'}
						<p class="address1">
							<label for="address1">{l s='Address'}<em>*</em></label>
							<textarea name="address1" id="address1" rows="3" cols="25">{if isset($smarty.post.address1)}{$smarty.post.address1}{else}{$address->address1|escape:'htmlall':'UTF-8'}{/if}</textarea>
							{*<input type="text" id="address1" name="address1" value="{if isset($smarty.post.address1)}{$smarty.post.address1}{else}{$address->address1|escape:'htmlall':'UTF-8'}{/if}" />*}
						</p>
						{/if}
						{*{if $field_name eq 'address2'}
						<p>
							<label for="address2">{l s='Address (2)'}</label>
							<input type="text" id="address2" name="address2" value="{if isset($smarty.post.address2)}{$smarty.post.address2}{else}{$address->address2|escape:'htmlall':'UTF-8'}{/if}" />
						</p>
						{/if}*}
						{if $field_name eq 'Country:name' || $field_name eq 'country'}
						<p class="hidden">
							<label for="id_country">{l s='Country'}<em>*</em></label>
							<select id="id_country" name="id_country">{$countries_list}</select>
						</p>
						{/if}
						{if $field_name eq 'State:name' || $field_name eq 'state'}
						{assign var="stateExist" value="true"}
						<p class="select"> {*WARNING using state as city*}
							<label for="id_state">{l s='City'}<em>*</em></label>
							<select name="id_state" id="id_state">
								<option value="">-</option>
							</select>
						</p>
						{/if}
					{/foreach}
					{if $stateExist eq "false"}
                                           {if isset($two_page_checkout) && $two_page_checkout == 2}
                                                <p class="select id_state"> {*WARNING using state as city*}
                                                        <label for="id_state">{l s='City'}<em></em></label>
                                                        <select name="id_state" id="id_state">
                                                                {$statesList}
                                                        </select>
                                                </p>
                                          {else}
						<p class=" select"> {*WARNING using state as city*}
							<label for="id_state">{l s='City'}<em>*</em></label>
							<select name="id_state" id="id_state">
								<option value="">-</option>
							</select>
						</p>
                                            {/if}
                                        {/if}
					{*<p class="province select">
						<label for="id_province">{l s='Province'}<em></em></label>
						<select name="id_province" id="id_province">
								<option value="">-</option>
						</select>
				   </p>*}
					<p class="bottom_zero city pressEnter"> {*WARNING using city as sub-province*}
						<label for="city">{l s='Province'}<em></em></label>
						<input type="text" name="city" id="city" value="{if isset($smarty.post.city)}{$smarty.post.city}{else}{$address->provinceName|escape:'htmlall':'UTF-8'}{/if}" maxlength="64" />
					</p>
					{*<p>
						<label for="postcode">{l s='Zip/Postal code'}</label>
						<input type="text" id="postcode" name="postcode" value="{if isset($smarty.post.postcode)}{$smarty.post.postcode}{else}{$address->postcode|escape:'htmlall':'UTF-8'}{/if}" />
					</p>*}
                                <p class="phone pressEnter">
					<label for="phone">{l s='Phone Number'} <em>*</em><span class="help_text"><br/>{l s='Eg:5551234567'}</span></label>
                                         <select name="regioncode" id="regioncode">
                                        {foreach from=$phnum_regioncode item=regionCode}
                                                        <option value="{$regionCode}">{$regionCode}</option>
                                         {/foreach}  </select>
					<input type="text" id="phone" name="phone" value="{if isset($smarty.post.phone)}{$smarty.post.phone}{else}{if isset($address->phone)}{$address->phone|escape:'htmlall':'UTF-8'}{/if}{/if}" maxlength="7"/>
				</p>
					{*<p>
						<label for="phone_mobile">{l s='Mobile phone'}</label>
						<input type="text" id="phone_mobile" name="phone_mobile" value="{if isset($smarty.post.phone_mobile)}{$smarty.post.phone_mobile}{else}{$address->phone_mobile|escape:'htmlall':'UTF-8'}{/if}" />
					</p>*}
				</fieldset>
				{*	<em style="float:left; width:100%; margin:5px 0;">{l s='Within the contiguous United States.. Excluded APO/FPO addresses.'}</em>
				<img src="{$img_dir}guarantee.png" width="103px" height="103px" alt="{l s='100% Satisfaction Guarantee'}"/>*}
			</div>
           <div class="cart_adress">
				{*<input type="checkbox" value="true" name="opt_in_tos" class="opt_in_tos"/>
				<label for="user_opt_in_tos" class="no_bold"><em></em>{l s='I have read and agree to the'} <a class="no_float no_margin" href="javascript:openTermsConditions();">{l s='terms and conditions'}</a>{l s=' and'}
				<a class="no_float no_margin" href="javascript:openPrivacyPolicy();">{l s='privacy policy'}</a>.</label>*}
				{if isset($id_address)}<input type="hidden" name="id_address" value="{$id_address|intval}" />{/if}
				{if isset($back)}<input type="hidden" name="back" value="{$back}" />{/if}
				{if isset($utm_params)}<input type="hidden" class="hidden" name="utm_params" value="{$utm_params}" />{/if}
				{if isset($mod)}<input type="hidden" name="mod" value="{$mod}" />{/if}
				{if isset($select_address)}<input type="hidden" name="select_address" value="{$select_address|intval}" />{/if}
				{if isset($carriers)}
					<input type="hidden" name="id_carrier" value="{$checked|intval}" id="id_carrier{$checked|intval}" />
				{/if}
                                {if isset($two_page_checkout) && $two_page_checkout == 2}
                                    <input type="submit" name="submitInfo" id="submitAddress" value=""  class="submit_address hidden"/>
                                {else}
                                    <input type="submit" name="submitInfo" id="submitInfo" value=""  class="submit_ address"/>
                                    <input type="submit" name="submitInfo" id="submitInfo2" value=""  class="submit_address2" style="display:none;"/>
                                {/if}
		   </div>
		</form>
                {if isset($two_page_checkout) && $two_page_checkout == 2}
                    <input type="submit" name="submitInfo" id="submitInfo"value="{l s='submit adres'}" class="buttonmedium blue" onclick="addressValidation();"/>
                {/if}
	</div>
    <hr/>
    {if !isset($two_page_checkout)}
	{include file="$tpl_dir./cart_bottom_footer.tpl"}
    {/if}
</div>

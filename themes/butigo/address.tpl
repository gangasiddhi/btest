<link rel="stylesheet" href="{$css_dir}address.css" type="text/css"></link>
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
    var provinceEmpty = "{l s='Please Select Province' js=1}";

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
		{$ordered_adr_fields.6 = 'country'}
		{$ordered_adr_fields.7 = 'state'}
        {$ordered_adr_fields.8 = 'province'}
	{/if}
{/if}

<script type="text/javascript">
/* <![CDATA[ */
	var baseDir = '{$base_dir_ssl}';

    {literal}
        function updateProvince()
        {
            $('select#id_province option:not(:first-child)').remove();
            var provinces = states[$('select#id_state').val()];
            if(typeof(provinces) != 'undefined')
            {
                $(provinces).each(function (key, item){
                    $('select#id_province').append('<option value="'+item.id+'"'+ (idSelectedState == item.id ? ' selected="selected' : '') + '">'+item.name+'</option>');
                });

                $('p.province:hidden').slideDown('slow');
            }
            else
                $('p.province').slideUp('fast');
        }

        $('#id_state').on('change', function(){
            if ($(this).children('option:first').is(':selected')) {
                $(this).val($(this).children('option:first').val());
                $('p.province').slideUp('slow');
                return;
            } else {
                $('p.province').slideDown('slow');
            }
            updateProvince();
        });


        $('#submitAddressForm').on('click', function(e){
            var isValidForm = addressValidation(false);

            var $email = $("#login_email");
            var $password = $("#login_password");

            var $emailContainer = $(".emailField");
            var $passwordContainer = $(".passwordField");

            var $firstname = $("#firstname");
            var $lastname = $("#lastname");


            $email.isValid = function () {
                var email = $email.val();
                var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
                return re.test(email);
            };

            $password.isValid = function () {
                var password = $password.val();
                return (password.length > 5);
            };


            var afterValidation = function () {
                if (!isValidForm) {
                    e.preventDefault();
                } else {
                    $("#submitAddress").trigger('click'); 
                }
            };


            var register = function (callback) {
                var firstname = $("#firstname").val();
                var lastname = $("#lastname").val();

                $emailContainer.find(".add_error").remove();
                if (!$email.isValid()) {
                    $("<span>").addClass("add_error").html("Lütfen geçerli bir e-posta adresi girin").appendTo($emailContainer);
                    return false;
                }

                if (firstname && lastname) {
                    var password = Math.random().toString(36).substring(2);

                    var data = {
                        customer_name : firstname + " " + lastname,
                        email : $("#login_email").val(),
                        passwd: password,
                        passwd_confirm: password,
                        submitAccount: 'true',
                        withAjax: true
                    };


                    if (!isValidForm) {
                        return true;
                    }

                    $.post("/uyelik-giris", data, function (r) {
                        if (r.errors && r.emailExists) {
                            $emailContainer.find(".add_error").remove();
                            $("<span>").addClass("add_error").html("Bu e-posta adresi kayıtlı, lütfen parolanızı girin").appendTo($emailContainer);
                            $passwordContainer.removeClass("hidden");
                        } else if (!r.errors) {
                            afterValidation();
                        }
                    }, 'json');
                }
            };

   
            var login = function () {
                $emailContainer.find(".add_error").remove();
                if (!$email.isValid()) {
                    $("<span>").addClass("add_error").html("Lütfen geçerli bir e-posta adresi girin").appendTo($emailContainer);
                    return false;
                }

                $passwordContainer.find(".add_error").remove();
                if (!$password.isValid()) {
                    $("<span>").addClass("add_error").html("Parola en az 6 karakterden oluşmalıdır").appendTo($passwordContainer);
                    return false;
                }


                var data = {
                    email : $("#login_email").val(),
                    passwd: $("#login_password").val(),
                    SubmitLogin: 'true',
                    withAjax: true
                };



                $.post("/giris", data, function (r) {
                    if (r.errors) {
                        $passwordContainer.find(".add_error").remove();
                        $("<span>").addClass("add_error").html("Parola geçersiz").appendTo($passwordContainer);
                    } else if (!r.errors) {
                        afterValidation();
                    }
                }, 'json');

            };


            if ($("#login_email").is(":visible") && !$("#login_password").is(":visible")) {
                register();
            } else if ($("#login_email").is(":visible") && $("#login_password").is(":visible")) {
                login();
            } else {
		afterValidation();
	    }

	    

        });

	var prevEmailVal = "";

	$("#login_email").blur(function (e) {
		var val = $(this).val();

		if (val != prevEmailVal) {
			$(".passwordField").addClass("hidden").find(".add_error").remove();
			$("#login_password").val('');
		}

		prevEmailVal = val;
	});
    {/literal}

    {if !isset($two_page_checkout)}
        {literal}
            $('#submitAddress').on('click', function(e) {
                var isValidForm = addressValidation(false);
                if (!isValidForm) {
                    e.preventDefault();
                }
            });
        {/literal}

    {/if}
        
    {if isset($two_page_chkout_no_add) && $two_page_chkout_no_add== 1}
        {literal}
            $('#address-checkout').on('click', function(e) {
                var isValidForm = addressValidation(true);
                if (!isValidForm) {
                    e.preventDefault();
                }
            });
        {/literal}

    {/if}
/* ]]> */
</script>

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

{foreach from=$states item='state'}
	{if isset($state.provinces)}
		states[{$state.id_state|intval}] = new Array();
		{foreach from=$state.provinces item='province' name='provinces'}
			states[{$state.id_state|intval}].push({ldelim}'id' : '{$province.id_province}', 'name' : '{$province.name|escape:'htmlall':'UTF-8'}'{rdelim});
		{/foreach}
	{/if}
{/foreach}

$(function(){ldelim}
	$('.id_state option[value={if isset($smarty.post.id_state)}{$smarty.post.id_state}{else}{if isset($address->id_state)}{$address->id_state|escape:'htmlall':'UTF-8'}{/if}{/if}]').attr('selected', 'selected');
	$('.province option[value={if isset($smarty.post.id_province)}{$smarty.post.id_province}{else} {if isset($address->id_province)}{$address->id_province|escape:'htmlall':'UTF-8'}{/if}{/if}]').attr('selected', 'selected');
{rdelim});
/* ]]> */
</script>


{if !isset($two_page_checkout)}
<div id="container">
{/if}
	<div class="edit_address {if isset($two_page_chkout_no_add) && $two_page_chkout_no_add== 1} newaddalgn new_chkout_label{/if}"  {if isset($two_page_checkout) && $two_page_checkout == 2}id="two_chk_out_edit_adres"{/if} >
		{*include file="$tpl_dir./errors.tpl"*}



                    <form action="{$link->getPageLink('address.php', true)}" method="post" class="std">
			<fieldset>
                <p class="new-address-form-title" {if isset($two_page_chkout_no_add) && $two_page_chkout_no_add== 1} style="padding: 0 0 0 67px;" {/if}>{if isset($id_address)}{l s='Your address'}{else}{l s='New address'}{/if}</p>
                                {if !isset($two_page_checkout)}
				<p class="required text dni">
					<label for="dni">{l s='Identification number'}</label>
					<input type="text" class="text" name="dni" id="dni" value="{if isset($smarty.post.dni)}{$smarty.post.dni}{else}{if isset($address->dni)}{$address->dni|escape:'htmlall':'UTF-8'}{/if}{/if}" />
					<span class="form_info">{l s='DNI / NIF / NIE'}</span>
					<sup>*</sup>
				</p>
                                {/if}
				{*{if $vat_display == 2}
					<div id="vat_area">
				{elseif $vat_display == 1}
					<div id="vat_area" style="display: none;">
				{else}
					<div style="display: none;">
				{/if}
					<div id="vat_number">
						<p class="text">
							<label for="vat_number">{l s='VAT number'}</label>
							<input type="text" class="text" name="vat_number" value="{if isset($smarty.post.vat_number)}{$smarty.post.vat_number}{else}{if isset($address->vat_number)}{$address->vat_number|escape:'htmlall':'UTF-8'}{/if}{/if}" />
						</p>
					</div>
				</div>*}
                {$address ->alias}
				<p id="address_alias" class="alias pressEnter">
					<input type="hidden" name="token" value="{$token}" />
					<label for="alias" class="new-form-label">{l s='Address Nickname'}<em>*</em></label>
					<input type="text" id="alias" name="alias" value="{if isset($smarty.post.alias)}{$smarty.post.alias}{else}{if isset($address->alias)}{$address->alias|escape:'htmlall':'UTF-8'}{/if}{if isset($select_address)}{else}{l s='My address'}{/if}{/if}" />
				</p>
				{foreach from=$ordered_adr_fields item=field_name}
					{*if $field_name eq 'company'}
						<p class="text">
						<input type="hidden" name="token" value="{$token}" />
						<label for="company">{l s='Company'}</label>
						<input type="text" id="company" name="company" value="{if isset($smarty.post.company)}{$smarty.post.company}{else}{if isset($address->company)}{$address->company|escape:'htmlall':'UTF-8'}{/if}{/if}" />
					</p>
					{/if*}
					{if $field_name eq 'firstname'}
					<p class="firstname pressEnter">
						<label for="firstname" class="new-form-label">{l s='First name'}<em>*</em></label>
						<input type="text" name="firstname" id="firstname" value="{if isset($smarty.post.firstname)}{$smarty.post.firstname}{else}{if isset($address->firstname)}{$address->firstname|escape:'htmlall':'UTF-8'}{/if}{/if}" />
					</p>
					{/if}
					{if $field_name eq 'lastname'}
					<p class="lastname pressEnter">
						<label for="lastname" class="new-form-label">{l s='Last name'}<em>*</em></label>
						<input type="text" id="lastname" name="lastname" value="{if isset($smarty.post.lastname)}{$smarty.post.lastname}{else}{if isset($address->lastname)}{$address->lastname|escape:'htmlall':'UTF-8'}{/if}{/if}" />
					</p>

                        {if $loginAfterBasketDisabled && !$_userLogged}
                            <p class="emailField pressEnter">
                                <label for="login_email" class="new-form-label">{l s='E mail'}<em>*</em></label>
                                <input type="text" name="login[email]" id="login_email" value="{if isset($smarty.post.login.email)}{$smarty.post.login.email}{/if}" />
                             </p>

                            <p class="passwordField hidden">
                                <label for="login_password" class="new-form-label">{l s='Password'}<em>*</em></label>
                                <input type="password" name="login[password]" id="login_password" value="{if isset($smarty.post.login.password)}{$smarty.post.login.password}{/if}" />
                            </p>
                        {/if}

					{/if}










					{if $field_name eq 'address1'}
					<p class="address1">
						<label for="address1" class="new-form-label">{l s='Address'}<em>*</em></label>
						<textarea name="address1" id="address1" rows="3" cols="25">{if isset($smarty.post.address1)}{$smarty.post.address1}{else}{$address->address1|escape:'htmlall':'UTF-8'}{/if}</textarea>
						{*<input type="text" id="address1" name="address1" value="{if isset($smarty.post.address1)}{$smarty.post.address1}{else}{if isset($address->address1)}{$address->address1|escape:'htmlall':'UTF-8'}{/if}{/if}" />*}
					</p>
					{/if}
					{*if $field_name eq 'address2'}
					<p>
						<label for="address2">{l s='Address (Line 2)'}</label>
						<input type="text" id="address2" name="address2" value="{if isset($smarty.post.address2)}{$smarty.post.address2}{else}{if isset($address->address2)}{$address->address2|escape:'htmlall':'UTF-8'}{/if}{/if}" />
					</p>
					{/if*}
					{*if $field_name eq 'postcode'}
					<p class="required postcode text">
						<label for="postcode">{l s='Zip / Postal Code'}</label>
						<input type="text" id="postcode" name="postcode" value="{if isset($smarty.post.postcode)}{$smarty.post.postcode}{else}{if isset($address->postcode)}{$address->postcode|escape:'htmlall':'UTF-8'}{/if}{/if}" onkeyup="$('#postcode').val($('#postcode').val().toUpperCase());" />
					</p>
					{/if*}
					{if $field_name eq 'Country:name' || $field_name eq 'country'}
					<p class="hidden">
						<label for="id_country" class="new-form-label">{l s='Country'}</label>
						<select id="id_country" name="id_country">{$countries_list}</select>
					</p>
					{/if}
					{*if isset($vatnumber_ajax_call) && $vatnumber_ajax_call}
					<script type="text/javascript">
					var ajaxurl = '{$ajaxurl}';
					{literal}
						$(document).ready(function(){
							$('#id_country').change(function() {
								$.ajax({
									type: "GET",
									url: ajaxurl+"vatnumber/ajax.php?id_country="+$('#id_country').val(),
									success: function(isApplicable){
										if(isApplicable == "1")
										{
											$('#vat_area').show();
											$('#vat_number').show();
										}
										else
										{
											$('#vat_area').hide();
										}
									}
								});
							});
						});
					{/literal}
					</script>
					{/if*}
					{if $field_name eq 'State:name' || $field_name eq 'state'}
					{assign var="stateExist" value="true"}
    					<p class="state select"> {*WARNING using state as city*}
                            <label for="id_state" class="new-form-label">{l s='City'}<em>*</em></label>
                            <select name="id_state" id="id_state">
                                {$statesList}
                            </select>
                        </p>
					{/if}


                    {if $field_name eq 'Province:name' || $field_name eq 'province'}
                        {assign var="provinceExist" value="true"}
                        {*S--Province*}
                            <p class="province select" style="{if !$address->id_state}display:none;{/if}">
                                <label for="id_province" class="new-form-label">{l s='Province'}<em>*</em></label>
                                <select name="id_province" id="id_province" class="province-dropdown">
                                    <option value="">-</option>
                                    {foreach from=$states item=state}
                                        {if $state.id_state == $address->id_state}
                                            {foreach from=$state.provinces item=province}
                                                <option value="{$province.id_province}" {if $province.id_province == $address->id_province} selected {/if}>{$province.name}</option>
                                            {/foreach}
                                            {break}
                                        {/if}
                                    {/foreach}
                                </select>
                            </p>
                        {*F--Province*}
                    {/if}
				{/foreach}
				{if !$stateExist}
                    <p class="state select"> {*WARNING using state as city*}
                        <label for="id_state" class="new-form-label">{l s='City'}<em>*</em></label>
                        <select name="id_state" id="id_state">
                          <option value="">-</option>
                            {foreach from=$states item=state}
                                <option value="{$state.id_state}" {if $state.id_state == $address->id_state} selected {/if}> {$state.name}</option>
                            {/foreach}
                        </select>
                    </p>
				{/if}
                {*S--Province*}
                    {if !$provinceExist}
                        <p class="province select" style="{if !$address->id_state}display:none;{/if}">
        					<label for="id_province" class="new-form-label">{l s='Province'}<em>*</em></label>
        					<select name="id_province" id="id_province" class="province-dropdown">
    							<option value="">-</option>
                                {foreach from=$states item=state}
                                    {if $state.id_state == $address->id_state}
                                        {foreach from=$state.provinces item=province}
                                            <option value="{$province.id_province}" {if $province.id_province == $address->id_province} selected {/if}>{$province.name}</option>
                                        {/foreach}
                                        {break}
                                    {/if}
                                {/foreach}
        					</select>
        				</p>
                    {/if}
                {*F--Province*}

				{*<p class="textarea">
					<label for="other">{l s='Additional information'}</label>
					<textarea id="other" name="other" cols="26" rows="3">{if isset($smarty.post.other)}{$smarty.post.other}{else}{if isset($address->other)}{$address->other|escape:'htmlall':'UTF-8'}{/if}{/if}</textarea>
				</p>*}

				{*<p style="margin-left:50px;">{l s='You must register at least one phone number'}<sup style="color:red;"></sup></p>*}
				<p class="phone pressEnter" style="margin-bottom: 5px">
					<label for="phone" class="new-form-label">{l s='Phone Number'} <em>*</em><span class="help_text"><br/>{l s='Eg:5551234567'}</span></label>
					{* region codes {$phnum_regioncode} *}
					<span style="font-weight:normal;">0</span>
					<input name ="regioncode" id="regioncode" style="width:28px" value="{$regioncode}" maxlength="3">
					<input type="text" id="phone" name="phone" value="{if isset($smarty.post.phone)}{$smarty.post.phone}{else}{if isset($address->phone)}{$phoneNumberWTRegion|escape:'htmlall':'UTF-8'}{/if}{/if}" maxlength="7"/>
				</p>

			</fieldset>
                
            {if !isset($two_page_chkout_no_add) && $two_page_chkout_no_add!= 1} 
                <hr/> 
            {else}
               {* <div id="same_as_delivery" style="width: 366px;white-space: nowrap;">
                    <input type="checkbox" name="same" class="two_chk_out_edit_label" id="addressesAreEquals" value="1" {if $cart->id_address_invoice == $cart->id_address_delivery || $addresses|@count == 1}checked="checked"{/if} />
                    <label id="addressSame" for="addressesAreEquals">{l s='Use same address as delivery address'}</label>
                </div> *}           
            {/if}
			

			<p class="edit_adress_buttons {if isset($two_page_checkout) && $two_page_checkout == 2}hidden{/if}">
				{if isset($id_address)}<input type="hidden" name="id_address" value="{$id_address|intval}" />{/if}
                                {if isset($two_page_checkout) && $two_page_checkout==2}<input type="hidden" name="backchkout2" value="order.php?step=2"/>{/if}
				{if isset($back)}<input type="hidden" name="back" value="{$back}" />{/if}
				{if isset($utm_params)}<input type="hidden" class="hidden" name="utm_params" value="{$utm_params}" />{/if}
				{if isset($mod)}<input type="hidden" name="mod" value="{$mod}" />{/if}
				{if isset($select_address)}<input type="hidden" name="select_address" value="{$select_address|intval}" />{/if}
				<input type="submit" name="submitAddress" id="submitAddress"  value="{l s='submit adress'}" class="buttonmedium blue" style="margin: 0 20px 0 0;padding: 0.5em 1em 0.7em";/>
				<input type="submit" name="cancelAddress" id="cancelAddress" value="{l s='cancel adress'}" class="buttonmedium pink" style="margin: 0 20px 0 0; padding: 0.5em 1em 0.7em"; />
				<br class="clear"/>
			</p>
            {if !isset($two_page_checkout)}
                <p style ="float: left; margin:0px">
                </p>
            {/if}
		</form>
        {if isset($two_page_checkout) && $two_page_checkout == 2}
           
             <div class="two_page_chk_addres_out_buttons">
                <input type="submit" name="submitcancelAddress" id="submitcancelAddress" onclick="closeAddressPopUp();" value="{l s='cancel adress'}" class="buttonmedium blue" {if isset($two_page_chkout_no_add) && $two_page_chkout_no_add== 1} style="display:none;" {/if}/>
                <input type="submit" name="submitAddressForm" id="submitAddressForm" value="{l s='submit adress'}" class="buttonmedium pink" {if isset($two_page_chkout_no_add) && $two_page_chkout_no_add== 1} style="margin: 0 0 0 202px;padding: 0.8em 5.5em 0.75em;"{else} style="margin: 0 0 0 14px;{/if}"/>
                <p style="margin: 0">
                    <div>
                    <strong style="color: #000; font-size: 11px; font-weight: bold;">
                    <em class="required" {if isset($two_page_chkout_no_add) && $two_page_chkout_no_add== 1} style="margin: 0 0 0 202px;"{/if}>*</em>{l s='Required field'}</strong>
                    </div>
                    <div style="color: #000; font-size: 11px">
                    <em class="required">*</em>Girdiğiniz e-posta adresi ile kullanıcı sözleşmesini kabul ederek Butigo'da üyelik oluşturmuş olacaksınız. Kullanıcı sözleşmesine sitemizden ulaşabilirsiniz
                    </div>
                </p>
             </div>
         {/if}
        </div>{* end of edit_address*}
{if !isset($two_page_checkout)}
</div>{* end of container*}
{/if}
{if !isset($two_page_checkout)}
    {include file="$tpl_dir./my-account-sidebar.tpl"}
{/if}

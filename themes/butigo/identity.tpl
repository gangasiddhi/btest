<script type="text/javascript">
	var baseDir = '{$base_dir_ssl}';
	var errCitizenId = "{l s='You have entered a wrong citizen id. Please correct and try again.'}";
	var errBirthday = "{l s='You have selected an invalid date. Please correct and try again.'}";

	{literal}
	$(document).on('submit', '.std', function(e) {
		var bday = $('.birthDate').val();
		var bmonth = $('.birthMonth').val();
		var byear = 2000;

		if ($('#citizen_id').val() && ! validateCitizenId($('#citizen_id').val())) {
			alert(errCitizenId);
			return false;
		}

		if (! isValidDate(byear, bmonth, bday)) {
			alert(errBirthday);
			return false;
		}
	});
	{/literal}
</script>

{*include file="$tpl_dir./errors.tpl"*}

{if isset($confirmation) && $confirmation}
	<div class="success message">
		<a id="error-close"></a>
		<div id="successmsg">success</div>
		{l s='Your personal information has been successfully updated.'}
		{if isset($pwd_changed)}<br />{l s='Your password has been sent to your e-mail:'} {$email|escape:'htmlall':'UTF-8'}{/if}
	</div>
	<div id="container">
	<form action="{$link->getPageLink('identity.php', true)}" method="post" class="std">
		<div id ="my_account">
			<h3>{l s=' Membership Information'}</h3>
			<p><em class="required">*</em>{l s='Indicates a required field.'}</p>
			<hr/>
			<fieldset class="medium clearAfter">
				<p>
					<label for="credits">{l s='Credits'}</label>
					<span>{$credits}</span>
				</p>

				<hr/>

				<p style = "margin-bottom:20px">
					<label for="">{l s='My Shoe Size'}</label>
					<select name="shoe_size" id="shoe_size">
					<option selected="selected" value="{$smarty.post.shoe_size}">{$smarty.post.shoe_size}</option>
					<option value="35">35</option>
					<option value="36">36</option>
					<option value="37">37</option>
					<option value="38">38</option>
					<option value="39">39</option>
					<option value="40">40</option>
					<option value="41">41</option>
					<option value="42">42</option>
					</select>
				</p>

				<hr/>

				<p>
					<label for="citizen_id">{l s='Citizen ID'}</label>
					<input type="text" maxlength="11" id="citizen_id" name="citizen_id" value="{if $smarty.post.citizen_id}{$smarty.post.citizen_id}{/if}" />
				</p>

				<p>
					<label for="firstname">{l s='First Name'}<em>*</em></label>
					<input type="text" id="firstname" name="firstname" value="{$smarty.post.firstname}" />
				</p>

				<p>
					<label for="lastname">{l s='Last Name'}<em>*</em></label>
					<input type="text" id="lastname" name="lastname" value="{$smarty.post.lastname}" />
				</p>

				<p class="select">
					<label>{l s='Date of Birth'}</label>

					<select name="birthDate" class="birthDate">
						{foreach from=$days item=day name=days}
							<option {if $birthday.mday == $day} selected{/if}
								value="{if $smarty.foreach.days.index+1 < 10}0{$smarty.foreach.days.index+1}{else}{$smarty.foreach.days.index+1}{/if}">&nbsp;{$day}&nbsp;</option>
						{/foreach}
					</select>

					<select name="birthMonth" class="birthMonth">
						{foreach from=$months item=month name=months}
							<option {if $birthday.mon == $smarty.foreach.months.index+1} selected{/if}
								value="{if $smarty.foreach.months.index+1 < 10}0{$smarty.foreach.months.index+1}{else}{$smarty.foreach.months.index+1}{/if}">&nbsp;{$month}&nbsp;</option>
						{/foreach}
					</select>
				</p>

				<p>
					<label for="email">{l s='Email Address'}<em>*</em></label>
					<input type="text" name="email" id="email" value="{$smarty.post.email}" />
				</p>

				<p>
					<label for="passwd">{l s='New Password'}</label>
					<input type="password" name="passwd" id="passwd" autocomplete="off" autocorrect="off" autocapitalize="off" />
				</p>

				<p>
					<label for="confirmation">{l s='Confirm New Password'}</label>
					<input type="password" name="confirmation" id="confirmation" autocomplete="off" autocorrect="off" autocapitalize="off" />
				</p>

				<hr/>

				<p class="submit">
					<input type="submit" class="buttonmedium  blue" name="submitIdentity" value="{l s='Save'}" />
				</p>
			</fieldset>

		</div>
	</form>
	</div>
{else}
	<div id="container">
	<form action="{$link->getPageLink('identity.php', true)}" method="post" class="std">
		<div id ="my_account">
			<h3>{l s=' Membership Information'}</h3>
			<p><em class="required">*</em>{l s='Indicates a required field.'}</p>

			<hr>

			<fieldset class="medium clearAfter">
				<p>
					<label for="credits">{l s='Credits'}</label>
					<span>{$credits}</span>
				</p>

				<hr>

				<p style = "margin-bottom:20px">
					<label for="">{l s='My Shoe Size'}</label>
					<select name="shoe_size" id="shoe_size">
					<option selected="selected" value="{$smarty.post.shoe_size}">{$smarty.post.shoe_size}</option>
					<option value="35">35</option>
					<option value="36">36</option>
					<option value="37">37</option>
					<option value="38">38</option>
					<option value="39">39</option>
					<option value="40">40</option>
					<option value="41">41</option>
					<option value="42">42</option>
					</select>
				</p>

				<hr>

				<p>
					<label for="citizen_id">{l s='Citizen ID'}</label>
					<input type="text" maxlength="11" id="citizen_id" name="citizen_id" value="{if $smarty.post.citizen_id}{$smarty.post.citizen_id}{/if}" />
				</p>

				<p>
					<label for="firstname">{l s='First Name'}<em>*</em></label>
					<input type="text" id="firstname" name="firstname" value="{$smarty.post.firstname}" />
				</p>

				<p>
					<label for="lastname">{l s='Last Name'}<em>*</em></label>
					<input type="text" id="lastname" name="lastname" value="{$smarty.post.lastname}" />
				</p>

				<p class="select">
					<label>{l s='Date of Birth'}</label>
					<select name="birthDate" class="birthDate">
						{foreach from=$days item=day name=days}
							<option {if $birthday.mday == $day} selected{/if}
								value="{if $smarty.foreach.days.index+1 < 10}0{$smarty.foreach.days.index+1}{else}{$smarty.foreach.days.index+1}{/if}">&nbsp;{$day}&nbsp;</option>
						{/foreach}
					</select>

					<select  name="birthMonth" class="birthMonth">
						{foreach from=$months item=month name=months}
							<option {if $birthday.mon == $smarty.foreach.months.index+1} selected{/if}
								value="{if $smarty.foreach.months.index+1 < 10}0{$smarty.foreach.months.index+1}{else}{$smarty.foreach.months.index+1}{/if}">&nbsp;{$month}&nbsp;</option>
						{/foreach}
					</select>
				</p>

				<p>
					<label for="email">{l s='Email Address'}<em>*</em></label>
					<input type="text" name="email" id="email" value="{$smarty.post.email}" />
				</p>

				<p>
					<label for="passwd">{l s='New Password'}</label>
					<input type="password" name="passwd" id="passwd" autocomplete="off" autocorrect="off" autocapitalize="off" />
				</p>

				<p>
					<label for="confirmation">{l s='Confirm New Password'}</label>
					<input type="password" name="confirmation" id="confirmation" autocomplete="off" autocorrect="off" autocapitalize="off" />
				</p>

				<hr/>

				<p class="submit">
					<input type="submit" class="buttonmedium  blue" name="submitIdentity" value="{l s='Save'}" />
				</p>
			</fieldset>
		</div>
	</form>
	</div>
{/if}

{include file="$tpl_dir./my-account-sidebar.tpl"}

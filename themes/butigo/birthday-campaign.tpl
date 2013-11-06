<script type="text/javascript">
	/*<![CDATA[*/
	var baseDir = '{$base_dir_ssl}';
	{*Redirect to page, on successfull, updation of the birthday*}
	{if isset($birthdayConfirmation) && $birthdayConfirmation == 1}
		window.setTimeout('window.location="{$base_dir_ssl}{$birthdayRedirectUrl}"; ',5000);
	{/if}	
		/*]]>*/
</script>
{if isset($birthdayConfirmation) && $birthdayConfirmation == 1}
	<div class="success message">
		<a id="error-close"></a>
		<div id="successmsg">success</div>
		{l s='Your birthdate has been successfully updated.'}		
	</div>
{else}
	<div id="birthday-container">
		<div id="birthday-left-container">
			<p class="birthday-heading pink-text">{l s='Every human being is special and you want to be remembered birthdays'}.</p>		
			<p class="birthday-subheading">{l s='Birthday surprises for you on your special day she will share it with me. We wish you a year full of health'}.</p>
			<form id="birthday-form" action="{$link->getPageLink('identity.php',true)}" method="post">
				<p class="birthday-date-month">
					<span><label>{l s='Date'}</label>
						<select name="birthDate">								
							{foreach from=$days item=day name=days}
								<option value="{if $smarty.foreach.days.index+1 < 10}0{$smarty.foreach.days.index+1}{else}{$smarty.foreach.days.index+1}{/if}">&nbsp;{$day}&nbsp;</option>
							{/foreach}
						</select>
					</span>
					<span>
						<label>{l s='Month'}</label>
						<select  name="birthMonth">								
							{foreach from=$months item=month name=months}
								<option value="{if $smarty.foreach.months.index+1 < 10}0{$smarty.foreach.months.index+1}{else}{$smarty.foreach.months.index+1}{/if}">&nbsp;{$month}&nbsp;</option>
							{/foreach}
						</select>
					</span>
				</p>
				<p class="birthday-submit">
					<input type="submit" value="{l s='submit'}" name="submitBirthday" class="buttonmedium  blue"/>
				</p>
			</form>
		</div>
		<div id="birthday-right-container">
			<img id="cakecup_image" src="{$img_dir}cupcake.jpg"/>
		</div>
	</div>	
{/if}
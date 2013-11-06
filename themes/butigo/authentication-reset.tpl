<div id="second-header">
	<h2>{l s='password reset'}</h2>
</div>
<!--<div id="left-col">-->
<div id="sign-in">
<form action="" method="post" id="reset_form" class="std">
	<fieldset>
	<p>{l s='Enter your new password'}</p>
	{if $reset_passwd_error}
		{*include file="$tpl_dir./errors.tpl"*}
	{/if}
	<p>
		<label for="password">{l s='Password'}</label>
		<input type="password" id="passwd" name="passwd" value="{if isset($smarty.post.passwd)}{$smarty.post.passwd|escape:'htmlall'|stripslashes}{/if}"/>
	</p>
	<p>
		<label for="comfirmpassword">{l s='Confirm Password'}</label>
		<input type="password" id="confirmpasswd" name="confirmpasswd" value="{if isset($smarty.post.confirmpasswd)}{$smarty.post.confirmpasswd|escape:'htmlall'|stripslashes}{/if}"/>
	</p>
	<p>
		{if isset($back)}<input type="hidden" class="hidden" name="back" value="{$back|escape:'htmlall':'UTF-8'}" />{/if}
		{if isset($utm_params)}<input type="hidden" class="hidden" name="utm_params" value="{$utm_params}" />{/if}
		<input type="hidden" class="hidden" name="email" value="{$email}" />
		<input type="submit" id="SubmitReset" name="SubmitReset" class="button_login buttonmedium  blue" value="{l s='Submit'}" />
	</p>
	</fieldset>
</form>
</div>
<!--</div>-->
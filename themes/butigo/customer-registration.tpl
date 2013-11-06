<div id="second-header">
	<h2>{l s='Registration'}</h2>
</div>

<div id="style-profile">
  <div>

	<img src="{$img_ps_dir}survey/gad-survey-signup-hdr.png" alt="{l s='you’re one step closer'}" style="margin:40px 0 0"/>

	{*include file="$tpl_dir./errors.tpl"*}

	<form method="post" id="new_user" class="new_user" action="{$link->getPageLink('customer-registration.php')}" {*onsubmit="return acceptCGV('{l s='Please accept the terms of service before the next step.' js=1}');"*}>

		{$HOOK_CREATE_ACCOUNT_TOP}
		<fieldset class="medium clearAfter">
			<p>
				<label for="customer_name">{l s='Name'}</label>
				<input type="text" class="sstext" id="customer_name" name="customer_name" value="{if isset($smarty.post.customer_name)}{$smarty.post.customer_name}{/if}" />
			</p>
			<p>
				<label for="email">{l s='Email'}</label>
				<input type="text" id="email_create" class="sstext" name="email" value="{if isset($smarty.post.email)}{$smarty.post.email|escape:'htmlall'|stripslashes}{/if}" />
			</p>
			<p>
				<label for="passwd">{l s='Password'}</label>
				<input type="password" class="sstext" name="passwd" id="passwd" />
			</p>
			<p>
				<label for="passwd_confirm">{l s='Password Confirmation'}</label>
				<input type="password" class="sstext" name="passwd_confirm" id="passwd" />
			</p>
		</fieldset>

		<div style="float:left; width:100%">
			<input type="hidden" name="ref_by" value="{if isset($smarty.post.ref_by)}{$smarty.post.ref_by|escape:'htmlall':'UTF-8'}{/if}" />
			<input type="submit" name="submitAccount" id="submitAccount" class="buttonlarge blue" value="{l s='hemen katil'}" />
		</div>
		{$HOOK_CREATE_ACCOUNT_FORM}

	</form>
	<img src="{$img_ps_dir}survey/survey-signup-ftr.png" alt="{l s='you’re one step closer'}" style="margin:0 0 0 0"/>

  </div>
</div>
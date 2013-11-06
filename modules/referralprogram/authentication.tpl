
{* MODULE ReferralProgram *}
{if isset($smarty.post.referralprogram)}
<div id ="sponser-email">
<fieldset class="account_creation">
	<p>
		<label for="referralprogram">{l s='E-mail address of your sponsor' mod='referralprogram'}</label>
		<input style="background-color:#f49ac1;margin-left:10px;text-align:center;width:180px;" type="text" name="referralprogram" value="{if isset($smarty.post.referralprogram)}{$smarty.post.referralprogram|escape:'htmlall':'UTF-8'}{/if}" readonly />
	</p>
</fieldset>
</div>
{/if}
{*<fieldset class="account_creation">
	<h3>{l s='Referral program' mod='referralprogram'}</h3>
	<p>
		<label for="referralprogram">{l s='E-mail address of your sponsor' mod='referralprogram'}</label>
		<input type="text" size="52" maxlength="128" class="text" id="referralprogram" name="referralprogram" value="{if isset($smarty.post.referralprogram)}{$smarty.post.referralprogram|escape:'htmlall':'UTF-8'}{/if}" />
	</p>
</fieldset>*}
{*END : MODULE ReferralProgram *}
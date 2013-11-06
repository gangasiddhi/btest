{*
* 2007-2011 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2011 PrestaShop SA
*  @version  Release: $Revision: 7638 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{*
** Compatibility code for Prestashop older than 1.4.2 using a recent theme
** Ignore list isn't require here
** $address exist in every PrestaShop version
*}

{* Will be deleted for 1.5 version and more *}
{* Smarty code compatibility v2 *}
{* If ordered_adr_fields doesn't exist, it's a PrestaShop older than 1.4.2 *}

<script type="text/javascript">
/* <![CDATA[ */
	{literal}
	$('document').ready( function(){
		$('#sign-in a#forgot').click( function(){
			$('#sign-in form#forgot_password').slideToggle('medium');
			return false;
		});
		var height = ($( window ).height());
		var width = ($( window ).width());
		if((width > 1000 && width < 1100) && height <= 600){
			height = 628;
		}else if((width > 1000 && width <= 1100) && (height > 600 && height <= 768)){
			height = 673;
		}else if((width > 1200 && width <= 1300) && (height > 600 && height <= 800)){
			height = 802;
		}else if((width > 1400 && width <= 1550) && (height > 600 && height <= 900)){
			height = 887;
		}else if((width > 1600 && width <= 1700) && (height > 600 && height <= 1050)){
			height = 1011;
		}else if((width > 1800 && width <= 1920) && (height > 600 && height <= 1200)){
			height = 1134;
		}else if((width > 1300 && width <= 1450) && (height > 600 && height <= 800)){
			height = 824
		}
		$('#loginbg').css('min-height',height) ;
	});
	{/literal}
/* ]]> */
</script>
{* Facebook Login button script*}
<script>
{literal}
    function fbLogin(){
        FB.login(function(response) {
            if (response.status) {
                FB.api('/me', function(response) {
                    console.log(response);
                    console.log('Good to see you, ' + response.name + '.');
                    console.log('Good to see you, ' + response.email + '.');
                    window.location ="{/literal}{$link->getPageLink('authentication.php')}{literal}?response="+encodeURIComponent(JSON.stringify(response));

                });
            } else {
                console.log('User cancelled login or did not fully authorize.');
            }
        }, {scope: 'email'});
    }

{/literal}
</script>
{*include file="$tpl_dir./errors.tpl"*}
{if ($reset == 1)}
			<div class="success message">{l s='Password has been changed successfully.Login again'}
			<a id="error-close"></a>
			<div id="successheading">{l s= 'success'}</div>
			</div>
			{/if}
			{if isset($confirmation)}
			<div class="success message">
				<a id="error-close"></a>
				<div id="successheading">{l s= 'success'}</div>{l s='Password has been changed successfully.Login again'}
{l s='Your password has been successfully reset and has been sent to your e-mail address:'} {$email|escape:'htmlall':'UTF-8'}

			</div>
			{/if}
{* The below code displays the sign-in page where the user logs into his/her account *}
<div id="second-header">
	<h2>{l s='sign in'}</h2>
</div>
<div id="signin_rightcontent_container" class="{if ($back == 'refer-friends.php')}frnd{else}signin{/if}">
	<div id="sign-in">
	<form action="{$link->getPageLink("authentication.php",true)}" method="post" id="login_form" class="std">
		<fieldset>
			<h3>{l s='Sign In'}</h3>
			<p>
				<label for="email">{l s='Email'}</label>
				<input type="text" id="email" name="email" placeholder="E-posta" value="{if isset($smarty.post.email)}{$smarty.post.email|escape:'htmlall'|stripslashes}{/if}"/>
			</p>
			<p>
				<label for="password">{l s='Password'}</label>
				<input type="password" id="passwd" name="passwd" placeholder="Şifre" value="{if isset($smarty.post.passwd)}{$smarty.post.passwd|escape:'htmlall'|stripslashes}{/if}"/>
			</p>
			<p>
				{if isset($back)}<input type="hidden" class="hidden" name="back" value="{$back|escape:'htmlall':'UTF-8'}" />{/if}
				{if isset($utm_params)}<input type="hidden" class="hidden" name="utm_params" value="{$utm_params}" />{/if}
				<input type="submit" id="SubmitLogin" name="SubmitLogin" class="buttonmedium padding2em blue{if isset($HOOK_JOIN_NOW) && $HOOK_JOIN_NOW} join_now{/if}" value="{l s='Giriş Yap'}"/>
              </p>
		</fieldset>
	</form>
	<a href="#" id="forgot">{l s='Forgot your password?'}</a>
	<form action="{$request_uri|escape:'htmlall':'UTF-8'}" method="post" id="forgot_password" class="std"style="display:none";>
		<p class="enter-passwrd">{l s='Enter your email address below to reset your password'}</p>
		<fieldset>
			<p>
				<label for="email_passwd">{l s='Email'}</label>
				<input type="text" id="email" name="email" value="{if isset($smarty.post.email)}{$smarty.post.email|escape:'htmlall'|stripslashes}{/if}" />
			</p>
			<p>
				{if isset($back)}<input type="hidden" class="hidden" name="back" value="{$back|escape:'htmlall':'UTF-8'}" />{/if}
				{if isset($utm_params)}<input type="hidden" class="hidden" name="utm_params" value="{$utm_params}" />{/if}
				<input type="submit" class="buttonmedium  blue" style="margin: 0 0 10px";id="SubmitEmail" name="SubmitEmail" value="{l s='Submit email'}" />
			</p>
		</fieldset>
	</form>
    <a href="javascript:void(0)" onclick="fbLogin();" id="fblogin"><img src="{$img_dir}/facebook_login_new.png"/></a>
	</div>
	 <div id = "right-content">
		<h3>{l s='Join now'}</h3>
		<p>{l s='Join ShoeDazzle to get your own monthly stylists selection of gorgeous shoes and more!'}</p>
                {if isset($HOOK_JOIN_NOW) && $HOOK_JOIN_NOW}
                    <a href="{$link->getPageLink('surveyvsregister.php')}" title="{l s='Join Now'}" class="buttonmedium pink">  {l s='Join Now'}
                    </a>
                 {else}
                    <a href="{$link->getPageLink('stylesurvey.php')}" title="{l s='Join Now'}" class="buttonmedium pink">  {l s='Join Now'}
                    </a>
                {/if}
   </div>
</div>

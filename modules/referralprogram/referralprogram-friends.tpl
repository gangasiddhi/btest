<script type="text/javascript">
	{literal}
	$(document).ready(function() {
	  	$("a.fbox-invite").fancybox({
			'autoSize': false,
			'width': 560,
			'height': 450,
			'padding': 0,
			'margin': 0,
			'titlePosition' : 'over',
			'titleShow': false,
			'centerOnScroll': true,
			'hideOnOverlayClick': true,
			'hideOnContentClick': false,
			'overlayColor': '#000',
			'showNavArrows': false,
			'type': 'iframe'
	  	});
	});
	{/literal}
</script>
{*The customer can invite friends through emails by importing contacts to join the website*}
{if $error}
		<div class="error message">
			<a id="error-close" ></a>
			{if $error == 'email invalid'}
			<div id="ero">{l s='Error'}</div>
				{l s='At least one e-mail address is invalid!' mod='referralprogram'}
			{elseif $error == 'email exists'}
			<div id="ero">{l s='Error'}</div>
				{l s='Someone with this e-mail address has already been sponsored!' mod='referralprogram'}: {foreach from=$mails_exists item=mail}{$mail} {/foreach}
			{elseif $error == 'cannot add friends'}
				<div id="ero">{l s='Error'}</div>
				{l s='Cannot add friends to database' mod='referralprogram'}
			{elseif $error == 'name invalid'}
			<div id="ero">{l s='Error'}</div>
				{l s='Enter the name' mod='referralprogram'}
			{elseif $error == 'no details'}
			<div id ="ero">{l s='Error'}</div>
			{l s='you are not entered name and email-adress' mod='referralprogram'}
	{/if}
		</div>
	{/if}
<div id="image-container" class="invite_friends_image">
    <img src="{$img_dir}invite_friends/invite-friends3.jpg" alt="{l s='Invite Image' mod='referralprogram'}" />
</div>

<div id="container">
	<div id ="invite-friends-container">
		<div id="email-area">
			<div id="email-address">
				<a href="{$base_dir}ct/ajax_ct.php" class="fbox-invite iframe buttonlarge blue"  {*onclick="javascript:enable_win(true);" href="javascript:void(0);"*} {*href="{$modules_dir}referralprogram/importer/index.php?refurl={$refer_id|escape:'htmlall':'UTF-8'}" rel="gb_page_center[760, 530]"*}>
					{*<img src="{$img_dir}invite_friends/invite_adr_book_btn.gif" alt="{l s='Import contacts from:' mod='referralprogram'}"/>*}{l s='Import contacts from:' mod='referralprogram'}
				</a>
			</div>
			<div id="email-operators">
				<a href="{$base_dir}ct/ajax_ct.php" class="fbox-invite iframe"  {*href="{$modules_dir}referralprogram/importer/index.php?refurl={$refer_id|escape:'htmlall':'UTF-8'}" rel="gb_page_center[760, 530]"*}>
					<img src="{$img_dir}invite_friends/email_operators.gif" alt="{l s='Import contacts from:' mod='referralprogram'}"/>
				</a>
			</div>
		</div>
		<div id="social_network">
			<div id="invite-facebook">
				<a href="http://www.facebook.com/share.php"
				   onclick='window.open("http://www.facebook.com/sharer.php?u={$base_dir|escape:'htmlall':'UTF-8'}{$refer_link|escape:'htmlall':'UTF-8'}{'&utm_medium=viral&utm_source=invitefriendsfb&utm_content='|escape:'htmlall':'UTF-8'}{$ref_id|escape:'htmlall':'UTF-8'}", "{l s='Facebook Share' mod='referralprogram'}", "toolbar=0, status=0, width=626, height=536"); return false;'
				   target="_blank" rel="nofollow" title="{l s='Facebook Share' mod='referralprogram'}">
					<img src="{$img_dir}invite_friends/invite_fb.png" alt="{l s='Facebook Share' mod='referralprogram'}"/>
				</a>
			</div>
			<div id ="invite-twitter">
				<a href="http://twitter.com/share?url={$base_dir|escape:'htmlall':'UTF-8'}{$refer_link|escape:'htmlall':'UTF-8'}{'&utm_medium=viral&utm_source=invitefriendstwitter&utm_content='|escape:'htmlall':'UTF-8'}{$ref_id|escape:'htmlall':'UTF-8'}&text={l s='Ivana Sert\'in ayakkabı sitesini keşfettim! @Butigocom \'a katılmış.' mod='referralprogram'}&count=none"
				   onclick='window.open("http://twitter.com/share?url={$base_dir|escape:'htmlall':'UTF-8'}{$refer_link|escape:'htmlall':'UTF-8'}{'&utm_medium=viral&utm_source=invitefriendstwitter&utm_content='|escape:'htmlall':'UTF-8'}{$ref_id|escape:'htmlall':'UTF-8'}&text={l s='Ivana Sert\'in ayakkabı sitesini keşfettim! @Butigocom \'a katılmış.' mod='referralprogram'}&count=none", "{l s='Twitter share' mod='referralprogram'}", "height=450, width=550, resizable=1"); return false;'
				   target="_blank" rel="nofollow" title="{l s='Twitter share' mod='referralprogram'}">
					<img src="{$img_dir}invite_friends/invite_tt.png" alt="{l s='Twitter share' mod='referralprogram'}" />
				</a>
			</div>
		</div>
       {* {if $error}
		<p class="error">
			{if $error == 'email invalid'}
				{l s='At least one e-mail address is invalid!' mod='referralprogram'}
			{elseif $error == 'email exists'}
				{l s='Someone with this e-mail address has already been sponsored!' mod='referralprogram'}: {foreach from=$mails_exists item=mail}{$mail} {/foreach}
			{elseif $error == 'cannot add friends'}
				{l s='Cannot add friends to database' mod='referralprogram'}
			{/if}
		</p>
	{/if}*}
	{if $invitation_sent}
		<p class="success">
		{if $nbInvitation > 1}
			{l s='Emails have been sent to your friends !' mod='referralprogram'}
		{else}
			{l s='Email have been sent to your friend !' mod='referralprogram'}
		{/if}
		</p>
	{/if}
		<div id ="invite-friends">
		{* <p>{l s='To:'}&nbsp;<span>{l s='invite friends from address book' mod='referralprogram'}</span>&nbsp;{l s='(separate email address by commass) or' mod='referralprogram'}</p>*}
		<form method="post" action="" class="std">
			<table class="std" id="enter-emails">
			<thead>
				<tr>
					<th class="empty">&nbsp;</th>
					<th class="item_width ">{l s='Name' mod='referralprogram'}</th>
					<th class="item_width ">{l s='E-mail' mod='referralprogram'}</th>
				</tr>
			</thead>
			<tbody>
				{section name=friends start=0 loop=$nbFriends step=1}
				<tr class="{if $smarty.section.friends.index % 2}item{else}alternate_item{/if}">
					<td class="align_right ">{$smarty.section.friends.iteration}</td>
					<td><input type="text" class="text" name="friendsFirstName[{$smarty.section.friends.index}]" size="14" value="{if isset($smarty.post.friendsFirstName[$smarty.section.friends.index])}{$smarty.post.friendsFirstName[$smarty.section.friends.index]}{/if}" /></td>
					<td><input type="text" class="text" name="friendsEmail[{$smarty.section.friends.index}]" size="20" value="{if isset($smarty.post.friendsEmail[$smarty.section.friends.index])}{$smarty.post.friendsEmail[$smarty.section.friends.index]}{/if}" /></td>
				</tr>
				{/section}
			</tbody>
			</table>

			<fieldset>
			<div>
				<p id="invite-message">{l s='Message:' mod='referralprogram'}</p>
				<textarea class="email-friends" id ="friendMessage" name="friendMessage" >{*{rows="5"l s='Hi! I wanted to share this amazing site called ShoeDazzle, where you get gorgeous shoes, handbags and jewelry, and personalized recommendations from real Hollywood stylists. Everything is just $39.95. Join now and get 20% off your first ShoeDazzle item!' mod='referralprogram'}*}</textarea>
				<div id="invite">
					{*<a href="" title="{l s='Terms and conditions' mod='referralprogram'}">{l s='Terms and conditions' mod='referralprogram'}</a>*}
					<input type="submit" id="submitSponsorFriends" name="submitSponsorFriends" class="buttonmedium pink" style="float: right; margin: 0 -5px 0 0;" value="{l s='Send Invites' mod='referralprogram'}"/>
				</div>
			</div>
			</fieldset>
		</form>
		</div>{*end of invite-friends*}
	</div>{*end of invite-friends-container*}
</div>{*end of container*}
<div class="sidebar" id="sideRight">
	<div id="first-invite">
		<h4>{l s='200 Stil Puanı Senin Olsun' mod='referralprogram'}</h4>
		<p>{l s=' Davet ettiğin her arkadaşının ilk alışverişinde 200 stil puanı senin olacak. 1000 puana ulaştığında istediğin bir Butigo’ya hiç bir ücret ödemeden sahip olacaksın. Üstelik kazanabileceğin ayakkabı sayısının limiti yok.' mod='referralprogram'}</p>
                <p>{l s=' Kazandıgın puanları stil puanlarım sayfasından kontrol edebilirsin. Daha çok arkadaşına gonder daha cok kazan!' mod='referralprogram'}</p>
	</div>
	<div id="personal-invite">
		<h4>{l s='Your personal invite link' mod='referralprogram'}</h4>
		<p>{l s='Copy and paste your personal invitation link to share online! Your friends must click through your personal link for you to earn free credits.' mod='referralprogram'}</p>
		<input class="invite-link" type="text" value="{$base_dir}{$refer_link}" name="refcode" id ="refcode" readonly="true"/>
	</div>
	<div id="already-invited">
		{*<h4>{l s='Already Sent Invites?' mod='referralprogram'}</h4>*}
		<p>{l s='Follow your invite statuses, remind your friends to join or invite more friends! For every friend who places her first order, you’ll get 200 Style Points toward a free credit. Your points will appear on your account when your friends’ orders ship.' mod='referralprogram'}<a href="{$link->getPageLink('referrals-stylepoints.php')}" title="{l s='visit your style point tab' mod='referralprogram'}">{l s='visit your style point tab' mod='referralprogram'}</a>&nbsp;{l s='to track your email invites.' mod='referralprogram'}</p>
	</div>
</div>{*end of sideRight*}

{*<div id="wbox">&nbsp;</div>
<div id="ibox" style="width: 400px;">
	<div id="x_wrap">
		<div class="clearit">
			<p style="float: left" class="clearit">
				<a onclick="javascript: enable_win(false);" href="javascript: void(0);" class="endmenu"><span>{l s='Close Window' mod='referralprogram'}</span></a>
			</p>
		</div>
		<div id="ajax"></div>
	</div>
</div>
<div id="loader" name="loader" style="display: none; width: 1px; height: 1px;">&nbsp;</div>*}

{*customers can send emails to friends to remind them to join the website.
Also the style points earned by the customer is also displayed *}
{if $revive_sent}
	<div class="success message">
		<a id="error-close"></a>
		<div id="succmsg">success</div>
		{*if $nbRevive > 1*}
			{l s='Revive email has been sent to your friend !' mod='referralprogram'}
		{*else}
			{l s='Revive emails have been sent to your friends!' mod='referralprogram'}
		{/if*}
	</div>
	{/if}
<div id="style-points-container">
    {* <img src="{$img_ps_dir}site/headermyinvites.jpg" alt="{l s='Invite Image' mod='referralprogram'}"/> *}
	<div id="stylepoints-top">

		<div id="top_container">
			<div>
				<div id="pending">
					{*<span class="color_pink" style="font-weight: bold;">{l s='Pending Points:' mod='referralprogram'}</span>
					<span class="style-point-value">{$pendingPoints}</span>*}
				</div>
				<div id ="my_style_points">
					<span class="style_label">{l s='My Style Points:' mod='referralprogram'}</span>&nbsp;<span class="style-point-value">{$totalPoints}</span>
				</div>
			</div>
			<div id="credit">
				<span class="style_label">{l s='Points Until a Free Credit 1000' mod='referralprogram'}</span><br/>
				<span class="color_pink">{l s='1000 Style Points = 1 Free Credit' mod='referralprogram'}</span><br/>
				<span class="style_label">{l s='The maximum amount of Free Product is 150 ' mod='referralprogram'}</span>
			</div>
		</div>
		<div id="bottom-container">
			<div id="learn_more">
				{*<span class="color_pink" style="font-weight: bold;"><a href="#">{l s='Learn More' mod='referralprogram'}</a></span>*}
			</div>
		</div>
	</div>
</div>
<div id ="style-point-links">
<h4>{l s='Earn More Style Points' mod='referralprogram'}</h4>
    <ul>
        <li {if isset($no_butigim_link)}id="svr_padding"{/if}><a href="{$link->getPageLink('referrals-friends.php')}" title="{l s='Invite Friends' mod='referralprogram'}"><img src="{$img_ps_dir}site/invite_friends.jpg" width="275" height= "117" alt="{l s='invite_friends' mod='referralprogram'}" /></a></li>
        {if !isset($no_butigim_link)}
             <li id="auto_padding"><a href="{$link->getPageLink('showroom.php')}" title="{l s='My Showroom' mod='referralprogram'}"><img src="{$img_ps_dir}site/make-a-purchase.jpg" width="277" height= "117" alt="{l s='make-a-purchase' mod='referralprogram'}" /></a></li>
         {/if}
        <li><img src="{$img_ps_dir}site/coming_soon.jpg" width="274" height= "117" alt="{l s='coming_soon' mod='referralprogram'}" /></li>
    </ul>
</div>
<div id ="style-points">


    <div style="float: left;">
    </div>
    <div id="track-email-con">
    	<div class="track-history track-history-pending-friends">
    		<div class="track-invites-table-inside">
    			{*<div>
    				<a href="" title="{l s='minus button' mod='referralprogram'}"><img src="{$img_dir}minus-button.gif" alt="{l s='minus button' mod='referralprogram'}"/></a>
    			</div>*}
    			<div>
    				<h4>{l s='Invited Emails' mod='referralprogram'}</h4>
    			</div>
    		</div>{*end of track-invites-table-inside*}
            <div class="invites-table">
                <table class="emails">
                    <thead>
                        <tr class="invites-table-heading">
                            <th>{l s='E-mail' mod='referralprogram'}</th>
                            <th>{l s='Status' mod='referralprogram'}</th>
                            <th>{l s='Resend' mod='referralprogram'}</th>
                        </tr>
                    </thead>
                    <tbody>
                        {if $pendingFriends AND $pendingFriends|@count > 0}
                            {foreach from=$pendingFriends item=pendingFriend name=myLoop}
                                <tr>
                                    <td>{$pendingFriend.email}</td>
                                    <td>{l s=' Not Accepted ' mod='referralprogram'}</td>
                                    <td>
                                        <form method="post" action="{*{$base_dir}modules/referralprogram/referralprogram-stylepoints.php*}" class="std">
                                            <input type="hidden" value="{$pendingFriend.id_referralprogram}" id="friendChecked" name="friendChecked"/>
                                            <input type="submit" value="{l s='Remind' mod='referralprogram'}" name="revive" id="revive" class="button_large" />
                                        </form>
    									&nbsp;
                                    </td>
                                </tr>
                            {/foreach}
                        {else}
                           <tr>
                                <td colspan=3 style="text-align:center">{l s='No referral emails have been sent yet.' mod='referralprogram'}<td>
                            </tr>
                    	{/if}
                    </tbody>
                </table>
            </div>
            {if $paginationParams.pendingFriends.totalItem > $paginationParams.pendingFriends.itemPerPage}
                <div id="pending-friends-pagination" class="pagination butigo-pagination pink-pagination"></div>
            {/if}
        </div>

        {if $subscribeFriends AND $subscribeFriends|@count > 0}
            <div class="track-history track-history-subscribe-friends">
                <div class="track-invites-table-inside">
                    {*<div>
                        <a href="" title="{l s='minus button' mod='referralprogram'}"><img src="{$img_dir}minus-button.gif" alt="{l s='minus button' mod='referralprogram'}"/></a>
                    </div>*}
                    <div>
                        <h4>{l s='Registered Emails' mod='referralprogram'}</h4>
                    </div>
                </div>{*end of track-invites-table-inside*}
                <div class="invites-table">
                    <table  class="emails">
                        <thead>
                            <tr class="invites-table-heading">
                                <th>{l s='E-mail' mod='referralprogram'}</th>
                                <th>{l s='Status' mod='referralprogram'}</th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach from=$subscribeFriends item=subscribeFriend name=myLoop}
                                <tr>
                                    <td>{$subscribeFriend.email}</td>
                                    <td>{l s='Accepted ' mod='referralprogram'}</td>
                                </tr>
                           {/foreach}
                        </tbody>
                    </table>
                </div>
                {if $paginationParams.subscribedFriends.totalItem > $paginationParams.subscribedFriends.itemPerPage}
                    <div id="subscribed-friends-pagination" class="pagination butigo-pagination pink-pagination"></div>
                {/if}
            </div>
        {/if}
    </div>

    <div id="track-style-points-table" class="track-history">
        <div class="track-invites-table-inside">
           {* <div>
                <a href="" title="{l s='minus button' mod='referralprogram'}"><img src="{$img_dir}minus-button.gif" alt="{l s='minus button' mod='referralprogram'}"/></a>
            </div>*}
            <div>
                <h4>{l s='My Style Point History' mod='referralprogram'}</h4>
            </div>
        </div>{*end of track-invites-table-inside*}
        <div class="invites-table">
            <table id="style-points-history">
                <thead>
                    <tr class="invites-table-heading">
						<th>{l s='Date' mod='referralprogram'}</th>
                        <th>{l s='My Actions' mod='referralprogram'}</th>
                        <th>{l s='Points' mod='referralprogram'}</th>
						{*<th>{l s='Points Status' mod='loyalty'}</th>*}
                        <th>{l s='Expiration*' mod='referralprogram'}</th>
                    </tr>
                </thead>
               <tbody>
					 {if $orders && count($orders)}
                        {foreach from=$orders item=order}
                            {if $order.id || $order.id_refer}
								<tr class="alternate_item">
									<td class="history_link bold">&nbsp;{dateFormat date=$order.date}</td>
									{if $order.id}
										<td class="history_date">{if $order.id_loyalty_state == 3 || $order.id_loyalty_state == 6 ||  $order.id_loyalty_state == 7  || $order.id_loyalty_state == 12 || $order.id_loyalty_state == 17 }{$order.state|escape:'htmlall':'UTF-8'}{else} {l s='Purchase'}{/if}<br/>{l s='Order#:'}{$order.id|intval}</td>
									{elseif $order.id_refer}
										{foreach from=$referralEmails item = 'referralEmail'}
										   {if $order.id_refer == $referralEmail.refer_id}
												<td class="history_date">{l s='Referral'}<br/>
												{l s='Reffered Email:'}{$referralEmail.email}</td>
										   {/if}
										{/foreach}
									{/if}
									<td class="history_method">{$order.points|intval}</td>
									{*<td class="history_method">{$order.state|escape:'htmlall':'UTF-8'}</td>*}
									<td>&nbsp;</td>
									{*<td class="history_date">{dateExpireFormat date=$order.date year=1}</td>*}
								</tr>
							{/if}
					   {/foreach}
					{*{else}
						<tr>{l s='No Style Points have been earned yet..' mod='referralprogram'}</tr>*}
					{/if}
                </tbody>
            </table>
            {if $paginationParams.order.totalItem > $paginationParams.order.itemPerPage}
                <div id="order-pagination" class="pagination butigo-pagination pink-pagination"></div>
            {/if}
        </div>{* end of invites-table*}
    </div>{*end of style-points*}
</div>

<script type="text/javascript">
    {literal}
        $(function() {
            var paginationParams = {/literal}{$paginationParams|@json_encode}{literal}
            {/literal}{if $paginationParams.order.totalItem > $paginationParams.order.itemPerPage}{literal}
                $("#order-pagination").pagination(paginationParams.order.totalItem, {
                    prev_text: '{/literal}{l s="Prev"}{literal}'
                    , next_text: '{/literal}{l s="Next"}{literal}'
                    , items_per_page: paginationParams.order.itemPerPage
                    , num_display_entries: 12
                    , num_edge_entries: 2
                    , current_page : paginationParams.order.pageNo-1
                    , callback: function(pageNo, $pagination) {
                        if (pageNo == paginationParams.order.pageNo-1) return;
                        pageNo = parseInt(pageNo) + 1;
                        var urlParams =  getQueryString();
                        urlParams['orderPagination'] = pageNo;
                        goToUrl(location.pathname+ '?' + $.param(urlParams));
                        return false;
                    }
                });

            {/literal}{/if}{literal}

            {/literal}{if $paginationParams.pendingFriends.totalItem > $paginationParams.pendingFriends.itemPerPage}{literal}

               $("#pending-friends-pagination").pagination(paginationParams.pendingFriends.totalItem, {
                    prev_text: '{/literal}{l s="Prev"}{literal}'
                    , next_text: '{/literal}{l s="Next"}{literal}'
                    , items_per_page:paginationParams.pendingFriends.itemPerPage
                    , num_display_entries: (paginationParams.pendingFriends.totalItem / paginationParams.pendingFriends.itemPerPage > 50)? 4 : 8
                    , num_edge_entries: 2
                    , current_page: paginationParams.pendingFriends.pageNo-1
                    , callback: function(pageNo, $pagination) {
                        if (pageNo == paginationParams.pendingFriends.pageNo-1) return;
                        pageNo = parseInt(pageNo) + 1;

                        var urlParams =  getQueryString();
                        urlParams['pendingFPagination'] = pageNo;
                        goToUrl(location.pathname+ '?' + $.param(urlParams));
                        return false;
                    }
                });

            {/literal}{/if}{literal}

            {/literal}{if $paginationParams.subscribedFriends.totalItem > $paginationParams.subscribedFriends.itemPerPage}{literal}
               $("#subscribed-friends-pagination").pagination(paginationParams.subscribedFriends.totalItem, {
                    prev_text: '{/literal}{l s="Prev"}{literal}'
                    , next_text: '{/literal}{l s="Next"}{literal}'
                    , items_per_page:paginationParams.subscribedFriends.itemPerPage
                    , num_display_entries: (paginationParams.subscribedFriends.totalItem / paginationParams.subscribedFriends.itemPerPage > 50)? 4 : 8
                    , num_edge_entries: 2
                    , current_page: paginationParams.subscribedFriends.pageNo-1
                    , callback: function(pageNo, $pagination) {
                        if (pageNo == paginationParams.subscribedFriends.pageNo-1) return;
                        pageNo = parseInt(pageNo) + 1;
                        var urlParams =  getQueryString();
                        urlParams['subscribedFPagination'] = pageNo;
                        goToUrl(location.pathname+ '?' + $.param(urlParams));
                        return false;
                    }
                });
            {/literal}{/if}{literal}

        });
    {/literal}

</script>

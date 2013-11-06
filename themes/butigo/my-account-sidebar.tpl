<div class="sidebar" id="sideRight">
	<div id="client_services">
		<h4>{l s='My Account'}</h4>
		<ul class="sidebar_options">
            <li {if $page_name == 'identity'}class="current"{/if}><a href="{$link->getPageLink('identity.php', true)}" title="{l s='Membership Information'}">{l s='Membership Information'}</a></li>
			<li {if $page_name == 'voucher'}class="current"{/if}><a href="{$link->getPageLink('vouchers.php', true)}" title="{l s='My Vouchers'}">{l s='My Vouchers'}</a></li>
			<li {if $page_name == 'addresses' || $page_name == 'address'}class="current"{/if}><a href="{$link->getPageLink('addresses.php', true)}" title="{l s='Shipping Addresses'}">{l s='Shipping Addresses'}</a></li>
            <li {if $page_name == 'history'}class="current"{/if}><a href="{$link->getPageLink('history.php', true)}" title="{l s='Order History'}">{l s='Order History'}</a></li>
		</ul>
	</div>
    <div class="ma_call">
		<p><img src="{$img_dir}phone.png" alt="{l s='phone'}"/>
        	{l s='Questions?'}<br/>
			{l s='1.888.508.1888 M-F: 8AM-5PM (Pacific)'}</p>
    </div>
</div>

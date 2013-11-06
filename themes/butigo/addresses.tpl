
{* Two variable are necessaries to display the address with the new layout system *}
{* Will be deleted for 1.5 version and more *}
{if !isset($multipleAddresses)}
	{$ignoreList.0 = "id_address"}
	{$ignoreList.1 = "id_country"}
	{$ignoreList.2 = "id_state"}
	{$ignoreList.3 = "id_customer"}
	{$ignoreList.4 = "id_manufacturer"}
	{$ignoreList.5 = "id_supplier"}
	{$ignoreList.6 = "date_add"}
	{$ignoreList.7 = "date_upd"}
	{$ignoreList.8 = "active"}
	{$ignoreList.9 = "deleted"}

	{* PrestaShop < 1.4.2 compatibility *}
	{if isset($addresses)}
		{$address_number = 0}
		{foreach from=$addresses key=k item=address}
			{counter start=0 skip=1 assign=address_key_number}
			{foreach from=$address key=address_key item=address_content}
				{if !in_array($address_key, $ignoreList)}
					{$multipleAddresses.$address_number.ordered.$address_key_number = $address_key}
					{$multipleAddresses.$address_number.formated.$address_key = $address_content}
					{counter}
				{/if}
			{/foreach}
		{$multipleAddresses.$address_number.object = $address}
		{$address_number = $address_number  + 1}
		{/foreach}
	{/if}
{/if}

{* Define the style if it doesn't exist in the PrestaShop version*}
{* Will be deleted for 1.5 version and more *}
{if !isset($addresses_style)}
	{$addresses_style.company = 'address_company'}
	{$addresses_style.vat_number = 'address_company'}
	{$addresses_style.firstname = 'address_name'}
	{$addresses_style.lastname = 'address_name'}
	{$addresses_style.address1 = 'address_address1'}
	{$addresses_style.address2 = 'address_address2'}
	{$addresses_style.city = 'address_city'}
	{$addresses_style.city = 'address_city'}
	{$addresses_style.country = 'address_country'}
	{$addresses_style.phone = 'address_phone'}
	{$addresses_style.phone_mobile = 'address_phone_mobile'}
	{$addresses_style.alias = 'address_title'}
{/if}

<script type="text/javascript">
/*<![CDATA[*/
	var baseDir = '{$base_dir_ssl}';
	{literal}
	$(document).ready(function(){
		//resizeAddressesBox();
	});
	{/literal}
/*]]>*/
</script>

<div id="container">
	<h3>{l s='shipping addresses'}</h3>
	<div id = "shipping_address">
		<hr/>
		{if isset($multipleAddresses) && $multipleAddresses}
		<div class="addresses">
			{assign var="adrs_style" value=$addresses_style}
			{foreach from=$multipleAddresses item=address name=myLoop}
			<ul class="full_address {if $smarty.foreach.myLoop.last}last_item{elseif $smarty.foreach.myLoop.first}first_item{/if} {if $smarty.foreach.myLoop.index % 2}alternate_item{else}item{/if}">
				{*<li class="address_title">{$address.object.alias}</li>*}
				{foreach from=$address.ordered name=adr_loop item=pattern}
					{assign var=addressKey value=" "|explode:$pattern}
					<li>
					{foreach from=$addressKey item=key name="word_loop"}
						<span class="{if isset($addresses_style[$key])}{$addresses_style[$key]}{/if}">
							{$address.formated[$key]|escape:'htmlall':'UTF-8'}
						</span>
					{/foreach}
					</li>
				{/foreach}
			</ul>
			<ul class="edit_addresses">
				<li class="address_edit">
					<a href="{$link->getPageLink('address.php', true)}?id_address={$address.object.id|intval}" class = "buttonmedium blue" style="display:block;" title="{l s='Update'}">
						{*<img src="{$img_dir}buttons/gunvele.png" alt="{l s='Update'}"/>*}{l s='Update'}
					</a>
				</li>
				<li class="address_delete">
					<a href="{$link->getPageLink('address.php', true)}?id_address={$address.object.id|intval}&amp;delete" onclick="return confirm('{l s='Are you sure?'}');"class = "buttonmedium pink" style="display:block;" title="{l s='Delete'}">
						{*<img src="{$img_dir}buttons/delete.png" alt="{l s='Delete'}"/>*}{l s='Delete'}
					</a>
				</li>
			</ul>
			{/foreach}
		</div>
		{else}
		<p class="no_address">
			{l s='No addresses available.'}&nbsp;<a href="{$link->getPageLink('address.php', true)}">{l s='Add new address'}</a>
		</p>
		{/if}
		<hr/>
	</div>{*end of shipping address*}
</div> {*end of container*}

{include file="$tpl_dir./my-account-sidebar.tpl"}

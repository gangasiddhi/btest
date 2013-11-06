<div id="slide_boxes_list" class="slide_list">
<ul>
	<li>
	<div id="slide-left">
		<div class="slide-width">
			<a target="_blank" href="{if $logged}{$link->getPageLink('referrals-friends.php', false)}{else}{$link->getPageLink('stylesurvey.php')}{/if}">
				<span class="slide_title">{l s='Arkadaşlarını Davet Et, Ayakkabın Hediyemiz Olsun.' mod='slideoutboxes'}</span>
				<span class="slide_links">{l s='Hemen başla' mod='slideoutboxes'}</span>
			</a>
		</div>
		<a target="_blank" href="{if $logged}{$link->getPageLink('referrals-friends.php', false)}{else}{$link->getPageLink('stylesurvey.php')}{/if}">
			<img src="{$this_path}img/hediye.jpg" alt="{l s='Hediye' mod='slideoutboxes'}"/>
		</a>
	</div>
	</li>
	<li>
	<div id="slide-left">
		{foreach from=$stylist item=stylist1}
		<div class="slide-width">
			<a target="_blank" href="{$link->getPageLink('stylists.php')}">
				<span class="slide_title">{$stylist1.quote}</span>
			</a>
		</div>
		<a target="_blank" href="{$link->getPageLink('stylists.php')}">
			<img src="{$img_ps_dir}stylists/thumbnail/{$stylist1.file2}" alt="{$stylist1.file2}"/>
		</a>
		{/foreach}
	</div>
	</li>
</ul>
</div>

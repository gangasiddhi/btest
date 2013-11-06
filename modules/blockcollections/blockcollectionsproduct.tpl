
{*begin collections*}
<div id="collections">
	<h2>{l s='Browse  Our  LookBooks' mod='blockcollections'}</h2>
	<div id="cols-content">
		<ul id="cols-list">
			{foreach from=$xml item=col_item name=collection key=count}
			{if $count <= 3}
			<li class="{if $smarty.foreach.collection.last}last{/if}">
				<a href="{$link->getPageLink('lookbook.php')}" class="lookbook" title="{l s='LookBooks' mod='blockcollections'}">
					<img src='{$media_server}{$this_path}{$col_item.thumbnail}'alt="" title="" />
					<p class="collection_name">{$col_item.collection_name}<span></span></p>
				</a>
			</li>
			{/if}
			{/foreach}
		</ul>
	</div>
</div>

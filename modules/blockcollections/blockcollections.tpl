
{*begin collections*}
<div id="collections">
	<div id="cols-content">
		<ul id="cols-list">
			{foreach from=$xml item=col_item name=collection key=count}
			<li class="{if $smarty.foreach.collection.last}last{/if}">
				<a data-trigger-rel="collection_detail_{$col_item.collection_order}" href='{$this_path}slides.php?count={$col_item.collection_count}' class="fbox-cols iframe">
					<img src='{$media_server}{$this_path}{$col_item.thumbnail}'alt="" title="" />
					<p class="collection_name">{$col_item.collection_name}<span></span></p>
				</a>
			</li>
			{/foreach}
		</ul>
	</div>
</div>
<div id="collection_details" style="display:none">
	{foreach from=$xmlDetails item=item name=collection}
		<ul id="cols-carousel" class="jcarousel-skin-cols">	
		{foreach from=$item->item item=detailItem name=collection}
			{if $detailItem->image}<li><a class="collection-detail-links" rel="collection_detail_{$item->collection_order}" href="{$media_server}{$this_path}{$detailItem->image}"><img src="{$media_server}{$this_path}{$detailItem->image}" /></a></li>{/if}
		{/foreach}
		</ul>
		{/foreach}
	</div>

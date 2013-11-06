{if isset($images) AND $images}
<div align="center" style="padding:0 0 10px 0;margin: {$margin}px auto {$margin}px auto;height:{$height}px;overflow: hidden; clear:both">
<div id="wrap">
	<ul id="shoes-more" class="jcarousel-skin-shoesmore">
	{foreach from=$images item=image key=i}
		<li>
		{if isset($image.link) AND $image.link}
			<a href="{$image.link}">
		{/if}
		{if isset($image.name) AND $image.name}
			<img src="{$media_server}{$this_path}slides/{$image.name}" {if $fit == 'true'} style="width:{$width}; height:{$height}px" {/if} alt="{$image.name}" />
		{/if}
		{if isset($image.link) AND $image.link}
			</a>
		{/if}
		</li>
	{/foreach}
	</ul>
</div>
</div>
{/if}

{if $bu_env=='production'}
	{*SailThru Meta Tags*}
	{*product Tags*}
	{assign var=tags value=$product->tags[$id_currency_cookie]}
	{assign var=productTags value='pid'|cat:$product->id|cat:","}
	{assign var=productTags value=$productTags|cat:$product->name|cat:","}
	{if isset($tags)}
		{foreach name=tags from=$tags key=k item=productTag}
			{foreach from=$productTag item=tag}
				{assign var=productTags value=$productTags|cat:$tag|cat:","}
			{/foreach}
		{/foreach}
	{/if}
	{if $product->quantity < 3}
		{assign var=productTags value=$productTags|cat:"low-stock,"}
	{/if}
	{assign var='productPrice' value=$product->getPrice(true, $smarty.const.NULL, 2, $smarty.const.NULL, false, false)}
	{assign var=productTags value=$productTags|cat:"price "|cat:$productPrice*100}
	
	<meta name="sailthru.title" content="{$product->name|escape:html:'UTF-8'}"/>
	<meta name="sailthru.tags" content="{$productTags}" />
	<meta name="sailthru.description" content="{$product->description_short|escape:html:'UTF-8'|truncate:145:''}"/>
	<meta name="sailthru.date" content="{$product->date_add}"/>
	<meta name="sailthru.image.full" content="{$link->getImageLink($product->link_rewrite, $cover.id_image, 'medium')}" />
	<meta name="sailthru.image.thumb" content="{$link->getImageLink($product->link_rewrite, $cover.id_image, 'prodsmall')}" />
	{if $product->quantity <= 0}
		{*<meta name="sailthru.expire_date" content="{$smarty.now|date_format:'%a, %d %b %Y %H:%M:%S'}"/>*}	
		<meta name="sailthru.expire_date" content="{$smarty.now|date_format:'%Y-%m-%d 00:00:00'}"/>
	{/if}
	{*End SailThru Meta Tags*}
{/if}
<script>
    var categoryId = '{$category->id}';
</script>

{*$HOOK_AFTER_MENU*}
{assign var=cat_name value=$category->name|lower|replace:' ':'' }
<div class="cat_header">
	{if ($category->id_parent == 1 && $category->name != 'Shoes')}
		<img src="{$img_cat_dir}{$category->id}.jpg" alt="{cat_name}" />
	{else}
		<div id="header_text">
				<h1>{$category->header}</h1>
				<h2>{$category->header_text}</h2>
		</div>
	{/if}
</div>
{if isset($child_categories)}
	<div class = "category-nav">
		<ul class="cat_links">
		{foreach from=$child_categories key=cat_id item=eachCategory name=catLoop}
			<li {if $category->id==$cat_id}class=current_category{/if}><h3><a href="{$eachCategory.link}">{$eachCategory.name}</a></h3></li>
		{/foreach}
		</ul>
            {$HOOK_FILTER_TAGS}
            {if $category->description}
                <div class="desc_title">
                    {if $category->name != 'Shoes'}
                        {$category->name}
                    {else}
                        {$category->header}
                    {/if}
                </div>
                <div class="category_description">
                    {$category->description}
                </div>
            {/if}
	</div>
{elseif $category->name == 'DiscountOutlet'}
	<div class = "category-nav">
		{$HOOK_FILTER_TAGS}
		{if $category->description}
			<div class="desc_title">
				{$category->header}
			</div>
			<div class="category_description">
				{$category->description}
			</div>
		{/if}
	</div>
{/if}

{if isset($category)}
	{if $category->id AND $category->active}
		{if $products}
			{include file="$tpl_dir./product-list.tpl" products=$products}
		{elseif !isset($subcategories)}
			<p class="cat_empty">{l s='There are no products in this category.'}</p>
		{/if}
	{elseif $category->id}
		<p class="cat_empty">{l s='This category is currently unavailable.'}</p>
	{/if}
{/if}
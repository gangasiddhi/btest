{*if isset($cms) && $cms->id != $cgv_id}
	{include file="$tpl_dir./breadcrumb.tpl"}
{/if*}
{if isset($cms) && !isset($category)}
	{if !$cms->active}
		<br />
		<div id="admin-action-cms">
			<p>{l s='This CMS page is not visible to your customers.'}
			<input type="hidden" id="admin-action-cms-id" value="{$cms->id}" />
			<input type="submit" value="{l s='Publish'}" class="exclusive" onclick="submitPublishCMS('{$base_dir}{$smarty.get.ad}', 0)"/>			
			<input type="submit" value="{l s='Back'}" class="exclusive" onclick="submitPublishCMS('{$base_dir}{$smarty.get.ad}', 1)"/>			
			</p>
			<div class="clear" ></div>
			<p id="admin-action-result"></p>
			{*</p>*}
		</div>
	{/if}
	{if $content_only}
	     {$cms->content}
	{else}
		<div class="rte">
			<div id="second-header">
				<h2>{$cms->meta_title}</h2>
			</div>
			{if $id_cms == 14 || $id_cms == 16 || $id_cms == 6 || $id_cms == 10 ||$id_cms == 11}
				<div id="container">
					{$cms->content}
				</div>
				{else}
					 {$cms->content}
				{/if}
			{if $id_cms == 14 || $id_cms == 16 || $id_cms == 6 || $id_cms == 10 ||$id_cms == 11}
				<div class="sidebar" id="sideRight">
					{include file="$tpl_dir./sidebar.tpl"}
				</div>
			{/if}
		</div>
	{/if}
{elseif isset($category)}
	<div>
		<h1>{$category->name|escape:'htmlall':'UTF-8'}</h1>
		{if isset($sub_category) & !empty($sub_category)}	
			<h4>{l s='List of sub categories in '}{$category->name}{l s=':'}</h4>
			<ul class="bullet">
				{foreach from=$sub_category item=subcategory}
					<li>
						<a href="{$link->getCMSCategoryLink($subcategory.id_cms_category, $subcategory.link_rewrite)|escape:'htmlall':'UTF-8'}">{$subcategory.name|escape:'htmlall':'UTF-8'}</a>
					</li>
				{/foreach}
			</ul>
		{/if}
		{if isset($cms_pages) & !empty($cms_pages)}
		<h4>{l s='List of pages in '}{$category->name}{l s=':'}</h4>
			<ul class="bullet">
				{foreach from=$cms_pages item=cmspages}
					<li>
						<a href="{$link->getCMSLink($cmspages.id_cms, $cmspages.link_rewrite)|escape:'htmlall':'UTF-8'}">{$cmspages.meta_title|escape:'htmlall':'UTF-8'}</a>
					</li>
				{/foreach}
			</ul>
		{/if}
	</div>
{else}
	{l s='This page does not exist.'}
{/if}
<br />
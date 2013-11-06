<div id="second-header">
	<h2>{l s='FAQ'}</h2>
</div>

<div id="container">
	{if $faqs && count($faqs) > 0}
	{*<script type="text/javascript" src="{$content_dir}js/faq.js"></script>*}

	<div id="faq_options">
		<a href="#" onclick="expandAll(); return false;">{l s='Expand All'}</a> | <a href="#" onclick="collapseAll(); return false;">{l s='Collapse All'}</a>
	</div>

	<dl class="faq">
	{foreach from=$faqs item=faq name=loop}
		<dt class="toggle_dd">
			<a href="javascript:void(0)">{$smarty.foreach.loop.iteration}.&nbsp;{$faq.question}</a>
		</dt>
		<dd class="showhide" style="display: none;">
			{$faq.answer}
		</dd>
	{/foreach}
	</dl>
	{/if}
</div>

<div id="sideRight" class="sidebar">
	{include file="$tpl_dir./sidebar.tpl"}
</div>
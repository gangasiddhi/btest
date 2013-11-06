{if $bu_env=='production'}
	{* ANALYTICS - GoogleAnalytics *}
	<script type="text/javascript">
		{literal}
			var _gaq = _gaq || [];
			_gaq.push(['_setAccount', 'UA-21428330-1']);
			_gaq.push(['_setDomainName', 'butigo.com']);
			_gaq.push(['_trackPageview']);
		{/literal}
	</script>

{elseif $bu_env=='development'}
	{* ANALYTICS - GoogleAnalytics *}
	<script type="text/javascript">
		{literal}
			var _gaq = _gaq || [];
			_gaq.push(['_setAccount', 'UA-21428330-2']);
			_gaq.push(['_setDomainName', 'adventureislife.com']);
			_gaq.push(['_trackPageview']);
		{/literal}
	</script>

{/if}

{* LiveChat Integration Start *}
{if $isLiveChatEnabled || $page_name == 'product'}
	<script type="text/javascript">
		{literal}
			var __lc = {};
			__lc.license = 1937821;
			__lc.params = [
				{name: 'customerName', value: {/literal}'{$customerName}'{literal}},
				{name: 'customerId', value: {/literal}'{$customerId}'{literal}}
			];

			(function() {
				var lc = document.createElement('script'); lc.type = 'text/javascript'; lc.async = true;
				lc.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'cdn.livechatinc.com/tracking.js';
				var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(lc, s);
			})();
		{/literal}
	</script>
{/if}
{* LiveChat Integration End *}
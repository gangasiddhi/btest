{if !$content_only}

</div>{* middle *}
</div>{* main-wrapper *}
<div id ="footer-wrapper">
	{$HOOK_FOOTER_TOP}

	<div id="footer">
		<div id="footer-inner">
			<div id="footer-top">
				{$HOOK_FOOTER}
			</div>
			<div id="questions">
				<img alt="{l s='phone'}" src="{$img_dir}phone.png"/>
				<p>{l s='Questions? Call 1.888.508.1888 Monday - Friday 8 a.m. - 5 p.m. (Pacific).'}</p>
			</div>
			<p id="copyright">
				{l s='* Within the contiguous United States. Excludes APO / FPO addresses. | '}&copy; {$copy_year} <a href="{if isset($HOOK_JOIN_NOW) && $HOOK_JOIN_NOW && !$logged} {$link->getPageLink('surveyvsregister.php')} {else} {$link->getPageLink('index.php')} {/if}">{l s='ShoeDazzle.com.'}</a> {l s='All rights reserved.'}
			</p>
		</div>
	</div>
</div>


<div class="hidden">{*a container for all footer javascripts*}
{if $bu_env=='production'}
	{* ANALYTICS - GoogleAnalytics *}
	<script type="text/javascript">
	{literal}
	  (function() {
		var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
		ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
		var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	  })();
	{/literal}
	</script>

	{* Google Analytics updated code*}
	<script type="text/javascript">
		{literal}
			 (function() {
				var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
				ga.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'stats.g.doubleclick.net/dc.js';
				var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
			  })();
		{/literal}
	</script>

    {*facebook Remarketing*}
<script type="text/javascript">
    {literal}
 var fb_param = {};
 fb_param.pixel_id = '6003779981887';
 (function(){
    var fpw = document.createElement('script');
    fpw.async = true;
    fpw.src = '//connect.facebook.net/en_US/fp.js';
    var ref = document.getElementsByTagName('script')[0];
    ref.parentNode.insertBefore(fpw, ref);
 })();
     {/literal}
 </script>
 <noscript><img height="1" width="1" alt="" style="display:none" src="https://www.facebook.com/offsite_event.php?id=6003779981887" /></noscript>

{elseif $bu_env=='development'}
	{* ANALYTICS - GoogleAnalytics *}
	<script type="text/javascript">
	{literal}
	  (function() {
		var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
		ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
		var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	  })();
	{/literal}
	</script>

	{* Google Analytics updated code*}
	<script type="text/javascript">
		{literal}
			 (function() {
				var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
				ga.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'stats.g.doubleclick.net/dc.js';
				var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
			  })();
		{/literal}
	</script>

{/if}

{$HOOK_FOOTER_BOTTOM}

</div>

{/if}{*end if not content_only*}

</body>
</html>

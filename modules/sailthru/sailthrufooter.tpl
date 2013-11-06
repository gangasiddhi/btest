{*SailThru Horizon*}
<script type="text/javascript" src="//ak.sail-horizon.com/horizon/v1.js"></script>
<script type="text/javascript">		
	{literal}
		$(function() {
			if (window.Sailthru) {
				Sailthru.setup({
					domain: 'horizon.mailing.butigo.com'
					{/literal}				
						{if $page_name == 'product' || $page_name == 'category'}
							,concierge: {literal}{{/literal}
											offsetBottom : 200,								
											cssPath	: "{$cssPath}css/sailthru-recommandation.css"	
										{literal}}{/literal}
						{/if}	
					{literal}	
				});
			}
		});
	{/literal}	
</script>

{*Scout Implementation*}
{if $logged}
<script type="text/javascript" src="//ak.sailthru.com/scout/v1.js"></script>
<script type="text/javascript">
	{literal}
		if(!getCookie('scout')){
			var scoutRecommendProducts = '';
			SailthruScout.setup({
				domain: 'horizon.mailing.butigo.com',
				numVisible : 20,
				includeConsumed: true,
				/*filter:'ayakkab',*/
				renderItem: function(item, pos) {	
					if(item.tags[0]){
						scoutRecommendProducts += item.tags[0].replace('pid','')+',';	
						setCookieWithPath('scout', scoutRecommendProducts , null, '/');
					}

					/*To refresh the page only once
					if(!getCookie('scout') || !getCookie('scout-reload')){
						location.reload(true);
						setCookieWithPath('scout-reload', '1' , null, '/');
					}*/					  
				}
			});	
		}
	{/literal}
</script>
{/if}

{*Concierge Implementation*}
{if $page_name == 'product' || $page_name == 'category'}
	<script type="text/javascript">
		var recommandText = "{l s='Recommand Product For You' mod='sailthru' js=1}";
		{literal}
			$(window).load(function(){
				$('.recommendationCategory').html('');	
				$('.recommendationCategory').html(recommandText);
			});
		{/literal}
	</script>
{/if}
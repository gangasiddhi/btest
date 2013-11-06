{if $bu_env=='production' || $bu_env=='development'}
<script type="text/javascript">
	{literal}
		  wl.setContext({
			  "productId" :{/literal}{$id_product}{literal}
		  });
{/literal}
	{if $logged}
		{literal}
			 $('a.rater').click(function(){
				var parameterString = this.href.replace(/.*\?(.*)/, "$1");
				var parameterTokens = parameterString.split("&");
				var parameterList = new Array();
				for (j = 0; j < parameterTokens.length; j++) {
					var parameterName = parameterTokens[j].replace(/(.*)=.*/, "$1"); 
					var parameterValue = parameterTokens[j].replace(/.*=(.*)/, "$1"); 
					parameterList[parameterName] = parameterValue;
				}
				var theratingID = parameterList['q'];
				var theVote = parameterList['j'];
					
			   wl.emitSignal({
					"productId" : theratingID,
					"productRating" :  theVote
				});
			});
	{/literal}
	{/if}
</script>
{/if}
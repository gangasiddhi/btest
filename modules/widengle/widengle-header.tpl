{if $bu_env=='production' || $bu_env=='development'}
{* ANALYTICS - Widengle *}
	{if $page_name != 'stylesurvey'}
		<script src="https://d1zchjxt6i84hj.cloudfront.net/eu/17/wl.js" type="text/javascript"></script>
		<script type="text/javascript">
		  var wl = new Widengle(17, true, "eu");
		</script>
		{if $logged}
		<script type="text/javascript">
			wl.addSignal({literal}{{/literal}
				"language" : "{$language}"
				{if $customerId}
				,"customerId" : {$customerId}
				{/if}
				{if $customerFirstname}
				,"customerFirstname" : "{$customerFirstname}"
				{/if}
				{if $customerLastname}
				,"customerLastname": "{$customerLastname}"
				{/if}
				{if $customerBirthdate}
				,"customerBirthdate": "{$customerBirthdate}"
				{/if}
				{if $customerAge}
				,"customerAge": "{$customerAge}"
				{/if}
				{if $customerShoeSize}
				,"customerShoeSize": {$customerShoeSize}
				{/if}
				{if $customerDressSize}
				,"customerDressSize": "{$customerDressSize}"
				{/if}
				{if $customerCountry}
				,"customerCountry" : "{$customerCountry}"
				{/if}
				{if $customerState}
				,"customerState":"{$customerState}"
				{/if}
				{if $customerCity}
				,"customerCity" :"{$customerCity}"
				{/if}
				{if $customerStreet}
				,"customerStreet" :"{$customerStreet}"
				{/if}
				{if $customerPostalcode}
				,"customerPostalcode" : {$customerPostalcode}
				{/if}
				{if $customerGroup}
				,"customerGroup" : "{$customerGroup}"
				{/if}
			{literal}}{/literal});
		</script>
		{/if}
	{/if}
{/if}
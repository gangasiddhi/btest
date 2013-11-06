{*Payment Page Scripts*}
{if $bu_env=='production' || $bu_env=='development'}
	{if $page_name == 'order-confirmation'}
		<div class="hidden">
			{*Google Code for Purchased Visitor *}
			<script type="text/javascript">
				/* <![CDATA[ */
				var google_conversion_id = 1009336416;
				var google_conversion_label = "GPfwCICejgQQ4ICl4QM";
				var google_custom_params = window.google_tag_params;
				var google_remarketing_only = true;
				/* ]]> */
			</script>
			<script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js"></script>
			<noscript>
				<div style="display:inline;">
					<img height="1" width="1" style="border-style:none;" alt="" src="//googleads.g.doubleclick.net/pagead/viewthroughconversion/1009336416/?value=0&amp;label=GPfwCICejgQQ4ICl4QM&amp;guid=ON&amp;script=0"/>
				</div>
			</noscript>

			{* GA code for E-commerce goals *}
			<script type="text/javascript">
				var tax = parseFloat({$order->total_products_wt}) - parseFloat({$order->total_products});
				_gaq.push(['_addTrans',
					'{$order->id}', /* order ID - required */
					'', /*affiliation optional*/
					'{$order->total_paid}', /* total - required */
					"'"+tax+"'", /* tax optional*/
					'{$order->total_shipping}', /* shipping optional*/
					'', /*city optional*/
					'', /*state optional*/
					''  /*country optional*/
				]);

				{foreach from=$products item=product name=productLoop}
					_gaq.push([
						'_addItem',
						'{$order->id}',
						'{$product.product_attribute_id}',
						'{$product.product_name}',
						'',
						'{$product.product_price_wt}',
						'{$product.product_quantity}'
					]);
				{/foreach}

				_gaq.push(['_trackTrans']);
			</script>

		   {if $bu_env=='production'}
				{* Facebook Convertion tracking *}
				<script type="text/javascript">
					{literal}
						var fb_param = {};
						fb_param.pixel_id = '6007221430687';
						fb_param.value = '0.00';
						(function(){
						  var fpw = document.createElement('script');
						  fpw.async = true;
						  fpw.src = (location.protocol=='http:'?'http':'https')+'://connect.facebook.net/en_US/fp.js';
						  var ref = document.getElementsByTagName('script')[0];
						  ref.parentNode.insertBefore(fpw, ref);
						})();
					{/literal}
				</script>

				<noscript><img height="1" width="1" alt="" style="display:none" src="//www.facebook.com/offsite_event.php?id=6007221430687&amp;value=0" /></noscript>

				{* Google Remarketing Code for Successful Purchase *}
				<script type="text/javascript">
					var google_conversion_id = 1009336416;
					var google_conversion_language = "en";
					var google_conversion_format = "3";
					var google_conversion_color = "ffffff";
					var google_conversion_label = "23JECICC1gIQ4ICl4QM";
					var google_conversion_value = 0;
				</script>

				<script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js"></script>

				<noscript>
					<div style="display:inline;">
						<img height="1" width="1" style="border-style:none;" alt="" src="//www.googleadservices.com/pagead/conversion/1009336416/?label=23JECICC1gIQ4ICl4QM&amp;guid=ON&amp;script=0"/>
					</div>
				</noscript>

			{* Offer Goal Conversion: Butigo | Satış Kampanyası*}
			 <img src="https://tr.rdrtr.com/GL4X6?adv_sub={$order->id|cat:_KO}&amount={$order->total_paid}" width="1" height="1" />
			{* End Offer Goal Conversion*}

			{* start adtriplex affiliate*}
			<noscript><img src="http://partners.adtriplex.com/p.php?gif=1&cid=402&uid={$cookie->id_customer}&rn=1&value={$order->total_paid_real}" /></noscript>
			<script type="text/javascript">
				 var atv_cid = 402;
				 var atv_uid = {$cookie->id_customer};
				 var atv_rn = 1;
				 var atv_sale = {$order->total_paid_real};
				{literal}
					(function() {
						var at = document.createElement('script'); at.type = 'text/javascript'; at.async = true;
						at.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'partners.adtriplex.com/p.js';
						var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(at, s);
					})();
				{/literal}
			</script>
			{* end adtriplex affiliate*}

			{* S--Affiliate Integrations *}
			{* ModaSor Integration *}
			{foreach from=$products item=product name=productLoop}
				<iframe style="width:1px;height:1px;visibility:hidden;"
					src="https://modasor.com/satisSonuc.php?urunID={$product.product_id}&amp;fiyat={$product.product_price_wt}&amp;adet={$product.product_quantity}&amp;siparisID={$order->id}&amp;partnerID=50&amp;satisTipi=1"></iframe>
			{/foreach}

			{* Stilgiyin Integration *}
			<script type="text/javascript">
				var storeName = 'butigo.com';
				var allSum = {$order->total_paid_real};
				var orderId = {$order->id};
				var orderItemsId = [];
				{foreach from=$products item=product name=productLoop}
					orderItemsId.push({$product.product_id});
				{/foreach}
				{literal}
					(function(){
						var at = document.createElement('script');
						at.type = 'text/javascript';
						at.async = true;
						at.src = ('https:' == document.location.protocol? 'https://': 'http://')+'www.stilgiyin.com/js/transaction.js';
						var s = document.getElementsByTagName('script')[0];
						s.parentNode.insertBefore(at,s);
					})();
				{/literal}
			</script>

			{* Stilsos Intergation *}
			{assign var="retailerId" value=123}
			{assign var="password" value=V6S5X5pb6L}
			{assign var="hash" value=$order->id|cat:$retailerId|cat:$password}
			{foreach from=$products item=product name=productLoop}
				<img style="width:1px;height:1px;visibility:hidden;position:absolute;" alt="StilSOS Tracking"
					src="//api.stilsos.com/track/sale?order_no={$order->id}&amp;product_id={$product.product_id}&amp;quantity={$product.product_quantity}&amp;price={$product.product_price_wt}&amp;retailer_id={$retailerId}&amp;hash={$hash|md5}" />
			{/foreach}
			{* F--Affiliate Integrations *}

			{* NanoInteractive Integration *}
			<script type="text/javascript">
				(function(d){ldelim}
					var HEIAS_PARAMS = [];
					{*productQuantities = [],
					productIds = [];*}
					HEIAS_PARAMS.push(['type', 'cpx'], ['ssl', 'force'], ['n', '6451'], ['cus', '17201']);
					HEIAS_PARAMS.push(['pb', '1']);
					HEIAS_PARAMS.push(['order_id', {$order->id}]);
					HEIAS_PARAMS.push(['order_total', {$order->total_paid_real}]);

					{*{foreach from=$products item=product name=productLoop}
						productQuantities.push({$product.product_quantity});
						productIds.push({$product.product_id});
					{/foreach}*}

					{foreach from=$products item=product name=productLoop}
						{assign var="quantity_sum" value= "`$quantity_sum+$product.product_quantity`"}
					{/foreach}

					{*HEIAS_PARAMS.push(['order_article', productIds]);
					HEIAS_PARAMS.push(['order_article', productQuantities]);*}
					HEIAS_PARAMS.push(['order_article','{foreach name=productLoop item=product from=$products}{$product.product_id}{if ! $smarty.foreach.productLoop.last},{/if}{/foreach}']);
					HEIAS_PARAMS.push(['product_quantity', '{$quantity_sum}']);


					if (typeof window.HEIAS == 'undefined') window.HEIAS = [];
					window.HEIAS.push(HEIAS_PARAMS);

					var scr = d.createElement('script');
					scr.async = true;
					scr.src = (d.location.protocol === 'https:' ? 'https:' : 'http:') + '//ads.heias.com/x/heias.async/p.min.js';
					var elem = d.getElementsByTagName('script')[0];
					elem.parentNode.insertBefore(scr, elem);
				{rdelim}(document));
			</script>

			{* S--Convertro Integration *}
			<script type="text/javascript">
					{literal}
						$CVO.push([ 'trackUser', {
							id:'{/literal}{$cookie->id_customer}{literal}',
							attr1:'{/literal}{$totalRealTimeValue}{literal}'
						}]);
					{/literal}
			</script>
			{* F--Convertro Integration *}

			{* --- START of the zanox affiliate HTML code --- *}
			{* --- (The HTML code may not be changed in the sense of faultless functionality!) --- *}
			<script type="text/javascript" src="https://ad.zanox.com/pps/?11787C2101398489&mode=[[1]]&CustomerID=[{$cookie->id_customer}]&OrderID=[{$order->id}]&CurrencySymbol=[[TRY]]&TotalPrice=[[{$product.product_price_wt}]]PartnerID=[{$smarty.cookies.butigo_zanox_id}]"></script>
			<noscript>
				<img src="https://ad.zanox.com/pps/?11787C2101398489&mode=[[2]]&CustomerID=[{$cookie->id_customer}]&OrderID=[{$order->id}]&CurrencySymbol=[[TRY]]&TotalPrice=[[{$product.product_price_wt}]]&PartnerID=[{$smarty.cookies.butigo_zanox_id}]" width="1" height="1" />
			</noscript>
			{php}$_COOKIE['butigo_zanox_id'] = '';{/php}
			{* --- END zanox-affiliate HTML-Code ---*}

			{* Following script for Affiliate - NetModa*}
				<script type="text/javascript">
					var _nma = [];
					_nma.push(['_rate','6']);

					_nma.push(['_amount','{$order->total_paid_real}']); // the amount of order
					_nma.push(['_unique_id', '{$order->id}']); // the ID of order
					{literal}
						(function(){
						var nm = document.createElement('script'); nm.type = 'text/javascript'; nm.async = true;
						nm.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'partners.netmoda.com/pixel.js';
						var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(nm, s);
						})();
					{/literal}
				</script>
			{* End of Affiliate - NetModa*}
			
			{*Social Ads Start*}
				<script language="javascript" src="https://secure.77tracking.com/77Tracking.js"></script> 
				<script language="javascript"> 
					fn77TPageHit('10429867');
					fn77TTransaction('26325', 'orderid={$order->id}&value={$order->total_paid_real}&quantity1={$products|@count}');
				 </script> 
				<noscript>
					<iframe src="https://secure.77tracking.com/PageHitAndAction.ashx?website=10429867&action=26325&transaction=orderid%3D{$order->id}%26value%3D{$order->total_paid_real}%26quantity1%3D{$products|@count}" width="0" height="0" style="border:none 0px white; width:0px; height:0px; position:absolute; top:0px; left:0px;"></iframe>
				</noscript>
			{*Social Ads End*}
			
			{/if}
		</div>
	{/if}
{/if}


{*Other Scripts*}
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

	{*Crazy Egg*}
	{*if $page_name == "lookbook"*}
		<script type="text/javascript">
			{literal}
				setTimeout(function(){
					var a=document.createElement("script");
					var b=document.getElementsByTagName("script")[0];
					a.src=document.location.protocol+"//dnn506yrbagrg.cloudfront.net/pages/scripts/0014/1528.js?"+Math.floor(new Date().getTime()/3600000);
					a.async=true;a.type="text/javascript";b.parentNode.insertBefore(a,b)
				}, 1);
			{/literal}
		</script>
	{*/if*}

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

 	<noscript>
		<img height="1" width="1" alt="" style="display:none" src="https://www.facebook.com/offsite_event.php?id=6003779981887" />
	</noscript>

	{$HOOK_FOOTER_BOTTOM}

	{*Pinterest Script*}
	{if $page_name == 'product'}
		<script type="text/javascript" src="//assets.pinterest.com/js/pinit.js"></script>
	{/if}

	{*Adroll Start*}
	{if $page_name == 'order-confirmation'}
		<script type="text/javascript">
			adroll_conversion_value_in_dollars = {$order->total_paid_real};
			adroll_custom_data = {literal} { {/literal}
						"ORDER_ID": "{$order->id}" ,
						"USER_ID": "{$cookie->email}"
						{literal} };{/literal}
		</script>

		<script type="text/javascript">
			{literal}
				adroll_adv_id = "T7565HOA4NDZLOED3CIXCT";
				adroll_pix_id = "DPCQVRR5WNFGHG7ANHTUZP";
				(function () {
					var oldonload = window.onload;
					window.onload = function(){
						__adroll_loaded=true;
						var scr = document.createElement("script");
						var host = (("https:" == document.location.protocol) ? "https://s.adroll.com" : "http://a.adroll.com");
						scr.setAttribute('async', 'true');
						scr.type = "text/javascript";
						scr.src = host + "/j/roundtrip.js";
						((document.getElementsByTagName('head') || [null])[0] ||
							document.getElementsByTagName('script')[0].parentNode).appendChild(scr);
						if(oldonload){
							oldonload()
						}
					};
				}());
			{/literal}
		</script>
	{/if}
	{*AdRoll End*}

	{*BEGIN DIGITOUCH TAG BOTTOM CODE*}
	<script type="text/javascript">
		{literal}
			if(typeof superT!="undefined"){if(typeof superT.b=="function"){superT.b();}}
		{/literal}
	</script>
	{*Do NOT remove the following <script>...</script> tag: SuperTag requires the following as a separate <script> block*}
	<script type="text/javascript">
		{literal}
			if(typeof superT!="undefined"){if(typeof superT.b2=="function"){superT.b2();}}
		{/literal}
	</script>
	{*END DIGITOUCH TAG BOTTOM CODE*}

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

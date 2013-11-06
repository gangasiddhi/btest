<script>
{*Facebook*}
function postToFeed() {literal}{{/literal}
var obj = {literal}{{/literal}
    method: 'feed',
    link: '{$link->getPageLink('stylesurvey.php')}',
    picture: '{$img_ps_dir}survey/{$style_result}.jpg',
    name: 'Benim Tarzım {$styleHeadline}',
    caption: '{$link->getPageLink('index.php')}',
    description: 'Butigo\'da her ayakkabı tarzıma göre hazırlanıyor. Sen de anketini oluştur tarzını belirle.',
{literal}}{/literal};

function callback(response) {literal}{{/literal}
    document.getElementById('msg').innerHTML = "Post ID: " + response['post_id'];
    if (response && response['post_id']) {literal}{{/literal}
        window.close();
    {literal}}{/literal}
{literal}}{/literal}

FB.ui(obj, callback);
{literal}}{/literal}
{*Facebook*}
</script>

<div id="style-survey-success">
        {*Style Survey Links over the style survey complete image*}
        <div id="social-network" class="survey-social-link">
            <ul>
                    <li><h4>{l s='SHARE IT'}</h4></li>
                    <li id="fb">
                        <a title="{l s='Share on Facebook'}" target="_blank" href="" onclick='postToFeed(); return false;'>{l s='Share on Facebook'}</a>
                    </li>
                    <li id="tt">
                        <a href="" {*href="http://twitter.com/share?url={$link->getPageLink('stylesurvey.php')|urlencode}&text=@{l s='butigocom tarzımı belirledi'}: {$styleHeadline}{l s='. Butigo.com\'da sen de stilini oluştur.'}&count=none"*}
                           onclick='window.open("http://twitter.com/share?url=http://bit.ly/1dCJSwG&text=@{l s='butigocom tarzımı belirledi'}: {$styleHeadline}{l s='. Butigo.com\'da sen de stilini oluştur.'}&count=none", "{l s='Twitter share'}", "height=450, width=550, resizable=0"); return false;'
			   target="_blank" rel="nofollow" title="{l s='Share on Twitter'}">{l s='Share on Twitter'}
			</a>
                    </li>
            </ul>
        </div>
        {*Style Survey Complete Image*}
        <img src="{$img_ps_dir}survey/{$style_result}.jpg" alt="{l s='you’re one step closer'}" usemap="#styleSurveyContinue"/>

        {*Image Mapping for the Style survey continuebutton*}
        <map name="styleSurveyContinue">
            <area shape="rect" alt="{l s='Continue'}" {if $cart->getProducts()|@count >= 1 } href="{$link->getPageLink('order.php')}?step=2" {elseif isset($waiting_room)} href="{$link->getPageLink('showroom.php')}" {else} href="{$link->getPageLink('lookbook.php')}" {/if} coords="556, 500 , 395, 455">
        </map>
</div>

{if $bu_env=='production' || $bu_env=='development'}
<div class="hidden">
	{* GA - grouping of customers *}
	<script type="text/javascript">
	{if isset($customer_group)}
		_gaq.push(['_setCustomVar', 1, 'Customer Group', '{$customer_group}', 1]);
	{/if}
		_gaq.push(['_setCustomVar', 2, 'Customer Join Month', '{$customer_join_month}', 1]);
		_gaq.push(['_setCustomVar', 3, 'Customer Join Year', '{$customer_join_year}', 1]);
	</script>

	{if $bu_env=='production'}

    {* ANALYTICS - Offer Goal Conversion: Butigo Üyelik*}
    <img src="https://tr.rdrtr.com/GL5FI" width="1" height="1" />
    {*End Offer Goal Conversion *}

	{* ANALYTICS - Optim.al Conversion Tracking *}
	<img width="1" height="1" src="http://t.orbengine.com/cv?co=1395&am=0.00"/>

	{* ANALYTICS - Social Ads Tool *}
	<script language="javascript" src="http://www.77tracking.com/77Tracking.js"></script>
	<script language="javascript"> fn77TPageHit('75bcf01e'); fn77TAction('18233'); </script>
	<noscript><iframe src="http://www.77tracking.com/PageHitAndAction.ashx?website=75bcf01e&action=18233&transaction=" width="0" height="0" style="border:none 0px white; width:0px; height:0px; position:absolute; top:0px; left:0px;"></iframe></noscript>

	{* Google Remartketing Code for Registered as a User *}
	<script type="text/javascript">
		/* <![CDATA[ */
		var google_conversion_id = 1009336416;
		var google_conversion_language = "en";
		var google_conversion_format = "3";
		var google_conversion_color = "ffffff";
		var google_conversion_label = "1DD_CIDk1QIQ4ICl4QM";
		var google_conversion_value = 0;
		/* ]]> */
	</script>
	<script type="text/javascript" src="http://www.googleadservices.com/pagead/conversion.js"></script>
	<noscript>
	<div style="display:inline;">
		<img height="1" width="1" style="border-style:none;" alt="" src="http://www.googleadservices.com/pagead/conversion/1009336416/?label=1DD_CIDk1QIQ4ICl4QM&amp;guid=ON&amp;script=0"/>
	</div>
	</noscript>

	{* Google AdWords Code for Registered as a User *}
	<script type="text/javascript">
		/* <![CDATA[ */
		var google_conversion_id = 1009336416;
		var google_conversion_language = "en";
		var google_conversion_format = "3";
		var google_conversion_color = "ffffff";
		var google_conversion_label = "PLo2CIiB1gIQ4ICl4QM";
		var google_conversion_value = 0;
		/* ]]> */
	</script>
	<script type="text/javascript" src="http://www.googleadservices.com/pagead/conversion.js"></script>
	<noscript>
	<div style="display:inline;">
		<img height="1" width="1" style="border-style:none;" alt="" src="http://www.googleadservices.com/pagead/conversion/1009336416/?label=PLo2CIiB1gIQ4ICl4QM&amp;guid=ON&amp;script=0"/>
	</div>
	</noscript>

        {* Facebook Convertion tracking *}
        <script type="text/javascript">
        {literal}
        var fb_param = {};
        fb_param.pixel_id = '6007221135487';
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
        <noscript><img height="1" width="1" alt="" style="display:none" src="https://www.facebook.com/offsite_event.php?id=6007221135487&amp;value=0" /></noscript>
	{/if}
</div>


    {* Stilgiyin Integration *}
    <script type="text/javascript">
        var storeName = 'butigo.com';
        var allSum = '1';
        var orderId = 'signup';
        var orderItemsId = [1];

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
{/if}

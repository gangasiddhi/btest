<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{$lang_iso}"
    xmlns:og="http://ogp.me/ns#"
    xmlns:fb="http://ogp.me/ns/fb#">

    <head>
        <title>{$meta_title|escape:'htmlall':'UTF-8'}</title>

        {if isset($page_name) AND $page_name == 'product'}

			{assign var="shoesTitle" value="- Bayan Ayakkabı Modelleri - Butigo.com"}
			{assign var="handbagsTitle" value="- Bayan Çanta Modelleri - Butigo.com"}
			{assign var="jewelryTitle" value="- Takı Modelleri - Butigo.com"}
			{assign var="shoesFirst" value="Sana özel bayan ayakkabı modellerini keşfet"}
			{assign var="shoesSecond" value=", ünlü tasarımcılar tarfından sana özel tasarlanmış ayakkabılardan sadece bir tanesi."}
			{assign var="handbagsFirst" value="Sana özel çanta modellerini keşfet"}
			{assign var="handbagsSecond" value=", ünlü tasarımcılar tarfından sana özel tasarlanmış çantalardan sadece bir tanesi."}
			{assign var="jewelryFirst" value="Sana özel takı modellerini keşfet"}
			{assign var="jewelrySecond" value=", ünlü tasarımcılar tarfından sana özel tasarlanmış takılardan sadece bir tanesi."}
			{assign var="shoesKeywords" value="ayakkabı, ayakkabı modelleri, bayan ayakkabı"}
			{assign var="handbagsKeywords" value="çanta, canta, çanta modelleri, bayan çanta, kol çantası, el çantaları"}
			{assign var="jewelryKeywords" value="takı, takı modelleri, takı trendi, takı aksesuar, taki, takı tasarım, takılar"}

            <meta property="og:title" content="{l s='SİZCE BANA YAKIŞIR MI?'}"/>
            <meta property="og:url" content="{$product_url}"/>
            <meta property="og:type" content="product"/>
            <meta property="og:description" content="{l s='Merhaba, Butigo\'da Ivana Sert benim için bu ayakkabıyı seçti. Sizce bana nasıl gider?'}"/>
            <meta property="og:image" content="{$fb_image_host}{$link->getImageLink($product->link_rewrite, $cover.fb_image, 'prodfacebook')}"/>
            <meta property="og:site_name" content="{$shop_name|escape:'htmlall':'UTF-8'}"/>
            <meta property="fb:app_id" content="{$app_id}"/>

			{if isset($product->category) AND $product->category == 'shoes' || $product->category == 'ayakkabi' || $product->category == 'bayan-ayakkabi-modelleri'}
				<meta name="title" content="{$product->name}{' '}{$shoesTitle|escape:html:'UTF-8'}" />
				<meta name="description" content="{$shoesFirst|escape:html:'UTF-8'}{' '}{$product->name|escape:html:'UTF-8'}{$shoesSecond|escape:html:'UTF-8'}" />
				<meta name="keywords" content="{$shoesKeywords|escape:html:'UTF-8'}{','}{$productSubcategory|escape:html:'UTF-8'}{','}{$product->name|escape:html:'UTF-8'}"/>
			{elseif isset($product->category) AND $product->category == 'canta' || $product->category == 'bayan-canta-modelleri'}
				<meta name="title" content="{$product->name}{' '}{$handbagsTitle|escape:html:'UTF-8'}" />
				<meta name="description" content="{$handbagsFirst|escape:html:'UTF-8'}{' '}{$product->name|escape:html:'UTF-8'}{$handbagsSecond|escape:html:'UTF-8'}" />
				<meta name="keywords" content="{$handbagsKeywords|escape:html:'UTF-8'}{', '}{$product->name|escape:html:'UTF-8'}"/>
			{elseif isset($product->category) AND $product->category == 'ayakkabi-aksesuarlari'}
				<meta name="title" content="{$product->name}{' '}{$jewelryTitle|escape:html:'UTF-8'}" />
				<meta name="description" content="{$jewelryFirst|escape:html:'UTF-8'}{$product->name|escape:html:'UTF-8'}{$jewelrySecond|escape:html:'UTF-8'}" />
				<meta name="keywords" content="{$jewelryKeywords|escape:html:'UTF-8'}{', '}{$product->name|escape:html:'UTF-8'}" />
			{/if}
			{*<meta name="keywords" content="{$product->name}{if isset($product->category)}{$product->category|escape:html:'UTF-8'},{/if}{if isset($features)}{foreach from=$features item=feature name=features}{$feature.value|escape:'htmlall':'UTF-8'}{if !$smarty.foreach.features.last},{/if}{/foreach}{/if}{if isset($product->price)},{$product->getPrice(true, $smarty.const.NULL, 2)}{/if}" />*}

            <link rel="image_src" href="{$fb_image_host}{$link->getImageLink($product->link_rewrite, $cover.id_image, 'prodthumb')}" />
        {else}
            <meta property="og:title" content="{$meta_title|escape:'htmlall':'UTF-8'}"/>
            {if isset($canonical_url)}
                <meta property="og:url" content="{$canonical_url}"/>
            {else}
                {if $page_name == 'index' OR $page_name == 'landing'}
                    <meta property="og:url" content="{$base_dir}"/>
                {/if}
            {/if}
            {if $page_name == 'index' OR $page_name == 'landing'}
                <meta property="og:type" content="website"/>
            {else}
                <meta property="og:type" content="article"/>
            {/if}
            {if isset($meta_description) AND $meta_description}
                <meta property="og:description" content="{$meta_description|escape:html:'UTF-8'}"/>
            {/if}
            <meta property="og:image" content="{$img_ps_dir}new_fb_refer3.jpg"/>
            <meta property="og:site_name" content="{$shop_name|escape:'htmlall':'UTF-8'}"/>
            <meta property="fb:app_id" content="{$app_id}"/>

            <meta name="title" content="{$meta_title|escape:html:'UTF-8'}" />
            {if isset($meta_description) AND $meta_description}
                <meta name="description" content="{$meta_description|escape:html:'UTF-8'}" />
            {/if}
			{if isset($meta_keywords) AND $meta_keywords}
				<meta name="keywords" content="{$meta_keywords|escape:html:'UTF-8'}" />
			{/if}

            <link rel="image_src" href="{$img_ps_dir}new_fb_refer3.jpg" />
        {/if}

        <meta http-equiv="Content-Type" content="application/xhtml+xml; charset=utf-8" />
        <meta name="robots" content="{if isset($nobots)}no{/if}index,follow" />

        <link rel="icon" type="image/vnd.microsoft.icon" href="{$img_ps_dir}favicon.ico?{$img_update_time}" />
        <link rel="shortcut icon" type="image/x-icon" href="{$img_ps_dir}favicon.ico?{$img_update_time}" />

        {if isset($page_name)}
            {if $page_name != 'order' AND $page_name != 'shipping' AND $page_name != 'cart' AND $page_name != 'address' AND $page_name != 'addresses' AND $page_name != 'history' AND $page_name != 'authentication' AND $page_name != 'waiting'}
                <link href='//fonts.googleapis.com/css?family=Oswald:400,700&subset=latin,latin-ext' rel='stylesheet' type='text/css'>
            {/if}
        {/if}

        {if isset($css_files)}
            {foreach from=$css_files key=css_uri item=media}
                <link href="{$css_uri}" rel="stylesheet" type="text/css" media="{$media}" />
            {/foreach}
        {/if}

        <script type="text/javascript">
            var baseDir = '{$content_dir}';
            var static_token = '{$static_token}';
            var token = '{$token}';
            var priceDisplayPrecision = {$priceDisplayPrecision*$currency->decimals};
            var priceDisplayMethod = {$priceDisplay};
            var roundMode = {$roundMode};
        </script>

        {if isset($js_files)}
            {foreach from=$js_files item=js_uri}
                <script type="text/javascript" src="{$js_uri}"></script>
            {/foreach}
        {/if}

        {$HOOK_HEADER}

		{$HOOK_HEADER_EXTERNAL_SCRIPTS}
    </head>

    <body {if $page_name}id="{$page_name|escape:'htmlall':'UTF-8'}"{/if}
        class="{if $logged || ($show_site && $page_name != 'authentication' && $page_name != 'stylists' && $page_name != 'stylesurvey' && $page_name != 'cms' && $page_name != 'faqs' && $page_name != 'testimonials' && $page_name != 'landing')}online{else}offline{/if} {if $has_title}page-title{/if}">

		{if $page_name == 'landing' || $page_name == 'product' || $page_name == 'lookbook' || $page_name == 'showroom' || $page_name == 'stylesurvey_complete' || $page_name == 'logged-out' || $page_name == 'authentication'}
        {* Loading facebook script for facebook features *}
			<div id="fb-root"></div>
			<script>
				{literal}
					window.fbAsyncInit = function() {
						FB.init({
							appId      : {/literal}{$app_id}{literal}, // App ID  alert('hi');
							channelUrl : '//{/literal}{$base_dir}{literal}/channel.html', // Channel File
							status     : true, // check login status
							cookie     : true, // enable cookies to allow the server to access the session
							xfbml      : true  // parse XFBML
						});
					};

					(function(d, s, id) {
						var js, fjs = d.getElementsByTagName(s)[0];
						if (d.getElementById(id)) return;
						js = d.createElement(s); js.id = id;
						js.src = "//connect.facebook.net/tr_TR/all.js#xfbml=1&appId={/literal}{$app_id}{literal}";
						fjs.parentNode.insertBefore(js, fjs);
					}(document, 'script', 'facebook-jssdk'));
				{/literal}
			</script>
		{/if}

		{if $logged}
		   {*Google Code for Registered Memebers *}
		   <script type="text/javascript">
			   /* <![CDATA[ */
			   var google_conversion_id = 1009336416;
			   var google_conversion_label = "Nf-HCIidjgQQ4ICl4QM";
			   var google_custom_params = window.google_tag_params;
			   var google_remarketing_only = true;
			   /* ]]> */
		   </script>
		   <script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js">
		   </script>
		   <noscript>
		   <div style="display:inline;">
			   <img height="1" width="1" style="border-style:none;" alt="" src="//googleads.g.doubleclick.net/pagead/viewthroughconversion/1009336416/?value=0&amp;label=Nf-HCIidjgQQ4ICl4QM&amp;guid=ON&amp;script=0"/>
		   </div>
		   </noscript>
		{/if}

        {if $bu_env=='production'}
            <div class="hidden">
                {* ANALYTICS - KissInsights *}
                <script type="text/javascript">var _kiq = _kiq || [];</script>
                <script type="text/javascript" src="//s3.amazonaws.com/ki.js/15039/2NP.js" async="true"></script>

                {if $logged}
                    <script type="text/javascript" charset="utf-8">
                        _kiq.push(['identify', '{$customerName}']);
                    </script>
                {/if}

                {* ANALYTICS - Google Remarketing Code for Visitors *}
                {if $page_name && !$logged}
                    {if $page_name == 'index' OR $page_name == 'landing' OR $page_name == 'stylesurvey' OR $page_name == 'product'}
                        <script type="text/javascript">
                            var google_conversion_id = 1009336416;
                            var google_conversion_language = "en";
                            var google_conversion_format = "3";
                            var google_conversion_color = "ffffff";
                            var google_conversion_label = "j7JLCJDi1QIQ4ICl4QM";
                            var google_conversion_value = 0;
                        </script>

                        <script type="text/javascript" src="//www.googleadservices.com/pagead/conversion.js"></script>

                        <noscript>
                            <div style="display:inline;">
                                <img height="1" width="1" style="border-style:none;" alt=""
                                    src="//www.googleadservices.com/pagead/conversion/1009336416/?label=j7JLCJDi1QIQ4ICl4QM&amp;guid=ON&amp;script=0"/>
                            </div>
                        </noscript>
                    {/if}
                {/if}
            </div>
        {/if}

        {include file="$tpl_dir./errors.tpl"}

        {if ! $content_only}
			{if $bu_env=='production'}
				{*BEGIN DIGITOUCH TAG TOP CODE*}
				<script type="text/javascript">
					var superT_dcd=new Date();
					document.write("\x3Cscr"+"ipt type=\"text/javascript\" src=\"" + "//c.supert.ag/digitouch-dijital-pazarlama/digitouch/" + "supertag.js?_dc="+Math.ceil(superT_dcd.getUTCMinutes()/5,0)*5+superT_dcd.getUTCHours().toString()+superT_dcd.getUTCDate()+superT_dcd.getUTCMonth()+superT_dcd.getUTCFullYear()+"\"\x3E\x3C/scr"+"ipt\x3E");
				</script>
				{*Do NOT remove the following <script>...</script> tag: SuperTag requires the following as a separate <script> block *}
				<script type="text/javascript">
					{literal}
						if(typeof superT!="undefined"){
							if(typeof superT.t=="function"){
								superT.t();
							}
						}
					{/literal}
				</script>
				{*END DIGITOUCH TAG TOP CODE*}
		    {/if}

            <div id ="main-wrapper">
				{if $page_name == 'authentication'}
					<div id="loginbg">
				{/if}
				{if $page_name == 'ozgur-masur'}
						<div id="ozgur_bgcolor">
							<div id="ozgur_bgimage">
						{/if}
                <div id="outer-wrapper">
                    <div id="wrapper">
                        <div id="header{if $logged}_logged{/if}">
                        <div id="header-wrapper">
                            <div itemscope itemtype="http://schema.org/Organization" id ="logo">
								<a href="{if isset($HOOK_JOIN_NOW) && $HOOK_JOIN_NOW && ! $logged}{$link->getPageLink('surveyvsregister.php')}{elseif !$logged}{$link->getPageLink('logged-out.php')}{else}{$link->getPageLink('index.php')}{/if}"
									title="{$shop_name|escape:'htmlall':'UTF-8'}"><span itemprop="name">{$shop_name|escape:'htmlall':'UTF-8'}</span></a>
								<meta itemprop="logo" content="{$base_dir}themes/butigo/img/logo-mko.png" />
                                {if $BLOG_ENABLED}
                                    <div id="blog-link">
                                        <a href="/blog" title="{l s='Butigo Blog'}" target="_blank">{l s='Butigo Blog'}</a>
                                    </div>
                                {/if}
                            </div>

                            <div id="header_right">
                                {$HOOK_TOP}
                            </div>

                            {$HOOK_SHOWROOM_NAVIGATION}
                        </div>
                        </div>

                        <!-- middle part -->
                        <div itemscope itemtype="http://data-vocabulary.org/Product" id="middle">
        {/if}


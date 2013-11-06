<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{$lang_iso}"
    xmlns:og="http://ogp.me/ns#"
    xmlns:fb="http://ogp.me/ns/fb#">

    <head>
        <title>{$meta_title|escape:'htmlall':'UTF-8'}</title>

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


        <meta http-equiv="Content-Type" content="application/xhtml+xml; charset=utf-8" />
        <meta name="robots" content="{if isset($nobots)}no{/if}index,follow" />

        <link rel="icon" type="image/vnd.microsoft.icon" href="{$img_ps_dir}favicon.ico?{$img_update_time}" />
        <link rel="shortcut icon" type="image/x-icon" href="{$img_ps_dir}favicon.ico?{$img_update_time}" />

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
    </head>

    <body {if $page_name}id="{$page_name|escape:'htmlall':'UTF-8'}"{/if}
        class="offline {if $has_title}page-title{/if}">


        {* Loading facebook script for facebook features *}
        <div id="fb-root"></div>

        <script>
            {literal}
                (function(d, s, id) {
                    var js, fjs = d.getElementsByTagName(s)[0];
                    if (d.getElementById(id)) return;
                    js = d.createElement(s); js.id = id;
                    js.src = "//connect.facebook.net/tr_TR/all.js#xfbml=1&appId={/literal}{$app_id}{literal}";
                    fjs.parentNode.insertBefore(js, fjs);
                }(document, 'script', 'facebook-jssdk'));
            {/literal}
        </script>

        {if $bu_env=='production'}
            <div class="hidden">
                {* ANALYTICS - Social Ads Tool *}
                {if $page_name}
                    {if $page_name == 'index' OR $page_name == 'landing'}
                        <script language="javascript" src="//www.77tracking.com/77Tracking.js"></script>
                        <script language="javascript">fn77TPageHit('75bcf01e');</script>
                        <noscript>
                            <iframe src="//www.77tracking.com/PageHit.ashx?website=75bcf01e" width="0" height="0"
                                style="border:none 0px white; width:0px; height:0px; position:absolute; top:0px; left:0px;"></iframe>
                        </noscript>
                    {/if}
                {/if}

                {* ANALYTICS - KissInsights *}
                <script type="text/javascript">var _kiq = _kiq || [];</script>
                <script type="text/javascript" src="//s3.amazonaws.com/ki.js/15039/2NP.js" async="true"></script>

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

        <div id="header_wrap">
            <div id="header">
                <div class="header_left">
                    <div id ="logo">
						<a href="{$link->getPageLink('logged-out.php')}" title="{$shop_name|escape:'htmlall':'UTF-8'}">{$shop_name|escape:'htmlall':'UTF-8'}</a>
                    </div>
                </div>

                <div class="header_right">
                    <ul class="nav-menu">
                        <li><a href="//www.youtube.com/watch?v=VN5NQbnCI8A&autoplay=1&rel=0" class="cms_14 fbox-hiw-button iframe" onclick="return false">{l s='How it Works'}</a></li>
                        {*<li {if $page_name == 'stylists'}class="current"{/if}><a  href="{$link->getPageLink('stylists.php')}" class="stylists" title="{l s='My Stylists' mod='blockheaderlinks'}">{l s='My Stylists'}</a></li>*}
                        <li {if $page_name == 'authentication'}class="current"{/if}><a href="{$link->getPageLink('authentication.php')}" class="sign_up" title="{l s='Sign In' mod='blockheaderlinks'}">{l s='Sign In'}</a></li>
                    </ul>
                </div>
            </div>
        </div>

        {if ! $content_only}
        <div id ="main-wrapper">

            {* middle part *}
            <div id="middle">
                {$HOOK_HOME_LOGGED_OUT}
        {/if}


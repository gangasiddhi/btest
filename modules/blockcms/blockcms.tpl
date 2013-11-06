{*The template displays links in the header and the footer*}
{if $block == 1}
<script type="text/javascript">
{if !$logged}
	var show_get_started = 1;
{else}
	var show_get_started = 0;
{/if}
</script>
{if !$logged}
	{foreach from=$cms_titles key=cms_key item=cms_title}
		<div id="header_links" class="top-links">
			{*<h4><a href="{$cms_title.category_link}">{if !empty($cms_title.name)}{$cms_title.name}{else}{$cms_title.category_name}{/if}</a></h4>*}
			<ul>
				{*foreach from=$cms_title.categories item=cms_page}
					{if isset($cms_page.link)}<li class="bullet"><b style="margin-left:2em;">
					<a href="{$cms_page.link}" title="{$cms_page.name|escape:html:'UTF-8'}">{$cms_page.name|escape:html:'UTF-8'}</a>
					</b></li>{/if}
				{/foreach*}
				<li class="item_14"><a href="http://www.youtube.com/watch?v=VN5NQbnCI8A&autoplay=1&rel=0{*$link_hiw_slideshow*}" class="cms_14 fbox-hiw-button iframe">{l s='nasıl calışıyor' mod='blockheaderlinks'}</a></li>
				<li {if $page_name == 'stylists'}class="current"{/if}><a  href="{$link->getPageLink('stylists.php')}" class="stylists" title="{l s='My Stylists' mod='blockheaderlinks'}">{l s='My Stylists' mod='blockheaderlinks'}</a></li>
				{*foreach from=$cms_title.cms item=cms_page}
					{if isset($cms_page.link)}<li class="item_{$cms_page.id_cms} {if $id_cms == $cms_page.id_cms}current{/if}"><a class="cms_{$cms_page.id_cms}" href="{$cms_page.link}" title="{$cms_page.meta_title|escape:html:'UTF-8'}">{$cms_page.meta_title|escape:html:'UTF-8'}</a></li>{/if}
				{/foreach*}
				<li {if $page_name == 'authentication'}class="current"{/if}><a href="{$link->getPageLink('authentication.php')}" class="sign_up" title="{l s='Sign In' mod='blockheaderlinks'}">{l s='Sign In' mod='blockheaderlinks'}</a></li>
				{*if $cms_title.display_store}<li><a href="{$link->getPageLink('stores.php')}" title="{l s='Our stores' mod='blockcms'}">{l s='Our stores' mod='blockcms'}</a></li>{/if*}
			</ul>
		</div>{*end of header_links*}
	{/foreach}
	{* button to be displayed under how it works popup when logged out *}
        {if isset($HOOK_JOIN_NOW) && $HOOK_JOIN_NOW}
            <a id="btn_get_started" class="btnget started buttonmedium blue" onmouseover="$(this).addClass('test')" style="display:none; margin:5px auto 0; width:222px; height:41px" title="{l s='Get Style Profile' mod='blockcms'}" href="{$link->getPageLink('surveyvsregister.php')}">
               {l s='Get Style Profile' mod='blockcms'}
            </a>
        {else}
            <a id="btn_get_started" class="btnget started buttonmedium blue" onmouseover="$(this).addClass('hiw_hover')" onmouseout="$(this).removeClass('hiw_hover')" style="display:none; margin:9px 0 0 0;width:auto;" title="{l s='Get Style Profile' mod='blockcms'}" href="{$link->getPageLink('stylesurvey.php')}">
             {l s='Get Style Profile' mod='blockcms'}   {*<img src="{$img_dir}buttons/btn-get-style-profile.gif" alt="{l s='Style Survey' mod='blockcms'}"/>*}
            </a>
        {/if}
{/if}
{else}
	<div id="footer-links">
	<ul> {* class="block_various_links" id="block_various_links_footer"  *}
		{*{if !$PS_CATALOG_MODE}<li class="first_item"><a href="{$link->getPageLink('prices-drop.php')}" title="{l s='Specials' mod='blockcms'}">{l s='Specials' mod='blockcms'}</a></li>{/if}
		<li class="{if $PS_CATALOG_MODE}first_{/if}item"><a href="{$link->getPageLink('new-products.php')}" title="{l s='New products' mod='blockcms'}">{l s='New products' mod='blockcms'}</a></li>
		{if !$PS_CATALOG_MODE}<li class="item"><a href="{$link->getPageLink('best-sales.php')}" title="{l s='Top sellers' mod='blockcms'}">{l s='Top sellers' mod='blockcms'}</a></li>{/if}
		{if $display_stores_footer}<li class="item"><a href="{$link->getPageLink('stores.php')}" title="{l s='Our stores' mod='blockcms'}">{l s='Our stores' mod='blockcms'}</a></li>{/if}
		<li class="item"><a href="{$link->getPageLink('contact-form.php', true)}" title="{l s='Contact us' mod='blockcms'}">{l s='Contact us' mod='blockcms'}</a></li>
		*}
		<li><a href="{$link->getPageLink('index.php')}" title="{l s='Anasayfa' mod='blockcms'}">{l s='Homepage' mod='blockcms'}</a></li>
		<li><a href="{$link->getPageLink('testimonials.php')}" title="{l s='Testimonials' mod='blockcms'}">{l s='Testimonials' mod='blockcms'}</a></li>
		{foreach from=$cmslinks item=cmslink}
			{if $cmslink.meta_title != ''}
				<li class="item"><a href="{$cmslink.link|addslashes}" title="{$cmslink.meta_title|escape:'htmlall':'UTF-8'}">{$cmslink.meta_title|escape:'htmlall':'UTF-8'}</a></li>
			{/if}
		{/foreach}
		<li><a href="{$link->getPageLink('faqs.php')}" title="{l s='FAQs' mod='blockcms'}">{l s='FAQs' mod='blockcms'}</a></li>
		<li><a href="{$base_dir}blog" title="{l s='Blog' mod='blockcms'}">{l s='Blog' mod='blockcms'}</a></li>
		{*{if $display_poweredby}<li class="last_item">{l s='Powered by' mod='blockcms'} <a href="http://www.prestashop.com">PrestaShop</a>&trade;</li>{/if}*}
	</ul>
	<div class="footer_header" style= "font-weight: bold;color: #7F7F7F; font-size: 1em;padding: 0 12px 0 0;line-height: 24px;">
		{l s='Categories' mod='blockcms'}
	</div>
	<ul>
		<li><a href="{$link->getPageLink('lookbook.php')}" title="{l s='LookBooks' mod='blockcms'}">{l s='LookBooks' mod='blockcms'}</a></li>
		<li><a href="{$link->getCategoryLink($shoe_link_rewrite)}" title="{l s='Shoes' mod='blockcms'}">{l s='Shoes' mod='blockcms'}</a></li>
		{foreach from=$categories key=cat_id item=eachCategory name=catLoop}
			<li><a href="{$eachCategory.link}" title="{l s=$eachCategory.name mod='blockcms'}">{l s=$eachCategory.name mod='blockcms'}</a></li>
		{/foreach}
	</ul>
{/if}

{* Displays an featuredimage on the home page which links to style survey; when logged in links to showroom *}
<div id="image-container">
{*if !$logged}
	<a href="{$link->getPageLink('stylesurvey.php')}" title="{l s='get my style profile' mod='blockfeaturedimage'}">*}
		<img src="{$media_server}{$image}" alt="{l s='what customers say' mod='blockfeaturedimage'}" />
	{*</a>
{else}
	<a href="{$link->getPageLink('showroom.php')}" title="{l s='view showroom' mod='blockfeaturedimage'}">
		<img src="{$image1}" alt="{l s='get my style profile' mod='blockfeaturedimage'}" />
	</a>
{/if*}
</div>{*end of image-container*}
<div id="pink_bar">
	<div id="pink_bar_aligncentre">
		<div class="pink_bar_sub1"><img src="{$img_dir}home/banner.png" alt="{l s='LIKE US'}" {*style="margin-top: 12px;*}></div>
	</div>
	<div id="pink_bar_sub2">
		<div id="fb_like">
			<fb:like href="{$base_dir}" send="false" layout="button_count" show_faces="false" width="125"></fb:like>
		</div>
		{*<div id="fb_count">
               <img src= "{$img_dir}buttons/fblike_count.png"alt=""/>
		</div>*}
		<div id="facepile">
			<img src="{$img_dir}home/facepile.jpg"  alt="{l s='get facebook profile images' mod='blockfeaturedimage'}">
		</div>
	</div>
</div>

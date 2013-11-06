
{if $id_cms == 6 || $id_cms == 10 || $id_cms == 11 || $id_cms == 16}
    {if !$logged}
	<div class="sidebar-block">
            {if isset($HOOK_JOIN_NOW) && $HOOK_JOIN_NOW}
                 <img src= "{$img_ps_dir}survey/banner-sidebar-join-now.png"alt="{l s='Join now'}"/>
               <a href="{$link->getPageLink('surveyvsregister.php')}" class="buttonlarge blue" style="float: left;margin:10px 0 0 0" title="{l s='Get My Style Profile'}">{l s='Get My Style Profile'}
                </a>
            {else}
                <img src="{$img_ps_dir}survey/banner-sidebar-join-now.png" alt="{l s='Fashion Survey'}" />
		<a href="{$link->getPageLink('stylesurvey.php')}" class="buttonlarge blue" style="float: left;margin:10px 0 0 0" title="{l s='Get My Style Profile'}">{l s='Get My Style Profile'}
		</a>
            {/if}
	</div>
    {/if}
{/if}
{*if $id_cms == 16}
	<div class="send-question sidebar-block sidebar-grad">
		<h4>{l s='Send us a Question'}</h4>
		<p>{l s='Style question? While we cannot promise to answer every question, we always love hearing from you!'}</p>
		<a href="{$link->getPageLink('authentication.php')}" title="{l s='Log In'}">
			<img src="{$img_ps_dir}cms/login_btn.png" alt={l s='Log In'}/>
		</a>
	</div>
{/if*}
{if $id_cms == 14}
	{*<div id="hiw-side-video" class="sidebar-block">
		<a class="fbox-youtube" title="{l s='How It Works'}" href="http://www.youtube.com/watch?v=H3-yVpUQgvY&autoplay=1&rel=0">
			<img src="{$img_dir}/videothumbhiw.png" alt="{l s='How it Works'}"/>
			<h4>{l s='How it Works'}</h4>
			<span>{l s='with Stylist Anya Sarre'}</span>
			<span class="watch">{l s='Watch the Video'}</span>
		</a>
	</div>*}
        {if !$logged}
            <div class="sidebar-block">
                {if isset($HOOK_JOIN_NOW) && $HOOK_JOIN_NOW}
                <a href="{$link->getPageLink('surveyvsregister.php')}" title="{l s='Join Now'}">
                        <img src= "{$img_ps_dir}survey/banner-sidebar-join-now.png"alt="{l s='Join now'}"/>
                    </a>
                {else}
                    <a href="{$link->getPageLink('stylesurvey.php')}" title="{l s='Fashion Survey'}">
                            <img src="{$img_ps_dir}site/banner-sidebar.png" alt="{l s='Fashion Survey'}" />
                    </a>
                {/if}
            </div>
        {/if}

{/if}
{if $id_cms == 11 || $id_cms == 14}
	<div class="sidebar-block">
		<fb:like-box href="http://www.facebook.com/butigo" width="305" height="187" show_faces="true" stream="false" header="false"></fb:like-box>
	</div>
{/if}
{if $id_cms == 6 || $id_cms == 10 || $id_cms == 16}
	<div class="sidebar-block">
		<fb:like-box href="http://www.facebook.com/butigo" width="305" height="488" show_faces="true" stream="true" header="false"></fb:like-box>
	</div>
{/if}

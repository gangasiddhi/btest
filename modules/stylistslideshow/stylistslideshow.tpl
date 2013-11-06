<div align="center" style=" padding: 1px 0 0;min-height:{$height}px;overflow: hidden; clear:both">
    <div id="wrap">
    	<div class ="jcarousel-skin-container">
        	<ul {if $no_of_imgs > 1} id="stylist_slide" {/if}>
            	{foreach from=$xml->link item=home_link name=links}
            		<li>
            			{if $home_link->url}
            				<a href='{$home_link->url}' title="{$home_link->desc}">
            			{/if}

            				<img src='{$media_server}{$this_path}{$home_link->img}'alt="{$home_link->desc}"/>

            			{if $home_link->url}
            				</a>
            			{/if}
            		</li>
            	{/foreach}
        	</ul>
    	</div>
    </div>
</div>

{if ! $logged} {*display these only to logged out users*}
    <div id="stylist"style="position: relative;top: 20px;left: 330px;">
        {if isset($HOOK_JOIN_NOW) && $HOOK_JOIN_NOW}
            {$HOOK_JOIN_NOW}
        {else}
            <a href="{$link->getPageLink('stylesurvey.php')}" class="buttonlarge blue" title="{l s='get my style profile'}">
                {l s='get my style profile' mod='stylistslideshow'}
            </a>
        {/if}
    </div>
{/if} {* close if display these only to logged out users*}

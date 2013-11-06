
{if $logged}
	{$HOOK_AFTER_MENU}
	{$HOOK_TOP_SELLERS}
	{$HOOK_HOME_LOGGED_IN}
{else}
    {if isset($HOOK_JOIN_NOW) && $HOOK_JOIN_NOW}
        <a href="{$link->getPageLink('surveyvsregister.php')}" title="{l s='Join Now'}">
            <img src= "{$img_ps_dir}survey/signup-landing-page.jpg"alt="{l s='Join now'}"/>
         </a>
     {else}
	{$HOOK_HOME_LOGGED_OUT}
    {/if}
{/if}

{$HOOK_HOME}

<div class="nav_block">
    {if isset($HOOK_JOIN_NOW) && $HOOK_JOIN_NOW}
        <a href="{$link->getPageLink('surveyvsregister.php')}" title="{l s='Join Now' mod='productblocknav'}">
            <img src= "{$img_ps_dir}survey/prod_logout_nav.gif"alt="{l s='Join now' mod='productblocknav'}"/>
         </a>
    {else}
            <div id="nav_block1">
            <span>{l s='Purchase our latest fashionable shoes Ivana Sert chose just for you' mod='productblocknav'}</span>
        <a href="{$link->getPageLink('stylesurvey.php')}" class="buttonmedium pink" style="margin:10px 10px 0 0;float: right;"title="{l s='get my style profile'}">
            {*<img src="{$img_dir}/product/prod_logout_nav.jpg"  alt="{l s='get my style profile'}" />*}{l s='get my style profile' mod='productblocknav'}
              </a> </div>



    {/if}
</div>

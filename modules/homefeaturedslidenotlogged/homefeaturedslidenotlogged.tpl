{literal}
<script type="text/javascript">
    var bgImage = {/literal}'{$media_server}{$bg_image}';{literal}

    var rand = parseInt(Math.random() * 7, 10);
    rand = rand >= 0 && rand <= 6 ? rand :  0;
    bgImage.replace(/bg_img_0[0-9]/g, 'bg_img_0' + rand);

    $("#main-wrapper").css("background-image", "url('" + bgImage + "')");
</script>

<script>
    function fbLogin(){
        FB.login(function(response) {
            if (response.status) {
                FB.api('/me', function(response) {
                    console.log(response);
                    console.log('Good to see you, ' + response.name + '.');
                    console.log('Good to see you, ' + response.email + '.');
                    window.location ="{/literal}{$link->getPageLink('authentication.php')}{literal}?response="+encodeURIComponent(JSON.stringify(response));

                });
            } else {
                console.log('User cancelled login or did not fully authorize.');
            }
        }, {scope: 'email'});
    }

{/literal}
</script>
{if $emailerror!=''}
    <div class="error message">
        <a id="error-close"></a>
        <div id="er">{l s='ERROR' mod='homefeaturedslidenotlogged'}</div>
        <ol>
        {*foreach from=$emailerror key=k item=error*}
                <li>{$emailerror}</li>
        {*/foreach*}
        </ol>
    </div>
{/if}
<div id="center-container{if isset($show_fb_form)}-fb{elseif isset($ivana_img)}-push-right{/if}">
{if isset($show_fb_form)}
    <div id="reg_form">
        <fb:registration
            fields="{$str}"
            redirect-uri="{$link->getPageLink('fb-stylesurvey.php')}?stp=1"
            width="530">
        </fb:registration>
    </div>
{else}
    <div id="outer_form_div">
        <div id="label_email">
            <label for="email" id="new-email-address-label">Email:</label>
        </div>
        <div id="new-user-email-id">
            <form action="{$link->getPageLink("authentication.php",true)}" method="post" id="new_user_login_form" name="new_user_login_form">
                    {*<label for="email" id="new-email-address-label">Email:</label>*}
                    <input type="text" name="newemail" value="{if isset($smarty.post.email)}{$smarty.post.email|escape:'htmlall'|stripslashes}{/if}" id="new-email-address"/>
                    {if isset($back)}<input type="hidden" class="hidden" name="back" value="{$back|escape:'htmlall':'UTF-8'}" />{/if}
                    {if isset($utm_params)}<input type="hidden" class="hidden" name="utm_params" value="{$utm_params}" />{/if}
                    <input type="submit" id="register-new-email" name="RegisterNewEmail"class="register-new-email{if isset($HOOK_JOIN_NOW) && $HOOK_JOIN_NOW} join_now{/if}" value=""/>
            </form>
        </div>
    </div>

    <img src="{$media_server}{$this_path}assets/new_bg_center_without_input.png" alt="" usemap="#center-image"/>
    <map name="center-image">
        <area shape="rect" coords="196,273,465,233" href="{$link->getPageLink('stylesurvey.php')}" alt="{l s='Style Survey' mod='homefeaturedslidenotlogged'}"/>
        <area shape="rect" coords="240,310,420,282" href="{$link->getPageLink('authentication.php')}" alt="{l s='Sign In' mod='homefeaturedslidenotlogged'}"/>
    </map>
    <span id="or_span">VEYA</span><br/>
    <a href="javascript:void(0)" {if isset($ivana_img)}class ="push_right_fb_login"{/if} onclick="fbLogin();"><img src="{$media_server}{$this_path}assets/facebook.png" /></a>
  {/if}
</div>

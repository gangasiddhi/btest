{* Displays a link to stylesurvey and gives contact information *}
<script type="text/javascript">
{literal}
/* <![CDATA[ */
$(document).ready(function() {
    $("#shopping-cart-top-gad").hover(function(){$(".check-out").css('color', '#f2018a')},
                                        function(){$(".check-out").css('color', '#000000')});
});
/* ]]> */
{/literal}
</script>

{if !$logged}

    <div id="get-started-link">
        {if isset($HOOK_JOIN_NOW) && $HOOK_JOIN_NOW}
            <a href="{$link->getPageLink('surveyvsregister.php')}?stylesurvey=1" class="blockgetstarted black" title="{l s='Join Now' mod='blockgetstarted'}">
                {l s='Join Now'}
            </a>
        {else}
            <a href="{$link->getPageLink('stylesurvey.php')}?stylesurvey=1" class="blockgetstarted black" title="{l s='Get Started Now' mod='blockgetstarted'}">
                {l s='Join Now' mod='blockgetstarted'}
            </a>
        {/if}
    </div>

    <p id="info-text">
        <img src= "{$img_dir}mutluluk_ekibi.gif" alt="{l s='helpdesk' mod='blockgetstarted'}"/><br/>
    </p>

    {if isset($show_site) && $show_site == 1 && $page_name != 'authentication' && $page_name != 'stylists' && $page_name != 'stylesurvey' && $page_name != 'cms' && $page_name != 'faqs' && $page_name != 'testimonials' && $page_name != 'landing'}
        <div id="shopping-cart-top{if $show_site == 1}-gad{/if}">
            {*<img src="{$img_dir}buttons/cart.png" alt="{l s='Your Shopping Cart' mod='blockuserinfo'}" />*}
            <a id ="shopping-cart-top-link" {*if $cart_qties == 0} class="collapsed" {else} class="check-out"{/if*} href="{$link->getPageLink('order.php')}?step=1" title="{l s='Your Shopping Cart' mod='blockgetstarted'}">
                    {*<span class="ajax_cart_quantity{if $cart_qties == 0} collapsed{/if}">{$cart_qties}</span>*}
                    <span class="ajax_cart_product_txt{*if $cart_qties != 1} collapsed{/if*}">{l s='SEPETİM' mod='blockgetstarted'}</span>
                    <!--
                    <span class="ajax_cart_closure{*if $cart_qties == 0} collapsed{/if*}">(</span><span class="ajax_cart_quantity{*if $cart_qties == 0} collapsed{/if*}">0</span><span class="ajax_cart_closure{*if $cart_qties == 0} collapsed{/if*}">)</span>
                    -->
            
                    {*<span class="ajax_cart_product_txt_s{if $cart_qties < 2} collapsed{/if}">{l s='products' mod='blockuserinfo'}</span>*}
            </a>
            {*<span class="ajax_cart_no_product{if $cart_qties > 0} collapsed{/if}" id="shopping-cart-top-span">{l s='SEPETİM' mod='blockgetstarted'} (0)</span>*}
        </div>{*end of shopping-cart-top*}
    {/if}
{/if}
{if $logged}
</div>
{/if}

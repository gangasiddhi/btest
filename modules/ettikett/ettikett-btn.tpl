{*<script type="text/javascript">

    var info_src = "{$img_dir}/order-success-popup.jpg";

    {literal}
        function trigger_info() {
            $.fancybox('<img usemap="#ettikett-link" src="' + info_src + '" alt=""/>', {
                'autoSize': false,
                'width': 950,
                'height': 570,
                'padding': 1,
                'margin': 0,
                'scrolling': 'no',
                'titleShow': false,
                'centerOnScroll': true,
                'hideOnOverlayClick': false,
                'hideOnContentClick': false,
                'overlayColor': '#000',
                'showNavArrows': false
            });
        }

        $(document).ready(function() {
            setTimeout("trigger_info()", 2000);
        });
    {/literal}
</script>*}

{*<map name="ettikett-link">
    <area href="#" onclick="_ettikett();" shape="rect" coords="55, 360, 240, 320" alt="{l s='Share Via Facebook' mod='ettikett'}" title="{l s='Share Via Facebook' mod='ettikett'}" />
    <area href="#" onclick="_ettikett();" shape="rect" coords="140, 360, 435, 320" alt="{l s='Share Via Twitter' mod='ettikett'}" title="{l s='Share Via Twitter' mod='ettikett'}" />
</map>*}
<a href="#"></a>
<div class="ettikett">
    <p>Alışverişini Ettikett'le, bir sonraki alışverişinizde 5 TL indirim kazan!</p>
    <a href="#" onclick="_ettikett();"><img src="{$img_dir}buttons/ettikettle-butigo-butonu.png" alt="{l s='Share Via ettikett' mod='ettikett'}"></a>
</div>    
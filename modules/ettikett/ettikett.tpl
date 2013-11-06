{if $page_name == 'order-confirmation'}
    <script type="text/javascript">
        {literal}
            (function(d){
            var js, id = 'ettikett-jssdk'; if (d.getElementById(id)) {return;}
            js = d.createElement('script'); js.id = id; js.async = true;
            js.src = "//www.ettikett.com/jsapi?key=g325ndcna21lbng6lxf8y3g71u8ax65s&campaignId=3ywklwoucb7qmwn0cl95ttxowr0tjg3c";
            d.getElementsByTagName('head')[0].appendChild(js);
            }(document));
        {/literal}

        var cl_key = 'g325ndcna21lbng6lxf8y3g71u8ax65s';
        var cl_campaignId = '3ywklwoucb7qmwn0cl95ttxowr0tjg3c';
        var cl_redirectUrl = '{$REDIRECT_URL}';
        var cl_banner = true;
    </script>
{/if}

<script type="text/javascript">
{literal}
// <![CDATA[
	 $(document).ready(function() {
		var untilTime = $("#shipping_time").attr("class");
		$("#shipping_time").countdown({
						until: untilTime,
						format: 'HMS',
						compact: true,
						description: ''
         });
	});
//]]>
{/literal}
</script>
<div align="center" class="main_heading">GÜNÜN AYAKKABI KAÇAMAĞI</div>
<div align="center" class="sub_heading">HER GÜN BİR AYAKKABI - ÖZEL FİYAT - ÜCRETSİZ GÖNDERİ</div>
<div align="center" style=" padding: 1px 0 0;height:{$height}px;">    
    <div id="wrap">
        <div class="jcarousel-skin-container">
            <ul {if $no_of_imgs > 1} id="celebrity_slide" {/if}>
                {if $count==0}
                {foreach from=$xml->link item=home_link name=links}
                    {if $current_time <= $to_date_time && $current_time >=$from_date_time}
                    {if $smarty.foreach.links.iteration == 1}
                        <li>
                            {if $home_link->url}
                                <a href='{$base_dir}{$home_link->url}' title="{$home_link->desc}">
                            {/if}

                                <div class="display_d_deal_black_img">
                                    <img src='{$media_server}{$this_path}assets/black_circle.png'alt="{$home_link->desc}"/>
                                </div>
                                <div align="center" class="display_d_deal_text">
                                    <div class="remaining_time">KALAN SÜRE</div>
                                    <div class="countdown_time"><span id="shipping_time" class="{$shipping_time}"></span></div>
                                    <div class="certain_color">Sadece belİrlİ renklerde</div>
                                    {assign var=discount_price value=","|explode:$home_link->prod_disc_price}
                                    {if $discount_price|is_array}
                                        <div class="discount_price">{$discount_price[0]}<sup>{$discount_price[1]}</sup>TL</div>
                                    {else}
                                        <div class="discount_price">{$home_link->prod_disc_price} TL</div>
                                     {/if}

                                    <div class="free_shiping">+ ÜCRETSİZ GÖNDERİ</div>
                                    <div class="normal_text">SEZON FİYATI</div>
                                    <div class="main_price">{$home_link->prod_price} TL</div>                                
                                </div>
                                <img src='{$media_server}{$this_path}{$home_link->img}'alt="{$home_link->desc}"/>

                            {if $home_link->url}
                                </a>
                            {/if}
                        </li>
                    {/if}
                  {/if}
                {/foreach}
                {else}
                    {if $current_time <= $to_date_time && $current_time >=$from_date_time}
                    <li>
                        {if $xml->link->$count->url}
                            <a href='{$base_dir}{$xml->link->$count->url}' title="{$xml->link->$count->desc}">
                        {/if}
                            <div class="display_d_deal_black_img">
                                <img src='{$media_server}{$this_path}assets/black_circle.png'alt="{$home_link->desc}"/>
                            </div>
                            <div align="center" class="display_d_deal_text">
                                <div class="remaining_time">KALAN SÜRE</div>
                                <div class="countdown_time"><span id="shipping_time" class="{$shipping_time}"></span></div>
                                <div class="certain_color">Sadece belİrlİ renklerde</div>
                                {assign var=discount_price value=","|explode:$xml->link->$count->prod_disc_price}
                                {if $discount_price|is_array}
                                    <div class="discount_price">{$discount_price[0]}<sup>{$discount_price[1]}</sup>TL</div>
                                {else}
                                    <div class="discount_price">{$xml->link->$count->prod_disc_price} TL</div>
                                 {/if}
                                 
                                <div class="free_shiping">+ ÜCRETSİZ GÖNDERİ</div>
                                <div class="normal_text">SEZON FİYATI</div>
                                <div class="main_price">{$xml->link->$count->prod_price} TL</div>                                
                            </div>
                            <img src='{$media_server}{$this_path}{$xml->link->$count->img}'alt="{$xml->link->$count->desc}"/>

                        {if $xml->link->$count->url}
                            </a>
                        {/if}
                    </li>
                    {/if}
                {/if}
            </ul>
        </div>
    </div>
</div>

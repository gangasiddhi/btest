<div>
    {if ! $logged}
        <div id="reg_form">
            <fb:registration
                fields="{$str}"
                redirect-uri="{$link->getPageLink('fb-stylesurvey.php')}?stp=1"
                width="530">
            </fb:registration>
        </div>

        {if $etkt_refer}
            <div id="etkt-discount">
                <p>{l s='You just earned 5.00 TL discount' mod='ettikett'}</p>
            </div>
        {/if}
    {/if}

    <div align="center" style=" padding: 1px 0 0;height:{$height}px;overflow: hidden; clear:both">
        <div id="wrap">
            <div class ="jcarousel-skin-container">
                <ul {if $no_of_imgs > 1}id="home-featured-slide-logged"{/if}>
                    {foreach from=$xml->link item=home_link name=links}
                        <li>
                            {if $home_link->url}
                                <a href='{$home_link->url}' title="{$home_link->desc}">
                            {/if}

                                <img src='{$media_server}{$this_path}{$home_link->img}' alt="{$home_link->desc}"/>

                            {if $home_link->url}
                                </a>
                            {/if}
                        </li>
                    {/foreach}
                </ul>
            </div>
        </div>
    </div>
</div>

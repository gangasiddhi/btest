<div align="center" style=" padding: 1px 0 0;height:{$height}px;overflow: hidden; clear:both">
    <div id="wrap">
        <div class="jcarousel-skin-container">
            <ul {if $no_of_imgs > 1} id="cloth_slide" {/if}>
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

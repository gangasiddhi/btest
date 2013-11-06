<div class="nav_block">
	<img src="{$media_server}{$this_path}img/nav_header.gif" class="nav_block_top" alt="{l s='Want to see more?' mod='showroomblocknav'}"/>

	<div class="nav_block_single">
		<img src="{$media_server}{$this_path}img/nav_title1.png" class="nav_block_top_img" alt="{l s='Browse Lookbooks' mod='showroomblocknav'}"/>
		<a href="{$link->getPageLink('lookbook.php')}" title="{l s='Browse Lookbooks' mod='showroomblocknav'}">
			<img src="{$media_server}{$this_path}img/directing-shoe-collection.jpg" class="nav_block_middle_img" alt="{l s='Browse Lookbooks' mod='showroomblocknav'}"/>
		</a>
		<hr/>
		<div class="nav_block_link">
			<a href="{$link->getPageLink('lookbook.php')}" title="{l s='Browse Lookbooks' mod='showroomblocknav'}">{l s='Browse Lookbooks' mod='showroomblocknav'}<span></span></a>
		</div>
	</div>

	<div class="nav_block_single">
		<img src="{$media_server}{$this_path}img/nav_title2.gif" class="nav_block_top_img" alt="{l s='Browse Handbags' mod='showroomblocknav'}"/>
		<a href="{$link->getCategoryLink($hand_link_rewrite)}" title="{l s='Browse Handbags' mod='showroomblocknav'}">
            <img src="{$media_server}{$this_path}img/directing-bag-collection.jpg" class="nav_block_middle_img" alt="{l s='Browse Handbags' mod='showroomblocknav'}"/>
		</a>
		<hr/>
		<div class="nav_block_link">
			<a href="{$link->getCategoryLink($hand_link_rewrite)}" title="{l s='Browse Handbags' mod='showroomblocknav'}">{l s='Browse Handbags' mod='showroomblocknav'}<span></span></a>
		</div>
	</div>

	<div class="nav_block_single">
		<img src="{$media_server}{$this_path}img/nav_title3.gif" class="nav_block_top_img nav_block_fb_link" alt="{l s='Join Us on Facebook' mod='showroomblocknav'}"/>
		<div class="fb-fanbox">
			<fb:like-box href="http://www.facebook.com/butigo" width="290" height="174" show_faces="true" stream="false" header="false"></fb:like-box>
		</div>
		<hr/>
		<div class="nav_block_link">
			<a href="http://www.facebook.com/butigo" target="_blank" title="{l s='Join Us on Facebook' mod='showroomblocknav'}">{l s='Join Us on Facebook' mod='showroomblocknav'}<span></span></a>
		</div>
	</div>
</div>

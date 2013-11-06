<div class="nav_block">
  <img src="{$media_server}{$this_path}slide/nav_header.gif" class="nav_top" alt="{l s='Want to see more?' mod='lookbookblocknav'}"/>
{if !isset($no_butigim_link)}
  <div class="nav_block_single">
		<img src="{$media_server}{$this_path}slide/nav_title1_new.png   " class="nav_block_top_img" alt="{l s='Go to My Showroom' mod='lookbookblocknav'}"/>
		<a href="{$link->getPageLink('showroom.php')}" title="{l s='Go to My Showroom' mod='lookbookblocknav'}">
			<img src="{$media_server}{$this_path}slide/directing-shoe-collection.jpg" class="nav_block_middle_link" alt="{l s='Go to My Showroom' mod='lookbookblocknav'}"/>
		</a>
		<div class="nav_hr">
			<hr/>
		</div>
		<div class="nav_block_link">
			<a href="{$link->getPageLink('showroom.php')}" title="{l s='Go to My Showroom' mod='lookbookblocknav'}">{l s='Go to My Showroom' mod='lookbookblocknav'}<span></span></a>
		</div>
  </div>
{/if}
  <div class="nav_block_single" id="nav_auto_padding">
		<img src="{$media_server}{$this_path}slide/nav_title2.gif" class="nav_block_top_img" alt="{l s='Browse Handbags' mod='lookbookblocknav'}"/>
		<a href="{$link->getCategoryLink($hand_link_rewrite)}" title="{l s='Browse Handbags' mod='lookbookblocknav'}">
		  <img src="{$media_server}{$this_path}slide/directing-bag-collection.jpg" class="nav_block_middle_link" alt="{l s='Browse Handbags' mod='lookbookblocknav'}"/>
		</a>
		<div class="nav_hr">
			<hr/>
		</div>
		<div class="nav_block_link">
			<a href="{$link->getCategoryLink($hand_link_rewrite)}" title="{l s='Browse Handbags' mod='lookbookblocknav'}">{l s='Browse Handbags' mod='lookbookblocknav'}<span></span></a>
		</div>
  </div>

  <div class="nav_block_single">
	<div style ="margin:0 0 0 10px">
        <img src="{$media_server}{$this_path}slide/nav_title3.gif" class="nav_block_top_img nav_block_fb_link" alt="{l s='Join Us on Facebook' mod='lookbookblocknav'}"/>
		<div class="fb-fanbox">
			<fb:like-box href="http://www.facebook.com/butigo" width="290" height="174" show_faces="true" stream="false" header="false"></fb:like-box>
		</div>
		<div class="nav_hr">
			<hr/>
		</div>
		<div class="nav_block_link">
			<a href="http://www.facebook.com/butigo" target="_blank" title="{l s='Join Us on Facebook' mod='lookbookblocknav'}">{l s='Join Us on Facebook' mod='lookbookblocknav'}<span></span></a>
		</div>
	</div>
  </div>
</div>

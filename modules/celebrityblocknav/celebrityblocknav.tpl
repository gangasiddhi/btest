<div class="nav_block">
  <img src="{$media_server}{$this_path}slide/24hr_nav_hdr.png" class="nav_top" alt="{l s='Want to see more?' mod='celebrityblocknav'}"/>
{if !isset($no_butigim_link)}
  <div class="nav_block_single">
		<img src="{$media_server}{$this_path}slide/24hr_nav_title1.jpg" class="nav_block_top_img" alt="{l s='Go to My Showroom' mod='celebrityblocknav'}"/>
                    <a href="{$link->getPageLink('showroom.php')}" title="{l s='Go to My Showroom' mod='celebrityblocknav'}">
                            <img src="{$media_server}{$this_path}slide/24hr_nav_img1.jpg" class="nav_block_middle_link" alt="{l s='Go to My Showroom' mod='celebrityblocknav'}"/>
                    </a>
		<div class="nav_hr">
			<hr/>
		</div>
		<div class="nav_block_link">
			<a href="{$link->getPageLink('showroom.php')}" title="{l s='Go to My Showroom' mod='celebrityblocknav'}">{l s='Go to My Showroom' mod='celebrityblocknav'}<span></span></a>
		</div>
  </div>
{/if}

  <div class="nav_block_single" id="nav_auto_padding">
		<img src="{$media_server}{$this_path}slide/24hr_nav_title2.jpg" class="nav_block_top_img" alt="{l s='Browse Lookbooks' mod='celebrityblocknav'}"/>
		<a href="{$link->getPageLink('lookbook.php')}" title="{l s='Browse Lookbooks' mod='celebrityblocknav'}">
		  <img src="{$media_server}{$this_path}slide/24hr_nav_img2.jpg" class="nav_block_middle_link" alt="{l s='Browse Lookbooks' mod='celebrityblocknav'}"/>
		</a>
		<div class="nav_hr">
			<hr/>
		</div>
		<div class="nav_block_link">
			<a href="{$link->getPageLink('lookbook.php')}" title="{l s='Browse Lookbooks' mod='celebrityblocknav'}">{l s='Browse Lookbooks' mod='celebrityblocknav'}<span></span></a>
		</div>
  </div>

  <div class="nav_block_single">
	<div style ="margin:0 0 0 10px">
		<img src="{$media_server}{$this_path}slide/24hr_nav_title3.jpg" class="nav_block_top_img" alt="{l s='Join Us on Facebook' mod='celebrityblocknav'}"/>
		<div class="fb-fanbox">
			<fb:like-box href="http://www.facebook.com/butigo" width="290" height="174" show_faces="true" stream="false" header="false"></fb:like-box>
		</div>
		<div class="nav_hr">
			<hr/>
		</div>
		<div class="nav_block_link">
			<a href="http://www.facebook.com/butigo" target="_blank" title="{l s='Join Us on Facebook' mod='celebrityblocknav'}">{l s='Join Us on Facebook' mod='celebrityblocknav'}<span></span></a>
		</div>
	</div>
  </div>
</div>

<link rel="stylesheet" type="text/css" href="{$this_path}css/jquery.jcarousel.css" />
<link rel="stylesheet" type="text/css" href="{$this_path}css/jquery.jcarousel.popup.css" />
<script src="{$base_dir}js/jquery/jquery-1.4.4.min.js" type="text/javascript"></script>
<script src="{$this_path}js/jquery.jcarousel.popup.js" type="text/javascript"></script>
<script src="{$this_path}js/jquery.jcarousel_startpopup.js" type="text/javascript"></script>
	
	
<div  id="col1" style="margin: 0 0 0">
	<div style="width:920px; margin:0 auto; overflow:hidden; clear:both; ">
	<div id="wrap">
		<ul id="cols-carousel" class="jcarousel-skin-cols">
		{foreach from=$xml->link item=home_link name=links}
			{if !$smarty.foreach.links.first}
			<li class="{if $smarty.foreach.links.last}last{/if}">
				{if $home_link->url}<a href="{$home_link->url}" target="_blank">{/if}<img src='{$this_path}{$home_link->popup}'alt="" />{if $home_link->url}</a>{/if}
			</li>
			{/if}
		{/foreach}
		</ul>
	</div>
	</div>
</div>
	
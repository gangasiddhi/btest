{*<link rel="stylesheet" type="text/css" href="{$this_path}css/jquery.jcarousel.css" />
<link rel="stylesheet" type="text/css" href="{$this_path}css/jquery.jcarousel.collection.css" />
<script src="{$this_path}js/jquery.jcarousel.collection.js" type="text/javascript"></script>
<script src="{$this_path}js/jquery.jcarousel_startcollection.js" type="text/javascript"></script>*}

<script type="text/javascript">
{literal}
$(document).ready(function() {
  //collections lightbox
  $("a.fbox-cols").fancybox({
    'autoSize' : false,
    'width' : 920,
    'height' : 550,
    'padding' : 2,
	'margin' : 0,
	'scrolling' : 'no',
    'titlePosition' : 'over',
	'titleShow' : false,
    'centerOnScroll' : true,
    'hideOnOverlayClick' : true,
    'hideOnContentClick' : false,
    'overlayColor' : '#000',
    'showNavArrows' : false
  });
});
{/literal}
</script>

{*begin collections*}
<div id="collections">
	<div id="cols-content">
		<ul id="cols-list">
		{foreach from=$xml item=home_link name=links key=count}
			<li class="{if $smarty.foreach.links.last}last{/if}">
				  {*<img src='{$this_path}{$home_link[0]}'alt="" title="{$home_link->desc}" />*}
				 <a href='{$content_dir}modules/showroomcollection/popup.php?count={$home_link.load_file}' class="fbox-cols iframe">
					<img src='{$media_server}{$this_path}{$home_link.thumb}'alt="" title="" />
				 {*</a>
				  <a href='{$content_dir}modules/showroomcollection/popup.php?count={$home_link.load_file}' class="fbox-cols iframe collection_pop_up">*}
					 <p class="collection_pop_up">{$home_link.col_name}
					  <span></span></p>
				  </a>
				 {*<a href="#col1" class="fbox-cols inline"><img src='{$this_path}{$home_link->thumbnail}'alt="" title="{$home_link->desc}" /></a>*}
			</li>
		{/foreach}
		</ul>
	</div>
</div>

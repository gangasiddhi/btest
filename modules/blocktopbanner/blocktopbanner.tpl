<div id="slider">
	{if $home_link->url}
	<a href='{$home_link->url}'>
	{/if}
		<img src='{$media_server}{$this_path}{$home_link}'alt=""title="{l s='get image after menu' mod='blockfeaturedimage'}"/>
	{if $home_link->url}
	</a>
	{/if}
</div>

{*<script type="text/javascript">
{literal}
$(document).ready(function() {
	$('#slider').nivoSlider({
		effect:'fade', //Specify sets like: 'fold,fade,sliceDown'
		slices:25,
		animSpeed:500, //Slide transition speed
		pauseTime:5000,
		startSlide:1, //Set starting Slide (0 index)
		directionNav:false, //Next & Prev
		directionNavHide:false, //Only show on hover
		pauseNav:true, //Pause & Play
		pauseNavHide:false, //Only show on hover
		controlNav:false, //1,2,3...
		controlNavThumbs:false, //Use thumbnails for Control Nav
      	controlNavThumbsFromRel:false, //Use image rel for thumbs
		controlNavThumbsSearch: '.jpg', //Replace this with...
		controlNavThumbsReplace: '_thumb.jpg', //...this in thumb Image src
		keyboardNav:true, //Use left & right arrows
		pauseOnHover:false, //Stop animation while hovering
		manualAdvance:false, //Force manual transitions
		captionOpacity:0.8, //Universal caption opacity
		beforeChange: function(){},
		afterChange: function(){},
		slideshowEnd: function(){} //Triggers after all slides have been shown
	});
});
{/literal}
</script>*}

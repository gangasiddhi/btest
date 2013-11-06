{if $bu_env=='production' || $bu_env=='development'}
{if isset($discount_image)}
<script type="text/javascript">
{literal}
function trigger_disc_info(img_src, imgWidth, imgHeight) {
	$.fancybox('<a href="{/literal}{$link->getPageLink('lookbook.php')}{literal}"><img src="'+img_src+'" alt=""/></a>',
	{
		'autoSize' : false,
		'width' : imgWidth,
		'height' : imgHeight,
		'padding' : 1,
		'margin' : 0,
		'scrolling' : 'no',
		'titleShow' : false,
		'centerOnScroll' : true,
		'hideOnOverlayClick' : false,
		'hideOnContentClick' : true,
		'overlayColor' : '#000',
		'showNavArrows' : false,
		'onClosed'	: function (){
			setCookieWithPath("agnsrv", 1, 1, "/"); 
		}
	});
}
	
$(document).ready(function() {
	var c_client_val = getCookie("agnsrv");
	var imgWidth = 572, imgHeight = 204; //Default image size
	var img_src = "{/literal}{$module_dir}{literal}img/"+"{/literal}{$discount_image}{literal}"+".jpg";
	var img = new Image();
	img.src = img_src;
	// To get the image size on load
	img.onload = function () {
		imgWidth = this.width;
		imgHeight = this.height;	
	}
	if (c_client_val==null || c_client_val=="")
		setTimeout(function (){trigger_disc_info(img_src, imgWidth, imgHeight)}, 2);
	else
		setCookieWithPath("agnsrv", 1, 1, "/");

});
{/literal}
</script>
{/if}
{/if}
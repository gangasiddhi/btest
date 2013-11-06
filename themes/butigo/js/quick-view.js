function qvProductSummary(id)
{
	$("#qv_fbox_"+id).trigger('click');
	$.fancybox.showLoading();
	ids = id.split('_');
	productAttributeId = 0;
	productId = parseInt(ids[0]);
	if (typeof(ids[1]) != 'undefined')
		productAttributeId = parseInt(ids[1]);
	$.ajax({
		type: 'GET',
		url: baseDir + 'product-quickview.php',
		async: true,
		cache: false,
		dataType: 'html',
		data: 'ajax=true&id_product='+productId+'&id_product_attribute='+productAttributeId,
		//data: 'ajax=true&add&summary&id_product='+productId+'&ipa='+productAttributeId + ( (customizationId != 0) ? '&id_customization='+customizationId : '') + '&qty='+qty+'&token=' + static_token ,
		success: function(res)
		{
			$('#qv_fbox_cont').html(res);
		}
	});
}

function qvOver(image)
{
	$('#qv_image_'+image).show();
}
function qvOut(image)
{
	$('#qv_image_'+image).hide();
}

$(window).ready(function() {
	$('a.qv_image').unbind('click').click(function(){ qvProductSummary($(this).attr('data-id')); return false; });

	$(".qv_fbox").fancybox({
		'autoSize' : false,
		'width' : 845,
		'height' : 435,
		'padding' : 0,
		'margin' : 0,
		'scrolling' : 'no',
		'titlePosition' : 'over',
		'titleShow' : false,
		'centerOnScroll' : true,
		'hideOnOverlayClick' : true,
		'hideOnContentClick' : false,
		'overlayOpacity' : 0,
		'overlayColor' : 'transparent',
		'showNavArrows' : false,
		'onComplete' : function() {
			$.fancybox.hideActivity();
		},
		'onClosed' : function() {
			$('#qv_fbox_cont').html('');
		}
	});
});

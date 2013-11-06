$(document).ready(function() {
	$("a.fbox-cols").click(function() {
		$("a[rel='" + $(this).data('trigger-rel') + "']").eq(0).trigger('click');
		return false;
	});

	$('.collection-detail-links').fancybox({
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
		'overlayColor' : '#000'
	});
});

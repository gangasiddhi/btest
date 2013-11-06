$(document).ready(function() {
	if ($("a.thumb_image").length > 0) {
		$("a.thumb_image").fancybox({
			'autoDimensions' : true,
			'width' : 920,
			'height' : 300,
			'padding' : 1,
			'margin' : 0,
			'scrolling' : 'no',
			'titleShow' : false,
			'hideOnContentClick' : true,
			'overlayColor' : '#000',
			'showNavArrows' : true
		});
	}
});

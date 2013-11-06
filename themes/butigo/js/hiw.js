$(document).ready(function() {
	$("a.fbox-hiw-button, a.fbox-hiw, area.fbox-hiw").click(function() {
		$.fancybox({
			'autoSize'	: false,
			'width'				: 880,
			'height'			: 520,
			'padding'			: 1,
			'titlePosition'		: 'over',
			'centerOnScroll'	: true,
			'hideOnOverlayClick': false,
			'overlayColor'		: '#000',
			'autoScale'			: false,
			'transitionIn'		: 'none',
			'transitionOut'		: 'none',
			'title'				: this.title,
			'href'				: this.href.replace(new RegExp("watch\\?v=", "i"), 'v/'),
			'type'				: 'swf',
			'swf'				: {
				'wmode'				: 'transparent',
				'allowfullscreen'	: 'true'
			},
			'onComplete'		: function() {
				if(show_get_started == 1) {
					$('#fancybox-content').append($('#btn_get_started').clone().css('display', 'block'));
					$('#fancybox-content').css('height', '570');
					$('#fancybox-close').css('cursor', 'pointer');
				}
			}
		});
		return false;
	});
});

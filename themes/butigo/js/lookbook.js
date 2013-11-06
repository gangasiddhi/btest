function checkLBPopup(c_name, c_val, c_time) {
	var c_client_val = getCookie(c_name);

	if (c_client_val === null || c_client_val === "") {
		setTimeout(function() {
			$("a#fbox-hiw-lkbk").trigger('click');
			setCookie(c_name, c_val, c_time);
		}, 3000);
	} else if (c_client_val == c_val) {
		setTimeout(function() {
			$("a#fbox-hiw-lkbk").trigger('click');
			setCookie(c_name, c_val + 1, c_time);
		}, 3000);
	}
}

function trigger_disc_info(img_src, imgWidth, imgHeight) {
	$.fancybox(/*'<a href="'+redirt_url+'">*/'<img src="'+img_src+'" alt=""/>'/*</a>'*/,
	{
		'autoSize' : false,
		'width' : imgWidth,
		'height' : imgHeight,
		'padding' : 0,
		'margin' : 0,
		'scrolling' : 'no',
		'titleShow' : false,
		'centerOnScroll' : true,
		'hideOnOverlayClick' : false,
		'hideOnContentClick' : true,
		'overlayColor' : '#000',
		'showNavArrows' : false,
		'onClosed'	: function (){
			setCookieWithPath("campaign", 1, 24*60*60, "/");
		}
	});
}

$(document).ready(function() {
	$('.showroom_sel_cntnr').lazyloader('a.product_image img');

	$('.showroom_sel_cntnr').mouseover(function(){
		var frontImage = $(this).find('img').first();
		var backImage = $(this).find('img').last();
		$(frontImage).css('display','inline');
	});

	/*$('.fade').mouseover(function(){
		$(this).prev().css('display','inline');
	});*/

	/*$('.showroom_sel_cntnr').mouseover(function(){
		var frontImage = $(this).find('img').first();
		var backImage = $(this).find('img').last();
		if(!$(frontImage).hasClass('hidden')){
			$(frontImage).addClass('hidden');
			if($(backImage).hasClass('hidden')){
				$(backImage).removeClass('hidden');
			}
		}
	});

	$('.showroom_sel_cntnr').mouseout(function() {
		var frontImage = $(this).find('img').first();
		var backImage = $(this).find('img').last();
		if($(frontImage).hasClass('hidden')){
			$(frontImage).removeClass('hidden');
			if(!$(backImage).hasClass('hidden')){
				$(backImage).addClass('hidden');
			}
		}
	});*/

	/*Changing the image on mouse over.
	$('.showroom_sel_shoe a').mouseenter(function(){
		var frontImage = $(this).find('img').first();
		var backImage = $(this).find('img').last();
		$(frontImage).stop(true,true).fadeOut('fast');
		$(backImage).stop(true,true).fadeTo(2000, 1, 'linear');
	});

	$('.showroom_sel_shoe a').mouseleave(function(){
		var frontImage = $(this).find('img').first();
		var backImage = $(this).find('img').last();
		$(backImage).stop(true,true).fadeOut('fast');
		$(frontImage).stop(true,true).fadeTo(2000, 1, 'linear');
	});*/

	if (window.butigim_pop_up) {
		if (butigim_pop_up == 1) {
			$("a#24-hr-showroom").fancybox({
				'autoSize' : false,
				'width' : 572,
				'height' : 204,
				'padding' : 1,
				'margin' : 0,
				'scrolling' : 'no',
				'titlePosition' : 'over',
				'titleShow' : false,
				'centerOnScroll' : true,
				'hideOnOverlayClick' : false,
				'hideOnContentClick' : false,
				'overlayColor' : '#000',
				'showNavArrows' : false
			});

			$("a#24-hr-showroom").trigger('click');
		}
	}

	var c_discountpop_val = getCookie("FifteenPercentDiscountPopup");
	var imgWidth = 532, imgHeight = 264; /*Default image size*/
	var img = new Image();
	img.src = img_src;
	/* To get the image size on load*/
	img.onload = function () {
		imgWidth = this.width;
		imgHeight = this.height;
	}
	/*if((c_discountpop_val==null || c_discountpop_val=="")){
		setTimeout(function (){trigger_disc_info(img_src, imgWidth, imgHeight)}, 2);
		setCookieWithPath("FifteenPercentDiscountPopup", 1, 24*60*60, "/");
	}*/


});

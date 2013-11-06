function checkHiwVideo(c_name, c_val, c_exp)
{
	//var c_val = '1', c_name = "hiw_v_sr";
	var c_client_val = getCookie(c_name);

	if (c_client_val==null || c_client_val=="")
	{
		setTimeout(function(){
			$("a.fbox-hiw").trigger('click');
			setCookie(c_name, c_val, c_exp);
		}, 2000);
	}
	else
		setCookie(c_name, c_val, c_exp);
}

$(document).ready(function() {
	//See more
	var show_shoes_slider = true, show_more_shoes = true;
	var show_handbags_slider = true, show_more_handbags = true;
	//var show_jewelry_slider = true, show_more_jewelry = true;

	$('#more_shoes a').click(function() {
		if(show_more_shoes == true) {
			$('#more_shoes_products').show();
			$('#more_shoes a').addClass('hide-more');
			show_more_shoes = false;
			if(show_shoes_slider == true) {
				show_shoes_slider = false;
			}
		} else {
			$('#more_shoes_products').hide();
			$('#more_shoes a').removeClass('hide-more');
			show_more_shoes = true;
		}
		return false;
	});

	$('#more_handbags a').click(function() {
		if(show_more_handbags == true) {
			$('#more_handbags_products').show();
			$('#more_handbags a').addClass('hide-more');
			show_more_handbags = false;
			if(show_handbags_slider == true) {
				show_handbags_slider = false;
			}
		} else {
			$('#more_handbags_products').hide();
			$('#more_handbags a').removeClass('hide-more');
			show_more_handbags = true;
		}
		return false;
	});

	// load images as needed
	$('.showroom_sel_cntnr').lazyloader('a.product_image img');

	$('.showroom_sel_cntnr').mouseover(function(){
		var frontImage = $(this).find('img').first();
		var backImage = $(this).find('img').last();
		$(frontImage).css('display','inline');
	});

	//HIW auto popup
//	checkHiwVideo("hiw_v_sr", 1, 3650);

	/*Changing the image on mouse over.*/
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

	$('.showroom_sel_cntnr').mouseout(function(){
		var frontImage = $(this).find('img').first();
		var backImage = $(this).find('img').last();
		if($(frontImage).hasClass('hidden')){
			$(frontImage).removeClass('hidden');
			if(!$(backImage).hasClass('hidden')){
				$(backImage).addClass('hidden');
			}
		}
	});*/


});

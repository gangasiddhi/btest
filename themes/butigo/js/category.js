$(document).ready(function() {
	$('.showroom_sel_cntnr').lazyloader('a.product_image img');

	$('.cat_links li').mouseover(function() {
			$(this).addClass('hover_cat')
		}).mouseout(function(){
			$(this).removeClass('hover_cat')
		});

	$('.showroom_sel_cntnr').mouseover(function(){
		var frontImage = $(this).find('img').first();
		var backImage = $(this).find('img').last();
		$(frontImage).css('display','inline');
	});

	/*$('.fade').mouseover(function(){
		$(this).prev().css('display','inline');
	});*/

	/*Changing the image on mouse over.*/
	/*$('.showroom_sel_cntnr').mouseover(function() {
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

});

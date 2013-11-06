/* 
 *  JS Related to the Customer Likes & Dislikes of products.
 */
var customerLikesDislikes = {
	url: baseDir+'modules/customerlikesdislikes/savecustomerlikesdislikes.php',
	saveLikes:function(productId){
		$.ajax({
			type: 'GET',
			url: customerLikesDislikes.url,
			data:'action=savelike&productId='+productId,
			async: true,
			cache: false,
			datatype: 'json',
			success: function(data){
			}
		});
	},
	saveDisLikes:function(productId){
		$.ajax({
			type: 'GET',
			url: customerLikesDislikes.url,
			data:'action=savedislike&productId='+productId,
			async: true,
			cache: false,
			datatype: 'json',
			success: function(data){
			}
		});
	}
};

$(document).ready(function(){
	/*To save the customer Likes*/
	$('.customer-like-btn').click(function(){
		var productId = $(this).attr('productId');
		if(!$(this).hasClass('showroom-like-selected')){
			$(this).addClass('showroom-like-selected');
			if($('.dislike-'+productId).hasClass('showroom-dislike-selected')){
				$('.dislike-'+productId).removeClass('showroom-dislike-selected');
			}
		}else{
			$(this).removeClass('showroom-like-selected');
		}
		customerLikesDislikes.saveLikes(productId);
	});
	
	$('.product-page-customer-like-btn').click(function(){
		if(!$(this).hasClass('product-like-selected')){
			$(this).addClass('product-like-selected');
			if($('.product-page-customer-dislike-btn').hasClass('product-dislike-selected')){
				$('.product-page-customer-dislike-btn').removeClass('product-dislike-selected');
			}
		}else{
			$(this).removeClass('product-like-selected');
		}
		
		var productId = $(this).attr('productId');
		customerLikesDislikes.saveLikes(productId);
	});
	
	/*To save the customer Dislikes*/
	$('.customer-dislike-btn').click(function(){
		var productId = $(this).attr('productId');
		if(!$(this).hasClass('showroom-dislike-selected')){
			$(this).addClass('showroom-dislike-selected');
			if($('.like-'+productId).hasClass('showroom-like-selected')){
				$('.like-'+productId).removeClass('showroom-like-selected');
			}
		}else{
			$(this).removeClass('showroom-dislike-selected');
		}
		customerLikesDislikes.saveDisLikes(productId);
	});
	$('.product-page-customer-dislike-btn').click(function(){
		var productId = $(this).attr('productId');
		if(!$(this).hasClass('product-dislike-selected')){
			$(this).addClass('product-dislike-selected');
			if($('.product-page-customer-like-btn').hasClass('product-like-selected')){
				$('.product-page-customer-like-btn').removeClass('product-like-selected');
			}
		}else{
			$(this).removeClass('product-dislike-selected');
		}
		customerLikesDislikes.saveDisLikes(productId);
	});
});
	



$(document).ready(function(){	
	/*Close the recomend-sizes aprt upon click on close button*/
	$('.product-recommend-size-close').click(function(){
		var productId = $(this).children().attr('data-product-id');
		$('#product-recommend-size-list-'+productId).hide();
	});
	
	/*To load the sizes of the recommend-product upon click on add to cart img*/
	$('.product_recommend_ajax_add_to_cart_button').unbind('click').click(function(){
			var idProduct =  $(this).attr('rel').replace('ajax_id_product_', '');
			$.ajax({
				type: 'GET',
				url: baseDir + 'modules/productrecommend/product-recommend-size.php?productId='+idProduct,
				async: true,
				cache: false,
				dataType : "html",
				success: function(data)
				{
					$('#product-recommend-size-list-'+idProduct).show();
					$('#product-recommend-size-list-'+idProduct).html('');
					$('#product-recommend-size-list-'+idProduct).html(data);
					 $('.ajax-js').each(function() {
						var jsfile = $(this).attr('jsfile');

						$.ajax({
							type: "GET",
							url: jsfile,
							dataType: "script"
						});
					});
				},
				error: function(XMLHttpRequest, textStatus, errorThrown) {
					//alert("TECHNICAL ERROR: unable to refresh the cart.\n\nDetails:\nError thrown: " + XMLHttpRequest + "\n" + 'Text status: ' + textStatus);
				}
			});
			
			return false;
		});
});

/*Chnaging the visula effects on recommend-product size select & add to cart via ajax on select of size*/
function updateProductRecommendChoiceSelect(element,idProduct, ipa, id_attribute, id_group)
{
	//if (id_attribute == 0)
	{
		//refreshProductImages(0);
		//return ;
	}
	// Visual effect
	$('#product-recommend-choices-group-'+id_group+' ul.product-recommend-choices li').removeClass('picked');
	$('#product-recommend-choice-'+id_attribute).parent().addClass('picked');
	$('#product-recommend-choice-'+id_attribute).fadeTo('fast', 1, function(){$(this).fadeTo('normal', 0, function(){$(this).fadeTo('normal', 1, function(){});});});
	
	/*Adding the recommend via ajax*/
	ajaxCart.add(idProduct, ipa, true, '');
	
	/*Close the recommend-product size part upon adding*/
	$('#product-recommend-size-list-'+idProduct).hide();
}

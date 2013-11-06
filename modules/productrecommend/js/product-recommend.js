/* function called by cropping process (buttons clicks) */
$(document).ready(function () {

	$('input[name="recommendActive"]').click(function(){
		var recommendActive = $('input[name="recommendActive"]:checked').val();
		if(recommendActive == 0){
			/*Interface Hidden*/
			$('input[name="recommendType"]:checked').attr('checked',false);
			
			if(!$('#recommend-products-container').hasClass('hidden')){							
				$('#recommend-products-container').addClass('hidden');				
			}
			if(!$('#recommend-category-container').hasClass('hidden')){
				$('#recommend-category-container').addClass('hidden');
			}
			
			$('#final-recommend-product-list tbody tr').each(function(){
				$(this).empty();
			});
			
			/*Empty the recommendations*/
			productRecommendCompleteArray['recommend'] = recommendActive;
			productRecommendCompleteArray['recommendType'] = '';
			productRecommendCompleteArray['categoryId'] = '';
			productRecommendCompleteArray['productIdList'] = '';
			
			saveRecommendations('Disabled');
			
		}
		
		productRecommendCompleteArray['recommend'] = recommendActive;
	});
	
	
	/*To check the Catgeory/products optins are selected, if catgeory is selected display the category list
	 *else products list .*/	
	
	$('input[name="recommendType"]').click(function(){
		if($('input[name="recommendActive"]:checked').val() == 1){
			var recommendType = $('input[name="recommendType"]:checked').val();
			if(recommendType === 'category'){
				productRecommendCompleteArray['recommendType'] = "category";
				if($('#recommend-category-container').hasClass('hidden')){
					$('#recommend-category-container').removeClass('hidden');
					if(!$('#recommend-products-container').hasClass('hidden')){
						$('#recommend-products-container').addClass('hidden');
					}				
				}					
			}else if(recommendType === 'products'){
				productRecommendCompleteArray['recommendType'] = "products";
				if($('#recommend-products-container').hasClass('hidden')){							
					$('#recommend-products-container').removeClass('hidden');
					if(!$('#recommend-category-container').hasClass('hidden')){
						$('#recommend-category-container').addClass('hidden');
					}				
				}					
			}else{
				productRecommendCompleteArray['recommendType'] = "";
				if(!$('#recommend-products-container').hasClass('hidden')){							
					$('#recommend-products-container').addClass('hidden');				
				}
				if(!$('#recommend-category-container').hasClass('hidden')){
					$('#recommend-category-container').addClass('hidden');
				}
			}
		}
	});
	
	//	/*To display the produts contains in the selecsted category*/
	//	$('#recommend-category').change(function(){
	//		var catId = $(this).val();
	//		productRecommendCompleteArray['categoryId'] = catId;
	//		/*To the products in the categories*/
	//		$.ajax({
	//			url: "../modules/productrecommend/ajax.php?action=getproducts&catId="+catId,
	//			type: "GET",
	//			dataType: "json",
	//			success : function(result){
	//				if(result != false){
	//					var data = '';
	//					for(var i=0; i < result.length ;i++){
	//						data += '<option value="'+result[i]['id_product']+(result[i]['id_product_attribute']?'_'+result[i]['id_product_attribute']:'')+(result[i]['name']?'_'+result[i]['name']:'')+'">'+result[i]['name']+'</option>'; 					
	//					}
	//					$('#select').empty();
	//					$('#select').append(data);
	//				}else{
	//					$('#select').empty();
	//					alert('There are no products in this category');					
	//				}
	//			}
	//		});	
	//	});
	
	/*To empty the category product container*/
	$('#referesh-category-recommend-product-list').click(function(){
		$('#select').empty();
	});
	
	/*Add the selected product as recommend*/
	$('#add-category-recommend-product').click(function(){				
		var selectedProducts = $('#select').val();
		if(selectedProducts != null){
			addProduct(selectedProducts);
		}
	});
	
	/*Add the all products in a category at a time as recommend*/
	$('#add-all-category-recommend-product').click(function(){	
		$('#select').each(function(){
			$('#select option').attr('selected','selected');
		});
	//		var selectedProducts = $('#select').val();
	//		if(selectedProducts != null){
	//			addProduct(selectedProducts);
	//		}
	});
	
	/*To move the table row up and down*/
	$('.move').click(function() {                                                               
		var direction = $(this).attr('direction'),
		$original = $(this).closest("tr"),
		$target = direction === "up" ? $original.prev() : $original.next();

		if ( $target.length && direction === "up" ) {
			$original.insertBefore($target);
		}else if( $target.length ) {
			$original.insertAfter($target);
		}
		
		var i = 1;
		var newProductList = '';
		$('.recommend-product-value').each(function(){
			var recommendValue = $(this).val().split('_');
			var recommendValueString = recommendValue[0]+'_'+recommendValue[1]+'_'+recommendValue[2]+'_'+i;
			newProductList += recommendValueString+'|';
			if(recommendValueString){
				$(this).val(recommendValueString);
				i++;
			}				
		});
		
		productRecommendCompleteArray['productIdList'] = newProductList;
		
		/*arrangeTheTableArrows();	*/	
	});

	/*Save the recommendations*/
	$('#final-recommend-products-list-save').click(function(){
		saveRecommendations('Saved');
	});
	
	/* function autocomplete */
	$('#recommand_product_autocomplete_input')
	.autocomplete('../modules/productrecommend/ajax-products-list.php', {
		minChars: 1,
		autoFill: true,
		max:20,
		matchContains: true,
		mustMatch:true,
		extraParams: {
			idLang: langId
		},
		scroll:false
	})		
	.result(afterTextInserted);
		
	refereshPage();
});

function afterTextInserted (event, data, formatted) {
	var selectedProducts = [];
	if (data == null){
		return false;
	}else{
		var productId = data[1];
		var productAttributeId = data[2];
		var productName = data[0];
		var productToAdd = productId+'_'+productAttributeId+'_'+productName;
		selectedProducts[0] = productToAdd.toString();
		addProduct(selectedProducts);
	}
}

/*Save the recommendations list*/
function saveRecommendations($option){
	$.ajax({
		type	: "POST",
		url		: "../modules/productrecommend/ajax.php",
		data : 'recommend='+productRecommendCompleteArray["recommend"]+'&recommendType='+productRecommendCompleteArray["recommendType"]+'&productId='+productRecommendCompleteArray["productId"]+'&categoryId='+productRecommendCompleteArray["categoryId"]+'&productIdList='+productRecommendCompleteArray["productIdList"]+'&action=saveRecommendations',
		dataType :'html',
		success: function(data){
			alert(data);
		}
	});
}

/*Adding the recommend product*/
function addProduct(selectedProducts){
	var recommendProductIds = '';
	var recommendedProductDetails  = '';
	var productData = '';
	var selectedProduct = [];
	var alreadyAddedProduts = [];
	var position = 0;
	/*Alreday existing recommend product list*/
	if(productRecommendCompleteArray['productIdList']){
		var productList = productRecommendCompleteArray['productIdList'].split('|');
	}else{
		var productList = [];
	}
	
	/*Filter the already added products*/
	var j = 0;
	for(var i=0; i < selectedProducts.length; i++){
		if(productList){
			selectedProduct = selectedProducts[i].split('_');
			var selectedProductString = selectedProduct[0]+'_'+selectedProduct[1]+'_'+selectedProduct[2];
			for(var k=0; k < productList.length; k++){
				var recommendProduct = productList[k].split('_');
				var productListString = recommendProduct[0]+'_'+recommendProduct[1]+'_'+recommendProduct[2];		
				if(productListString == selectedProductString){
					alreadyAddedProduts[j] = selectedProducts[i];
					j++;
					delete(selectedProducts[i]);
					continue;					
				}
			}			
		}
	}

	/*Alert the already Added Products*/
	for(var i=0; i < alreadyAddedProduts.length; i++){
		var alreadyAddedProduct = alreadyAddedProduts[i].split('_');
		alert(alreadyAddedProduct[2]+" Already Addedd.");
	}
	
	for(var i=0; i < selectedProducts.length; i++){		
		/*To check whether the product is already is added or not, if the product is not added, add
		 * other wise display an alert message*/
		if(selectedProducts[i]){
			selectedProduct = selectedProducts[i].split('_');
			/*Adding the product*/
			position = productRecommendCompleteArray['productIdList'].split('|');
			recommendProductIds = '';
			recommendedProductDetails  = '';
			productData = '';
			recommendProductIds += selectedProduct[0]+'_'+selectedProduct[1]+'_'+selectedProduct[2]+'_'+(position.length)+'|';
			productData = selectedProduct[0]+'_'+selectedProduct[1]+'_'+selectedProduct[2]+'_'+(position.length);
			recommendedProductDetails += '<tr><td>'+selectedProduct[2]+'</td>';
			recommendedProductDetails += '<td><p><span class="move down-arrow" direction="down"></span><span class="move up-arrow" direction="up"></span></p></td>';
			recommendedProductDetails += '<td style="width:50px;text-align:center">'+'<input  class ="recommend-product-value" value="'+productData+'" style="cursor:pointer;background-image:url(\'../img/admin/delete.gif\');border:none;width:16px;height:16px;background-color:#FFFFF0;text-indent: -9999px;" onClick="deleteProduct(this);return false;" /></td></tr>';		
			recommendedProductDetails += '</table>';
			
			productRecommendCompleteArray['productIdList'] = productRecommendCompleteArray['productIdList'] != null ? productRecommendCompleteArray['productIdList']+recommendProductIds:recommendProductIds;
			
			if($('#final-recommend-product-list tr:visible').length != 1){
				$('table#final-recommend-product-list').find('tbody').append(recommendedProductDetails);
			}else{
				$('table#final-recommend-product-list').find('tbody tr:last').after(recommendedProductDetails)	
			}
		}
	}	
	
	/*To reload the product-recommend.js*/
	$('.ajax-js').each(function() {
		var jsfile = $(this).attr('jsfile');

		$.ajax({
			type: "GET",
			url: jsfile,
			dataType: "script"
		});
	});	
	
	/*Arranging the table rows*/
	/*arrangeTheTableArrows();*/
}

/*Deleting the recommend product*/
function deleteProduct(obj){
	var deleteProduct = $(obj).val();
	var productList = productRecommendCompleteArray['productIdList'].split('|');
	var newProductList = '';
	var selectedProduct = []; 
	var j = 0;
	/*Constructing the new list , except the deleted product*/
	for(var i=0; i < productList.length; i++){
		if(productList[i] != deleteProduct && productList[i]){
			selectedProduct = productList[i].split('_');
			newProductList += selectedProduct[0]+'_'+selectedProduct[1]+'_'+selectedProduct[2]+'_'+(j+1)+'|';	
			j++;
		}	
	}
	/*Replacing with newProductList*/
	productRecommendCompleteArray['productIdList'] = newProductList;
	
	/*To delete the product in UI*/
	$($(obj).parent()).parent().empty();	
	
	/*Updating the input value on deleting*/
	productList = productRecommendCompleteArray['productIdList'].split('|');		
	for(var i=0; i < productList.length; i++){
		if(productList[i]){
			selectedProduct = productList[i].split('_');
			var productListString = selectedProduct[0]+'_'+selectedProduct[1]+'_'+selectedProduct[2];
			$('.recommend-product-value').each(function(){
				var recommendValue = $(this).val().split('_');
				var recommendValueString = recommendValue[0]+'_'+recommendValue[1]+'_'+recommendValue[2];
				if(recommendValueString == productListString){
					$(this).val(productList[i]);
				}				
			});
		}
	}
}

/*To show the previous selected options*/
function refereshPage(){
	$('input[name="recommendType"]:checked').trigger('click');
	$('input[type="checkbox"]:checked').trigger('click');
//$('#recommend-category').trigger('change');
}

function showProductsInTheCategory(categoryId){
	productRecommendCompleteArray['categoryId'] = categoryId;
	/*To the products in the categories*/
	$.ajax({
		url: "../modules/productrecommend/ajax.php?action=getproducts&catId="+categoryId+"&langId="+langId,
		type: "GET",
		dataType: "json",
		success : function(result){
			if(result != false){
				var data = '';
				for(var i=0; i < result.length ;i++){
					data += '<option value="'+result[i]['id_product']+(result[i]['id_product_attribute']?'_'+result[i]['id_product_attribute']:'')+(result[i]['name']?'_'+result[i]['name']:'')+'">'+result[i]['name']+'</option>'; 					
				}
				$('#select').empty();
				$('#select').append(data);
			}else{
				$('#select').empty();
				alert('There are no products in this category');					
			}
		}
	});	
}

function arrangeTheTableArrows(){
	var rowCount = $('#final-recommend-product-list tr:visible').length;

	$('#final-recommend-product-list tbody tr').each(function(){			
		if($(this).closest('tr').index() == $('#final-recommend-product-list tr').eq(1).index()){
			if($($(this).children('td').children('p').children('span')[1]).hasClass('up-arrow')){
				$($(this).children('td').children('p').children('span')[1]).removeClass('up-arrow');
			}
		}else if($(this).closest('tr').index() == $('#final-recommend-product-list tr:visible:last').index()){				
			if($($(this).children('td').children('p').children('span')[0]).hasClass('down-arrow')){
				$($(this).children('td').children('p').children('span')[0]).removeClass('down-arrow');
			}
		}else{
			if(!$($(this).children('td').children('p').children('span')[0]).hasClass('down-arrow') && $($(this).children('td').children('p').children('span')[1]).attr('direction') == 'down'){
				$($(this).children('td').children('p').children('span')[0]).addClass('down-arrow');
			}else if(!$($(this).children('td').children('p').children('span')[1]).hasClass('up-arrow') && $($(this).children('td').children('p').children('span')[1]).attr('direction') == 'up'){
				$($(this).children('td').children('p').children('span')[1]).addClass('up-arrow');
			}
		}
				
	});
}
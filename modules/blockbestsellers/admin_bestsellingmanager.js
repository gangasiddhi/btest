/* function called by cropping process (buttons clicks) */

$(window).load(function () {
	
	/* function autocomplete */
	$('#product_autocomplete_input')
		.autocomplete('../modules/blockbestsellers/ajax_products_list_bestsellingmanager.php', {
			minChars: 1,
			autoFill: true,
			max:20,
			matchContains: true,
			mustMatch:true,
			extraParams: {idLang: function() { return $("#id_lang").val(); }},
			scroll:false
		})		
		.result(afterTextInserted);
		
	$('#product_autocomplete_input_add')
		.autocomplete('../modules/blockbestsellers/ajax_products_list_bestsellingmanager.php', {
			minChars: 1,
			autoFill: true,
			max:20,
			matchContains: true,
			mustMatch:true,
			extraParams: {idLang: function() { return $("#id_lang").val(); }},
			scroll:false
		})	
		.result(afterTextInsertedAdd);
});

function afterTextInserted (event, data, formatted) {	
	if (data == null){
		return false;
	}	
	var idProduct = data[1];
	var nameProduct = data[0];
	var idLang = data[2];
	$('#id_product_custom').val(idProduct);
	$('#id_lang').val(idLang);
	showDetail();
}

function afterTextInsertedAdd (event, data, formatted) {	
	if (data == null){
		return false;
	}
	var idProduct = data[1];
	var nameProduct = data[0];
	$('#id_product').val(idProduct);
	addProduct();
}

function showDetail(){ 
//	name_product_custom = $('#product_autocomplete_input').val();
//	id_product_custom = $('#id_product_custom').val();
//	id_lang = $('#id_lang').val();
		
	$.ajax({
		type	: "GET",
		cache	: false,
		url		: "../modules/blockbestsellers/ajax.php",
		data	: "action=show",
		success: function(data){ 
			$('#liste').fadeOut("fast",function(){
				$('#out').empty();
//				$('#name_product_custom').empty();
				strings = data.split('<sep>');
//				$('#name_product_custom').append(strings[0]);
				$('#out').append(strings[1]);
				$('#liste').fadeIn("slow");
			});
		}
	});
}

function addProduct(){ //alert("hai");
	/*name_product_custom = $('#product_autocomplete_input').val();	*/
	name_product = $('#product_autocomplete_input_add').val();
/*	id_product_custom = $('#id_product_custom').val();*/
	id_product = $('#id_product').val();
	id_lang = $('#id_lang').val();
	
	$.ajax({
		type	: "GET",
		cache	: false,
		url		: "../modules/blockbestsellers/ajax.php",
		data	:"name_product=" + name_product 
			  +  "&id_product=" + id_product + "&id_lang=" + id_lang + "&action=add",
		success: function(data){
			$('#out').empty();
			$('#error').empty();
			strings = data.split('<sep>');
			if(strings[1]){
				$('#out').append(strings[1]);
				$('#conf').fadeOut("slow");
				$('#error').fadeOut("slow");
				$('#error').append(strings[0]);
				$('#error').fadeIn("slow");
			}
			else{
				$('#out').append(data);
				$('#error').fadeOut("slow");
				$('#conf').fadeOut("slow");
				$('#conf').fadeIn("slow");
			}			
		}
	});	
}

function deleteProduct(id_product){
//	name_product_custom = $('#product_autocomplete_input').val();
//	id_product_custom = $('#id_product_custom').val();
	id_lang = $('#id_lang').val();
		
	$.ajax({
		type	: "GET",
		cache	: false,
		url		: "../modules/blockbestsellers/ajax.php",
		data	: "id_product=" 
		          + id_product + "&id_lang=" + id_lang + "&action=delete",
		success: function(data){
			$('#out').empty();
			$('#out').append(data);
			$('#error').fadeOut("slow");
			$('#conf').fadeOut("slow");
			$('#conf').fadeIn("slow");
		}
	});	
}

function sortProducts(id_product,move_towards){ 
	id_lang = $('#id_lang').val();
	var move_toward = '';
	
	/*Get the value of the position*/
	if(move_towards == 1)
		move_toward = $('.positionUp').val();
	else if(move_towards == 0)
		move_toward = $('.positonDown').val();
	
	/*Hide the up and down arrows while sorting*/
	$('.positonDown').hide();
	$('.positionUp').hide();
	
	$.ajax({
		type	: "GET",
		cache	: false,
		url		: "../modules/blockbestsellers/ajax.php",
		data	:"id_product=" + id_product + "&move_towards="+move_toward+"&id_lang=" + id_lang + "&action=sort",
		success: function(data){ 
			showDetail();
		}
	});
}
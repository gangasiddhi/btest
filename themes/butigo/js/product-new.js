
//global variables
var combinations = new Array();
var selectedCombination = new Array();
var globalQuantity = new Number;
var colors = new Array();

//check if a function exists
function function_exists(function_name)
{
	if (typeof function_name == 'string')
		return (typeof window[function_name] == 'function');
	return (function_name instanceof Function);
}

//execute oosHook js code
function oosHookJsCode()
{
	for (var i = 0; i < oosHookJsCodeFunctions.length; i++)
	{
		if (function_exists(oosHookJsCodeFunctions[i]))
		setTimeout(oosHookJsCodeFunctions[i]+'()', 0);
	}
}

//update display of the availability of the product AND the prices of the product
function updateDisplay()
{
	if (!selectedCombination['unavailable'] && quantityAvailable > 0)
	{
		//show the choice of quantities
		$('#quantity_wanted_p:hidden').show('slow');

		//show the "add to cart" button ONLY if it was hidden
		$('#add_to_cart:hidden').fadeIn(600);

		//hide the hook out of stock
		$('#oosHook').hide();

		//availability value management
		if (availableNowValue != '')
		{
			//update the availability statut of the product
			$('#availability_value').removeClass('warning-inline');
			$('#availability_value').text(availableNowValue);
			$('#availability_statut:hidden').show();
		}
		else
		{
			//hide the availability value
			$('#availability_statut:visible').hide();
		}

		//'last quantities' message management
		if (quantityAvailable <= maxQuantityToAllowDisplayOfLastQuantityMessage && !allowBuyWhenOutOfStock)
		{
			//display the 'last quantities' message
			$('#last_quantities').show('slow');
		}
		else
		{
			//hide the 'last quantities' message
			$('#last_quantities').hide('slow');
		}

		//display the quantities of pieces (only if allowed)
		if (quantitiesDisplayAllowed)
		{
			$('#pQuantityAvailable:hidden').show('slow');
			$('#quantityAvailable').text(quantityAvailable);
			if(quantityAvailable < 2)
			{
				$('#quantityAvailableTxt').show();
				$('#quantityAvailableTxtMultiple').hide();
			}
			else
			{
				$('#quantityAvailableTxt').hide();
				$('#quantityAvailableTxtMultiple').show();
			}

            if (typeof productHasAttributes != 'undefined' && productHasAttributes)
            {
                if($('#choices_group_4 ul li.picked').length > 0)
                {
                    $('#qty-available span').text(quantityAvailable);
                    $('#qty-available').show();
                }
            }
            if(typeof color_shoe_combination == 'undefined')
                $('#qty-available span').text(quantityAvailable);
		}
	}
	else
	{
		//show the hook out of stock
		$('#oosHook').show();
		if ($('#oosHook').length > 0 && function_exists('oosHookJsCode'))
			oosHookJsCode();

		//hide 'last quantities' message if it was previously visible
		$('#last_quantities:visible').hide('slow');

		//hide the quantity of pieces if it was previously visible
		$('#pQuantityAvailable:visible').hide('slow');

		//hide the choice of quantities
		if (!allowBuyWhenOutOfStock)
			$('#quantity_wanted_p:visible').hide('slow');

		//display that the product is unavailable with theses attributes
		if (!selectedCombination['unavailable'])
			$('#availability_value').text(doesntExistNoMore + (globalQuantity > 0 ? ' ' + doesntExistNoMoreBut : '')).addClass('warning-inline');
		else
		{
			$('#availability_value').text(doesntExist).addClass('warning-inline');
			$('#oosHook').hide();
		}
		$('#availability_statut:hidden').show();

		//show the 'add to cart' button ONLY IF it's possible to buy when out of stock AND if it was previously invisible
		if (allowBuyWhenOutOfStock && !selectedCombination['unavailable'])
		{
			$('#add_to_cart:hidden').fadeIn(600);

			if (availableLaterValue != '')
			{
				$('#availability_value').text(availableLaterValue);
				$('p#availability_statut:hidden').show('slow');
			}
			else
				$('p#availability_statut:visible').hide('slow');
		}
		else
		{
			$('#add_to_cart:visible').fadeOut(600);
			$('p#availability_statut:hidden').show('slow');
		}
	}

	if (selectedCombination['reference'] || productReference)
	{
		if (selectedCombination['reference'])
			$('#product_reference span').text(selectedCombination['reference']);
		else if (productReference)
			$('#product_reference span').text(productReference);
		$('#product_reference:hidden').show('slow');
	}
	else
		$('#product_reference:visible').hide('slow');

	//update display of the the prices in relation to tax, discount, ecotax, and currency criteria
	/*if (!selectedCombination['unavailable'])
	{
		var tax = (taxRate / 100) + 1;

		if (noTaxForThisProduct)
			var attribut_price_tmp = selectedCombination['price'] / (1 + (parseFloat(defaultTaxRate) / 100));
		else
			var attribut_price_tmp = selectedCombination['price'];

		var productPriceWithoutReduction2 = (ps_round(attribut_price_tmp * currencyRate) + productPriceWithoutReduction);

		if (reduction_from != reduction_to && (currentDate > reduction_to || currentDate < reduction_from))
			var priceReduct = 0;
		else
			var priceReduct = productPriceWithoutReduction2 / 100 * parseFloat(reduction_percent) + reduction_price;
		var priceProduct = productPriceWithoutReduction2 - priceReduct;

		if (!noTaxForThisProduct)
			var productPricePretaxed = (productPriceWithoutReduction2 - priceReduct) / tax;
		else
			var productPricePretaxed = priceProduct;

		if (displayPrice == 1)
		{
			priceProduct = productPricePretaxed;
			productPriceWithoutReduction2 /= tax;
		}

		if (group_reduction)
		{
			priceProduct *= group_reduction;
			productPricePretaxed *= group_reduction;
		}

		$('#our_price_display').text(formatCurrency(priceProduct, currencyFormat, currencySign, currencyBlank));
		$('#pretaxe_price_display').text(formatCurrency(productPricePretaxed, currencyFormat, currencySign, currencyBlank));
		$('#old_price_display').text(formatCurrency(productPriceWithoutReduction2, currencyFormat, currencySign, currencyBlank));
		$('#ecotax_price_display').text(formatCurrency(selectedCombination['ecotax'], currencyFormat, currencySign, currencyBlank));
	}*/
}

//add a combination of attributes in the global JS sytem
function addCombination(idCombination, arrayOfIdAttributes, quantity, price, ecotax, id_image, reference)
{
	globalQuantity += quantity;

	var combination = new Array();
	combination['idCombination'] = idCombination;
	combination['quantity'] = quantity;
	combination['idsAttributes'] = arrayOfIdAttributes;
	combination['price'] = price;
	combination['ecotax'] = ecotax;
	combination['image'] = id_image;
	combination['reference'] = reference;
	combinations.push(combination);
}
/*
// search the combinations' case of attributes and update displaying of availability, prices, ecotax, and image
function findCombination(firstTime)
{
	//create a temporary 'choice' array containing the choices of the customer
	var choice = new Array();
	$('div#attributes select').each(function(){
		choice.push($(this).val());
	});
	var nbAttributesEquals = 0;
	//testing every combination to find the conbination's attributes' case of the user

	for (combination in combinations)
	{
		//verify if this combinaison is the same that the user's choice
		nbAttributesEquals = 0;
		for (idAttribute in combinations[combination]['idsAttributes'])
		{
			//ie6 bug fix
			if (idAttribute != 'indexOf'){
				//if this attribute has been choose by user
				if (in_array(combinations[combination]['idsAttributes'][idAttribute], choice))
				{
					//we are in a good way to find the good combination !
					nbAttributesEquals++;
				}
			}
		}

		if (nbAttributesEquals == choice.length)
		{
			//combination of the user has been found in our specifications of combinations (created in back office)
			selectedCombination['unavailable'] = false;
			selectedCombination['reference'] = combinations[combination]['reference'];
			$('#idCombination').val(combinations[combination]['idCombination']);

			//get the data of product with these attributes
			quantityAvailable = combinations[combination]['quantity'];
			selectedCombination['price'] = combinations[combination]['price'];
			/*if (combinations[combination]['ecotax'])
				selectedCombination['ecotax'] = combinations[combination]['ecotax'];
			else
				selectedCombination['ecotax'] = default_eco_tax;

			//show the large image in relation to the selected combination

			if (combinations[combination]['image'] && combinations[combination]['image'] != -1)
				$('#thumb_'+combinations[combination]['image']).parent().click();

			//update the display
			updateDisplay();

			if(typeof(firstTime) != 'undefined' && firstTime)
				refreshProductImages(0);
			else
				refreshProductImages(combinations[combination]['idCombination']);
			//leave the function because combination has been found
			return;
		}

	}
	//this combination doesn't exist (not created in back office)
//	selectedCombination['unavailable'] = true;
//	updateDisplay();
}*/

// search the combinations' case of attributes and update displaying of availability, prices, ecotax, and image
function findCombination(firstTime)
{
	//create a temporary 'choice' array containing the choices of the customer
	var choice = new Array();
	$('div#attributes select').each(function(){
		choice.push($(this).val());
	});
	var nbAttributesEquals = 0;
	//testing every combination to find the conbination's attributes' case of the user
//alert(choice);
	for (combination in combinations)
	{
		//verify if this combinaison is the same that the user's choice
		nbAttributesEquals = 0;
		for (idAttribute in combinations[combination]['idsAttributes'])
		{
			//ie6 bug fix
			if (idAttribute != 'indexOf'){
				//if this attribute has been choose by user
				//alert(choice);
				if (in_array(combinations[combination]['idsAttributes'][idAttribute], choice))
				{
					//we are in a good way to find the good combination !
					nbAttributesEquals++;
				}
			}
		}
//		if(!firstTime && onchange && nbAttributesEquals != choice.length)
//		{
//			if(combinations[combination]['idCombination'] == ipa[id_attribute] )
//			{
//			$('#thumb_'+combinations[combination]['image']).parent().click();
//			refreshProductImages(ipa[id_attribute]);}
//		}


		if (nbAttributesEquals == choice.length)
		{//alert("choice="+choice);
			//alert(nbAttributesEquals);
			//combination of the user has been found in our specifications of combinations (created in back office)
			selectedCombination['unavailable'] = false;
			selectedCombination['reference'] = combinations[combination]['reference'];
//alert(combinations[combination]['idCombination']);

			if(!firstTime){
				$('#idCombination').val(combinations[combination]['idCombination']);}

			//get the data of product with these attributes
			quantityAvailable = combinations[combination]['quantity'];
			selectedCombination['price'] = combinations[combination]['price'];
			/*if (combinations[combination]['ecotax'])
				selectedCombination['ecotax'] = combinations[combination]['ecotax'];
			else
				selectedCombination['ecotax'] = default_eco_tax;*/

			//show the large image in relation to the selected combination
			if(!firstTime)
			{
				if (combinations[combination]['image'] && combinations[combination]['image'] != -1)
					$('#thumb_'+combinations[combination]['image']).parent().click();
			}
			//update the display
			updateDisplay();
			if(!firstTime)
				refreshProductImages(combinations[combination]['idCombination']);
			//leave the function because combination has been found
			//return;
		}

		if(typeof(firstTime) != 'undefined' && firstTime)
		{
			if(url_product_attribute != 'undefined')
			{
				if (combinations[combination]['image'] && combinations[combination]['image'] != -1)
				{
					if(combinations[combination]['idCombination'] == url_product_attribute )
					{
						$('#thumb_'+combinations[combination]['image']).parent().click();
					}
					refreshProductImages(url_product_attribute);
					for (idAttribute in combinations[combination]['idsAttributes'])
					{
						if(combinations[combination]['idCombination'] == url_product_attribute )
							var attribute=combinations[combination]['idsAttributes'][idAttribute];
					}
					$('#color_'+attribute).parent().addClass('picked');
					if(color_shoe_combination == 0)
						$('#idCombination').val(url_product_attribute);
					//$('#group_'+2+' option[value='+attribute+']').attr('selected', 'selected');

				}
			}
			else
			{
				refreshProductImages(0);
				if (nbAttributesEquals == choice.length ){
				$('#idCombination').val(combinations[combination]['idCombination']);}
			}
		}
	}
	/*if(firstTime && url_product_attribute != 'undefined' && color_shoe_combination == 1 )
	{
		var sizes = shoesizes[attribute];

		var item_values = new Array();
		$(sizes).each(function (key, item){
			item_values[item.id] = new Array(item.size,item.quantity,item.ipa);
		});
		var csd = false;
		var defaultsize = false;
		for(var key in item_values)
			{
				if(customerShoeSizeDefault == item_values[key][0])
				{
					if(item_values[key][1]>0)
					{
						$('#choice_'+key).parent().addClass('picked');
						$('#idCombination').val(item_values[key][2]);
						csd = true;
						defaultsize = true;
					}
				    else
						$('#choice_'+key).parent().addClass('no_stock');
					break;
				}

			}
			if(csd == false)
				for(var key in item_values)
				{
					if(item_values[key][0]==default_shoesize)
					{
						if(item_values[key][1]>0)
						{
							$('#choice_'+key).parent().addClass('picked');
							$('#idCombination').val(item_values[key][2]);
							defaultsize = true;
						}
						else
							$('#choice_'+key).parent().addClass('no_stock');
						break;
					}
				}
			if(defaultsize== false)
				for(var key in item_values)
				{
					if(item_values[key][1]>0)
					{
						$('#choice_'+key).parent().addClass('picked');
						$('#idCombination').val(item_values[key][2]);
						break;
					}
				}
	}*/

	//this combination doesn't exist (not created in back office)
	selectedCombination['unavailable'] = true;
	updateDisplay();
}


function updateProductChoiceSelect(id_attribute, id_group)
{
	//if (id_attribute == 0)
	{
		//refreshProductImages(0);
		//return ;
	}
	// Visual effect
	$('#choices_group_'+id_group+' ul.product_choices li').removeClass('picked');
	$('#choice_'+id_attribute).parent().addClass('picked');
	$('#choice_'+id_attribute).fadeTo('fast', 1, function(){$(this).fadeTo('normal', 0, function(){$(this).fadeTo('normal', 1, function(){});});});

	if($('#group_'+id_group+' option[value='+id_attribute+']').length > 0)
	{
		$('#group_'+id_group+' option[value='+id_attribute+']').attr('selected', 'selected');
		$('#group_'+id_group+' option[value!='+id_attribute+']').removeAttr('selected');
		findCombination(true);

		// Enable Check out
		$('.out-of-stock').addClass('hidden');
		$('#check_out_btn').removeClass('hidden');
	}
	else
	{
		// Disable Check out as no stock
		$('.out-of-stock').removeClass('hidden');
		$('#check_out_btn').addClass('hidden');
	}
}

//function updateColorSelect(id_attribute)
//{
//	if (id_attribute == 0)
//	{
//		refreshProductImages(0);
//		return ;
//	}
//	// Visual effect
//	$('#color_to_pick_list li').removeClass('picked');
//	$('#color_'+id_attribute).parent().addClass('picked');
//	$('#color_'+id_attribute).fadeTo('fast', 1, function(){$(this).fadeTo('normal', 0, function(){$(this).fadeTo('normal', 1, function(){});});});
//
//	if($('#group_'+id_color_default+' option[value='+id_attribute+']').length > 0)
//	{
//		// Attribute selection
//		$('#group_'+id_color_default+' option[value='+id_attribute+']').attr('selected', 'selected');
//		$('#group_'+id_color_default+' option[value!='+id_attribute+']').removeAttr('selected');
//		findCombination();
//
//		// Enable Check out
//		$('.out-of-stock').addClass('hidden');
//		$('#check_out_btn').removeClass('hidden');
//	}
//	else
//	{
//		// Disable Check out as no stock
//		$('.out-of-stock').removeClass('hidden');
//		$('#check_out_btn').addClass('hidden');
//	}
//}

// Serialscroll exclude option bug ?
function serialScrollFixLock(event, targeted, scrolled, items, position)
{
	serialScrollNbImages = $('#thumbs_list li:visible').length;
	serialScrollNbImagesDisplayed = 5;

	var leftArrow = position == 0 ? true : false;
	var rightArrow = position + serialScrollNbImagesDisplayed >= serialScrollNbImages ? true : false;

	$('a#view_scroll_left').css('cursor', leftArrow ? 'default' : 'pointer').css('display', leftArrow ? 'none' : 'block').fadeTo(0, leftArrow ? 0 : 1);
	$('a#view_scroll_right').css('cursor', rightArrow ? 'default' : 'pointer').fadeTo(0, rightArrow ? 0 : 1).css('display', rightArrow ? 'none' : 'block');
	return true;
}

// Serialscroll exclude option bug ?
function accessorySerialScrollFixLock(event, targeted, scrolled, items, position)
{
	serialScrollNbImages = $('#product-recommend-thumbs-list li:visible').length;
	serialScrollNbImagesDisplayed = 3;

	var leftArrow = position == 0 ? true : false;
	var rightArrow = position + serialScrollNbImagesDisplayed >= serialScrollNbImages ? true : false;

	/*$('a#accessory-btn-scroll-left').css('cursor', leftArrow ? 'default' : 'pointer').css('display', leftArrow ? 'none' : 'block').fadeTo(0, leftArrow ? 0 : 1);
	$('a#accessory-btn-scroll-right').css('cursor', rightArrow ? 'default' : 'pointer').fadeTo(0, rightArrow ? 0 : 1).css('display', rightArrow ? 'none' : 'block');
	*/return true;
}

// Change the current product images regarding the combination selected
function refreshProductImages(id_product_attribute)
{
	$('#thumbs_list_frame').scrollTo('li:eq(0)', 700, {axis:'y'});
	$('#thumbs_list li').hide();
	id_product_attribute = parseInt(id_product_attribute);

	if (typeof(combinationImages) != 'undefined' && typeof(combinationImages[id_product_attribute]) != 'undefined')
	{
		for (var i = 0; i < combinationImages[id_product_attribute].length; i++)
			$('#thumbnail_' + parseInt(combinationImages[id_product_attribute][i])).show();
	}
	if (i > 0)
		$('#thumbs_list_frame').width( (parseInt(($('#thumbs_list_frame >li').width() + 9)* i)) + 'px'); //  Bug IE6, needs 3 pixels more ?
	$('#thumbs_list').trigger('goto', 0);
	serialScrollFixLock('', '', '', '', 0);// SerialScroll Bug on goto 0 ?
	accessorySerialScrollFixLock('', '', '', '', 0);// SerialScroll Bug on goto 0 ?
}

//To do after loading HTML
$(document).ready(function()
{
	//init the serialScroll for thumbs
	$('#thumbs_list').serialScroll({
		items:'li',
		prev: 'a#btn_scroll_up',
		next: 'a#btn_scroll_down',
		axis: 'y',
		onBefore:serialScrollFixLock,
		step: 1,
		lazy: false,
		lock: false,
		cycle:false,
		stop: true,
		force:false
	});
	$('#thumbs_list ul').trigger('goto', 1);// SerialScroll Bug on goto 0 ?
	$('#thumbs_list ul').trigger('goto', 0);

	//init the serialScroll for product-recommend thumbs
	$('#product-recommend-thumbs-list').serialScroll({
		items:'li',
		prev: 'a#product-recommend-btn-scroll-left',
		next: 'a#product-recommend-btn-scroll-right',
		axis: 'x',
		onBefore:accessorySerialScrollFixLock,
		step: 1,
		lazy: false,
		lock: false,
		cycle:false,
		stop: true,
		force:false
	});
	$('#product-recommend-thumbs-list ul').trigger('goto', 1);// SerialScroll Bug on goto 0 ?
	$('#product-recommend-thumbs-list ul').trigger('goto', 0);


	//init the price in relation of the selected attributes
	if (typeof productHasAttributes != 'undefined' && productHasAttributes)
		findCombination(true);

	$('a#resetImages').click(function() {
		updateColorSelect(0);
	});

    // Accordion
    $('#product_accordion').accordion({
    	heightStyle: 'content'
    });

    var untilTime = $("#shipping_time").attr("class");
    $("#shipping_time").countdown({
                until: untilTime,
                format: 'DHMS',
                compact: true,
                description: ''
    });

    /*Accessories Products*/
    $('.accessories-products').mouseup(function(){
        var productId = $(this).attr('productId');
        var accessoryProductAttributeId = $(this).attr('productAttributeId');
        $('input[name="accessorieProduct"]').each(function(){
            if($(this).val() == productId){
                $(this).attr('checked','checked');
                $('input[name="accessoryProductId"]').attr('value',productId);
                $('input[name="accessoryProductAttributeId"]').attr('value',accessoryProductAttributeId);
            }
        });
    });

});

function saveCustomization()
{
	$('#quantityBackup').val($('#quantity_wanted').val());
	customAction = $('#customizationForm').attr('action');
	$('body select[@id^="group_"]').each(function() {
		customAction = customAction.replace(new RegExp(this.id + '=\\d+'), this.id +'='+this.value);
	});
	$('#customizationForm').attr('action', customAction);
	$('#customizationForm').submit();
}

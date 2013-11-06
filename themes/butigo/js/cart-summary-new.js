/*
* 2007-2011 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2011 PrestaShop SA
*  @version  Release: $Revision: 7357 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

$(document).ready(function(){
    // If block cart isn't used, we don't bind the handle actions
    //if (window.ajaxCart !== undefined)
    {
            /*The below code is commented to disable the ajax method of Adding/Deleting/Decreasing the quantity of the product*/
            /*$('.cart_quantity_up').unbind('click').click(function(){ upQuantity($(this).attr('id').replace('cart_quantity_up_', '')); return false;	});
            $('.cart_quantity_down').unbind('click').click(function(){ downQuantity($(this).attr('id').replace('cart_quantity_down_', '')); return false; });
            $('.cart_quantity_delete').unbind('click').click(function(){ deletProductFromSummary($(this).attr('id')); return false; });
            $('.qty-vary').typeWatch({ highlight: true, wait: 600, captureLength: 0, callback: updateQty });*/
    }

    /*To check the numberofaccessories are added for each AccessorisedProduct*/
    $('.cart_navigation input[name="processAddress"]').click(function() {
        var numberOfAccessorisedProducts = parseInt($('input[name="numberOfAccessorisedProducts"]').val(), 10);
        var numberOfAccessories = parseInt($('input[name="numberOfAccessories"]').val(), 10);

        if (numberOfAccessories < numberOfAccessorisedProducts) {
            alert(accessoryErrorMessage + (numberOfAccessorisedProducts - numberOfAccessories));

            return false;
        }

        return true;
    });

    /*Slider for the accessories at the cart page*/
    $('#slider1').tinycarousel({
        duration: 300,
        controls: true
    });

    /*Checking the qty*/
    $('.qty-vary').change(function() {
        var entered_qty = parseInt($(this).val());
        var curr_qty = parseInt($(this).parent().children('input[name=current_qty]').val());

        if( entered_qty >= curr_qty ) {
            var qty = entered_qty - curr_qty;
            $(this).parent().children('input[name=qty]').val(qty);
            $(this).parent().children('input[name=op]').val('up');
        } else {
            var qty = curr_qty - entered_qty;
            $(this).parent().children('input[name=qty]').val(qty);
            $(this).parent().children('input[name=op]').val('down');
        }
    });
    $('.qty-refresh').click(function() {
        var entered_qty = parseInt($(this).parent().children('input[name=entered_qty]').val());
        var curr_qty = parseInt($(this).parent().children('input[name=current_qty]').val());

        if( isNaN(entered_qty) || entered_qty == undefined ) {
            alert(qty_not_null);
            $(this).parent().children('input[name=entered_qty]').val(curr_qty);
            return false;
        }
        if(curr_qty == entered_qty) {
            alert(qty_are_same);
            return false;
        }
    });
});

/*function updateQty(val){
    var id = $(this.el).attr('name');
    var exp = new RegExp("^[0-9]+$");

    if (exp.test(val) == true){
        var hidden = $('input[name='+ id +'_hidden]').val();
        var input = $('input[name='+ id +']').val();
        var QtyToUp = parseInt(input) - parseInt(hidden);

        if (parseInt(QtyToUp) > 0){
            upQuantity(id.replace('quantity_', ''),QtyToUp);
        }
        else if(parseInt(QtyToUp) < 0){
            downQuantity(id.replace('quantity_', ''),QtyToUp);
        }
    }else{
        $('input[name='+ id +']').val($('input[name='+ id +'_hidden]').val());
    }
}

function deletProductFromSummary(id)
{
	var customizationId = 0;
	var productId = 0;
	var productAttributeId = 0;
	var ids = 0;
	var x,y;
	var discount_value = 0.00;
	ids = id.split('_');
	productId = parseInt(ids[0]);
	if (typeof(ids[1]) != 'undefined'){
            productAttributeId = parseInt(ids[1]);
        }
	if (typeof(ids[2]) != 'undefined'){
            customizationId = parseInt(ids[2]);
        }
    $.ajax({
       type: 'GET',
       url: baseDir + 'cart.php',
       async: true,
       cache: false,
       dataType: 'json',
       data: 'ajax=true&delete&summary&id_product='+productId+'&ipa='+productAttributeId+ ( (customizationId != 0) ? '&id_customization='+customizationId : '') + '&token=' + static_token ,
       success: function(jsonData)
       {
       		if (jsonData.hasError){
                    var errors = '';
                    for(error in jsonData.errors)
                        //IE6 bug fix
                        if(error != 'indexOf')
                                errors += jsonData.errors[error] + "\n";
    		}
    		else
    		{
				updateCartSummary(jsonData.summary);
				updateHookShoppingCart(jsonData.HOOK_SHOPPING_CART);
				updateHookShoppingCartExtra(jsonData.HOOK_SHOPPING_CART_EXTRA);
				updateCustomizedDatas(jsonData.customizedDatas);
				if (jsonData.carriers != null)
					updateCarrierList(jsonData);

    		}
       	},
       error: function(XMLHttpRequest, textStatus, errorThrown) {alert("TECHNICAL ERROR: unable to save update quantity \n\nDetails:\nError thrown: " + XMLHttpRequest + "\n" + 'Text status: ' + textStatus);}
   });
}

function upQuantity(id, qty)
{
	if(typeof(qty)=='undefined' || !qty)
		qty = 1;
	var customizationId = 0;
	var productId = 0;
	var productAttributeId = 0;
	var ids = 0;
	ids = id.split('_');
	productId = parseInt(ids[0]);
	if (typeof(ids[1]) != 'undefined')
		productAttributeId = parseInt(ids[1]);
	if (typeof(ids[2]) != 'undefined')
		customizationId = parseInt(ids[2]);
	$.ajax({
       type: 'GET',
       url: baseDir + 'cart.php',
       async: true,
       cache: false,
       dataType: 'json',
       data: 'ajax=true&add&summary&id_product='+productId+'&ipa='+productAttributeId + ( (customizationId != 0) ? '&id_customization='+customizationId : '') + '&qty='+qty+'&token=' + static_token ,
       success: function(jsonData)
       {
       		if (jsonData.hasError)
    		{
    			var errors = '';
    			for(error in jsonData.errors)
    				//IE6 bug fix
    				if(error != 'indexOf')
    					errors += jsonData.errors[error] + "\n";
    			alert(errors);
    			$('input[name=quantity_'+ id +']').val($('input[name=quantity_'+ id +'_hidden]').val());
       		}
    		else
    		{
    			updateCustomizedDatas(jsonData.customizedDatas);
    			updateCartSummary(jsonData.summary);
    			updateHookShoppingCart(jsonData.HOOK_SHOPPING_CART);
				updateHookShoppingCartExtra(jsonData.HOOK_SHOPPING_CART_EXTRA);
	    		if (jsonData.carriers != null)
					updateCarrierList(jsonData);
    		}
    	},
       error: function(XMLHttpRequest, textStatus, errorThrown) {alert("TECHNICAL ERROR: unable to save update quantity \n\nDetails:\nError thrown: " + XMLHttpRequest + "\n" + 'Text status: ' + textStatus);}
   });
}

function downQuantity(id, qty)
{
	var val = $('input[name=quantity_'+id+']').val();
	var newVal = val;
	if(typeof(qty)=='undefined' || !qty)
	{
		qty = 1;
		newVal = val - 1;
	}
	else if (qty < 0)
    	qty = -qty;
	var customizationId = 0;
	var productId = 0;
	var productAttributeId = 0;
	var ids = 0;
	if (newVal > 0)
	{
		ids = id.split('_');
		productId = parseInt(ids[0]);
		if (typeof(ids[1]) != 'undefined')
			productAttributeId = parseInt(ids[1]);
		if (typeof(ids[2]) != 'undefined')
			customizationId = parseInt(ids[2]);
		$.ajax({
	       type: 'GET',
	       url: baseDir + 'cart.php',
	       async: true,
	       cache: false,
	       dataType: 'json',
	       data: 'ajax=true&add&summary&id_product='+productId+'&ipa='+productAttributeId+'&op=down' + ( (customizationId != 0) ? '&id_customization='+customizationId : '') + '&qty='+qty+'&token=' + static_token ,
	       success: function(jsonData)
	       {
	       		if (jsonData.hasError)
	    		{
	    			var errors = '';
	    			for(error in jsonData.errors)
	    				//IE6 bug fix
	    				if(error != 'indexOf')
	    					errors += jsonData.errors[error] + "\n";
	    			alert(errors);
	    			$('input[name=quantity_'+ id +']').val($('input[name=quantity_'+ id +'_hidden]').val());
	    		}
	    		else
	    		{
	    			updateCustomizedDatas(jsonData.customizedDatas);
	    			updateCartSummary(jsonData.summary);
	    			updateHookShoppingCart(jsonData.HOOK_SHOPPING_CART);
					updateHookShoppingCartExtra(jsonData.HOOK_SHOPPING_CART_EXTRA);
	    			if (jsonData.carriers != null)
						updateCarrierList(jsonData);
	    		}
	    	},
	       error: function(XMLHttpRequest, textStatus, errorThrown) {alert("TECHNICAL ERROR: unable to save update quantity \n\nDetails:\nError thrown: " + XMLHttpRequest + "\n" + 'Text status: ' + textStatus);}
	   });

	}
	else
	{
		deletProductFromSummary(id);
	}
}

function updateCustomizedDatas(json){
    for(i in json)
        for(j in json[i])
            for(k in json[i][j]){
                    $('input[name=quantity_'+i+'_'+j+'_'+k+'_hidden]').val(json[i][j][k]['quantity']);
                    $('input[name=quantity_'+i+'_'+j+'_'+k+']').val(json[i][j][k]['quantity']);
            }
}*/

function updateHookShoppingCart(html){
    $('#HOOK_SHOPPING_CART').html(html);
}

function updateHookShoppingCartExtra(html){
    $('#HOOK_SHOPPING_CART_EXTRA').html(html);
}


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
* Do not edit or add to this file if you fav to upgrade PrestaShop to newer
* versions in the future. If you fav to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2011 PrestaShop SA
*  @version  Release: $Revision: 6594 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

/**
 * Update FavList Cart by adding, deleting, updating objects
 *
 * @return void
 */
function FavlistCart(id, action, id_product, id_product_attribute, quantity)
{
	$.ajax({
		type: 'GET',
		url:	baseDir + 'modules/blockfavourites/cart.php',
		async: true,
		cache: false,
		data: 'action=' + action + '&id_product=' + id_product + '&quantity=' + quantity + '&token=' + static_token + '&id_product_attribute=' + id_product_attribute,
		success: function(data)
		{
			var str,fav_text,qv_str,qv_fav_text;

				if(id == 'mutiple_products' || id == 'qv_single_product' || id == 'qv_mutiple_products')
				 {
					str = "_"+id_product+"_"+id_product_attribute;
					fav_text ='';
					//var test = "'+'+id+'";
				 }
				 else if(id == 'single_product')
				 {
					str='';
					fav_text = '<span id="fav_text">Favorilerimden Kaldır</span>';
				 }

				if( id == 'qv_mutiple_products' )
				{
					qv_str = "_"+id_product+"_"+id_product_attribute;
				}
				else if(id == 'qv_single_product')
				{
					qv_str='';
				}

			/*For products in showroom, lookbooks, celebrity page*/
				//if(id == 'mutiple_products' || id == 'single_product' || id == 'qv_single_product' || id == 'qv_mutiple_products')
				{
					$("#to_fav"+str).remove();
					var faved = '<a href="javascript:;" id="faved'+str+'"  class="favorite_flag in_myfavorite"  onclick="FavProductRemove(\''+id+'\', \'delete\', '+id_product+','+id_product_attribute+')">'+fav_text+'</a>';
					$("#ajax_response"+str).append(faved);
				}
		   /*For products in showroom, lookbooks, celebrity page*/

			/*For products in qiuck view*/
				if(id == 'qv_single_product' || id == 'qv_mutiple_products')
				{
					qv_fav_text = '<span id="fav_text">Favorilerimden Kaldır</span>';
					$("#qv_to_fav"+qv_str).remove();
					var faved = '<a href="javascript:;" id="qv_faved'+qv_str+'"  class="qv_favorite_flag in_myfavorite"  onclick="FavProductRemove(\''+id+'\', \'delete\', '+id_product+','+id_product_attribute+')">'+qv_fav_text+'</a>';
					$("#qv_ajax_response"+qv_str).append(faved);
				}
			/*For products in quickview*/
			
		}
	});
}

/**
 * Change customer default favlist
 *
 * @return void
 */
/*function FavlistChangeDefault(id, id_favlist)
{
	$.ajax({
		type: 'GET',
		url:	baseDir + 'modules/blockfavourites/cart.php',
		async: true,
		data: 'id_favlist=' + id_favlist + '&token=' + static_token,
		cache: false,
		success: function(data)
		{
			$('#' + id).slideUp('normal');
			document.getElementById(id).innerHTML = data;
			$('#' + id).slideDown('normal');
		}
	});
}*/

/**
 * Buy Product
 *
 * @return void
 */

/**
function FavlistBuyProduct(token, id_product, id_product_attribute, id_quantity, button, ajax)
{
	if(ajax)
		ajaxCart.add(id_product, id_product_attribute, false, button, 1, [token, id_quantity]);
	else
	{
		$('#' + id_quantity).val(0);
		FavlistAddProductCart(token, id_product, id_product_attribute, id_quantity)
		document.forms['addtocart' + '_' + id_product  + '_' + id_product_attribute].method='POST';
		document.forms['addtocart' + '_' + id_product  + '_' + id_product_attribute].action=baseDir + 'cart.php';
		document.forms['addtocart' + '_' + id_product  + '_' + id_product_attribute].elements['token'].value = static_token;
		document.forms['addtocart' + '_' + id_product  + '_' + id_product_attribute].submit();
	}
	return (true);
}

function FavlistAddProductCart(token, id_product, id_product_attribute, id_quantity)
{
	if ($('#' + id_quantity).val() <= 0)
		return (false);
	$.ajax({
		type: 'GET',
		url: baseDir + 'modules/blockfavourites/buyfavlistproduct.php',
		data: 'token=' + token + '&static_token=' + static_token + '&id_product=' + id_product  + '&id_product_attribute=' + id_product_attribute,
		async: true,
		cache: false, 
		success: function(data)
		{
			if (data)
				alert(data);
			else
			{
				$('#' + id_quantity).val($('#' + id_quantity).val() - 1);
			}
		}
	});
	return (true);
}*/

/**
 * Show favlist managment page
 *
 * @return void
 */
/*
function FavlistManage(id, id_favlist)
{
	$.ajax({
		type: 'GET',
		async: true,
		url: baseDir + 'modules/blockfavourites/managefavlist.php',
		data: 'id_favlist=' + id_favlist + '&refresh=' + false,
		cache: false,
		success: function(data)
		{
			$('#' + id).hide();
			document.getElementById(id).innerHTML = data;
			$('#' + id).fadeIn('slow');
		}
	});
}*/

/**
 * Show favlist product managment page
 *
 * @return void
 */
function FavlistProductManage(id, action, id_favlist, id_product, id_product_attribute, quantity, priority)
{
	$.ajax({
		type: 'GET',
		async: true,
		url: baseDir + 'modules/blockfavourites/managefavlist.php',
		data: 'action=' + action + '&id_favlist=' + id_favlist + '&id_product=' + id_product + '&id_product_attribute=' + id_product_attribute + '&quantity=' + quantity + '&priority=' + priority + '&refresh=' + true,
		cache: false,
		success: function(data)
		{
			if (action == 'delete')
				$('#'+ id + '_' + id_product + '_' + id_product_attribute).remove();
			if( $(".showroom_sel_cntnr").length <1)
			{
				$('#customer_favourite_shoes').remove();
				$('#fav_empty').removeClass('hidden');
			}
			/*else if (action == 'update')
			{
				$('#wlp_' + id_product + '_' + id_product_attribute).fadeOut('fast');
				$('#wlp_' + id_product + '_' + id_product_attribute).fadeIn('fast');
			}*/
		}
	});
}

function FavProductRemove(id, action, id_product, id_product_attribute)
{
	$.ajax({
		type: 'GET',
		async: true,
		url: baseDir + 'modules/blockfavourites/managefavlist.php',
		data: 'action=' + action + '&id_product=' + id_product + '&id_product_attribute=' + id_product_attribute,
		cache: false,
		success: function(data)
		{
			var str,fav_text,qv_str,qv_fav_text,str1,str2;
			if(id == 'mutiple_products' || id == 'qv_mutiple_products' || id == 'qv_single_product')
		     {
				str = "_"+id_product+"_"+id_product_attribute;
				fav_text='';
				str1 = '';
				str2='';
			 }
			 else if(id == 'single_product')
			 {
				str='';
				//fav_text = '<span id="fav_text">Favorilerfime Ekle</span>';
				fav_text = '<span id="fav_text" style="display: none;">Favorilerfime<br>Ekle<img width="5" height="10" alt="Pinkarrow" class="arrow" src="'+baseDir+'themes/butigo/img/buttons/pinkarrow.png"></span>';
				str1 = 'onmouseover = "$(\'#fav_text\').show(); return false"';
				str2 = 'onmouseout = "$(\'#fav_text\').hide(); return false"';
			 }

			if( id == 'qv_mutiple_products' )
			{
				qv_str = "_"+id_product+"_"+id_product_attribute;
			}
			else if(id == 'qv_single_product')
			{
				qv_str='';
			}
			
			$("#faved"+str).remove();
			var tofav = '<a href="#" id="to_fav'+str+'"  class="favorite_flag"  onclick="FavlistCart(\''+id+'\', \'add\', '+id_product+', '+id_product_attribute+', 1);return false;" '+str1+' '+str2+'>'+fav_text+'</a>';
			$("#ajax_response"+str).append(tofav);

			if(id == 'qv_mutiple_products' || id == 'qv_single_product')
			{
				qv_fav_text =' <span id="fav_text">Favorilerime Ekle</span>';
				$("#qv_faved"+qv_str).remove();
				var tofav = '<a href="#" id="qv_to_fav'+qv_str+'"  class="qv_favorite_flag"  onclick="FavlistCart(\''+id+'\', \'add\', '+id_product+', '+id_product_attribute+', 1);return false;">'+qv_fav_text+'</a>';
				$("#qv_ajax_response"+qv_str).append(tofav);

				if (action == 'delete')
					$('#fav_delete_' + id_product + '_' + id_product_attribute).remove();
			}

			// $("#qv_faved"+str).remove();
			 
			/*$('#faved_'+id_product+'_'+id_product_attribute).remove();
			var tofav = '<a href="#" id="to_fav_'+id_product+'_'+id_product_attribute+'" class="favorite_flag"  onclick="FavlistCart(\'favourite_block_list\', \'add\', '+id_product+', '+id_product_attribute+', 1) ; return false;"></a>';
			var fav_text = '<span id="fav_text">Add to Favourites</span>';
			if(fav_text == true)
				$(tofav).append(fav_text);
			$('#ajax_response_'+id_product+'_'+id_product_attribute).append(tofav);*/
			
			/*if (action == 'delete')
				$('#wlp_' + id_product + '_' + id_product_attribute).fadeOut('fast');
			else if (action == 'update')
			{
				$('#wlp_' + id_product + '_' + id_product_attribute).fadeOut('fast');
				$('#wlp_' + id_product + '_' + id_product_attribute).fadeIn('fast');
			}*/
		}
	});
}

/**
 * Delete favlist
 *
 * @return boolean succeed
 */
/*
function FavlistDelete(id, id_favlist, msg)
{
	var res = confirm(msg);
	if (res == false)
		return (false);
	$.ajax({
		type: 'GET',
		async: true,
		url: baseDir + 'modules/blockfavourites/myfavourites.php',
		cache: false,
		data: 'deleted&id_favlist=' + id_favlist,
		success: function(data)
		{
			$('#' + id).fadeOut('slow');
		}
	});
}*/

/**
 * Hide/Show bought product
 *
 * @return void
 */
/*function FavlistVisibility(bought_class, id_button)
{
	if ($('#hide' + id_button).css('display') == 'none')
	{
		$('.' + bought_class).slideDown('fast');
		$('#show' + id_button).hide();
		$('#hide' + id_button).fadeIn('fast');
	}
	else
	{
		$('.' + bought_class).slideUp('fast');
		$('#hide' + id_button).hide();
		$('#show' + id_button).fadeIn('fast');
	}
}*/

/**
 * Send favlist by email
 *
 * @return void
 */
/*function FavlistSend(id, id_favlist, id_email)
{
	$.post(baseDir + 'modules/blockfavourites/sendfavlist.php',
	{ token: static_token,
	  id_favlist: id_favlist,
	  email1: $('#' + id_email + '1').val(),
	  email2: $('#' + id_email + '2').val(),
	  email3: $('#' + id_email + '3').val(),
	  email4: $('#' + id_email + '4').val(),
	  email5: $('#' + id_email + '5').val(),
	  email6: $('#' + id_email + '6').val(),
	  email7: $('#' + id_email + '7').val(),
	  email8: $('#' + id_email + '8').val(),
	  email9: $('#' + id_email + '9').val(),
	  email10: $('#' + id_email + '10').val() },
	function(data)
	{
		if (data)
			alert(data);
		else
			FavlistVisibility(id, 'hideSendFavlist');
	});
}*/

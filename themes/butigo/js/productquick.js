var selectedCombination = new Array();

// search the combinations' case of attributes and update displaying of availability, prices, ecotax, and image
function findCombination(firstTime,id_color_attr,second_click_ipa)
{

//	alert(firstTime+"--"+id_color_attr+"--"+ipa+"--"+color_flag);
	if(!firstTime || !color_flag)
	{
		var choice = new Array();
		if(id_color_attr)
		{ //alert("test");
			$('div#attributes_'+id_color_attr+' select').each(function(){
				choice.push($(this).val());
			});
		}else{ //alert("test1");
			$('div#attributes select').each(function(){
				choice.push($(this).val());
			});
		}
		//alert("ch="+choice);
	}
	for (var combination in combinations)
	{
		var nbAttributesEquals = 0;
		if(!firstTime || !color_flag)
		{ //alert("test2");
			//alert(combinations[combination]['idCombination']+"--"+url_product_attribute);
			for (var key in combinations[combination]['idsAttributes'])
			{
				//ie6 bug fix
				if (key != 'indexOf'){
					//if this attribute has been choose by user
					if (in_array(combinations[combination]['idsAttributes'][key], choice))
					{
						//we are in a good way to find the good combination !
						nbAttributesEquals++;
					}
				}
			}
			//alert("ae="+nbAttributesEquals);alert("length="+choice.length);
			if (nbAttributesEquals == choice.length)
			{
				if(color_flag && (url_product_attribute != 'undefined' || second_click_ipa))
				{ //alert("test3");
					$('#idCombination_'+id_color_attr).val(combinations[combination]['idCombination']);
				}else{  //alert("test4");
					$('#idCombination').val(combinations[combination]['idCombination']);
				}
			}
		}
		//attribute is color id chosen by the user
		if(firstTime && color_flag) 
		for (var idAttribute in combinations[combination]['idsAttributes'])
		{//alert("hello");
			if(combinations[combination]['idCombination'] == url_product_attribute)
				var attribute=combinations[combination]['idsAttributes'][idAttribute];
			$('#idCombination_'+attribute).val(url_product_attribute);
		}
	}
	
	if(color_flag)
	{   
		// alert("url"+url_product_attribute);alert("att"+attribute);
		//if(!firstTime){ var attribute = id_color_attr; }
		
		if(second_click_ipa){ attribute = id_color_attr; }
		
		if(firstTime || second_click_ipa)
		{
			//alert("url"+url_product_attribute);alert("att"+attribute);
			if(url_product_attribute != 'undefined' || second_click_ipa)
			{ 
				$('#color_'+attribute+'_'+attribute).parent().addClass('picked');
			}
			
			var sizes = prod_data[attribute];
			var item_values = new Array();
			$(sizes).each(function (key, item){
				item_values[item.id] = new Array(item.size,item.quantity,item.ipa);
			});
			/*var csd = false;
			var defaultsize = false;
			//alert(item_values);
			//if(!second_time){
			for(var key in item_values)
			{
				if(customerShoeSize == item_values[key][0])
				{//alert(attribute);
					if(item_values[key][1]>0)
					{ //alert("test5");
						//alert("iv1="+item_values[key][1]);alert("k3="+key);
						$('#choices_group_'+id_attribute_group+'_'+attribute+' ul.product_choices li').removeClass('picked');
						$('#choice_'+key+'_'+attribute).parent().addClass('picked');
						$('#idCombination_'+attribute).val(item_values[key][2]);
						csd = true;
						defaultsize = true;
					}
					else
						$('#choice_'+key+'_'+attribute).parent().addClass('no_stock');
					break;
				}
			}
			if(csd == false)*/
				for(var key in item_values)
				{
					if(item_values[key][0]==default_shoesize)
					{  //alert("test6");
						if(item_values[key][1]>0)
						{
//							//alert("iv1="+item_values[key][2]);alert("k3="+key);
//							$('#choices_group_'+id_attribute_group+'_'+attribute+' ul.product_choices li').removeClass('picked');
//							$('#choice_'+key+'_'+attribute).parent().addClass('picked');
							$('#idCombination_'+attribute).val(item_values[key][2]);
//							defaultsize = true;
						}
						else
							$('#choice_'+key+'_'+attribute).parent().addClass('no_stock');
						break;
					}
				}
			/*if(defaultsize== false)
				for(var key in item_values)
				{
					if(item_values[key][1]>0)
					{  //alert("test7");
//						//alert("iv1="+item_values[key][2]);alert("k3="+key);
						$('#choices_group_'+id_attribute_group+'_'+attribute+' ul.product_choices li').removeClass('picked');
						$('#choice_'+key+'_'+attribute).parent().addClass('picked');
						$('#idCombination_'+attribute).val(item_values[key][2]);
						break;
					}
				}*/
		}
		//}
	}
	//this combination doesn't exist (not created in back office)
	selectedCombination['unavailable'] = true;
}

function updateProductChoiceSelect(id_attribute, id_group, id_color)
{ //alert("hai");
	if(color_flag){ var str= '#fancy-quick-'+id_color+' ';} else{ var str=''; }
	// Visual effect
	$('#choices_group_'+id_group+'_'+id_color+' ul.product_choices li').removeClass('picked');
	$('#choice_'+id_attribute+'_'+id_color).parent().addClass('picked');
	//$(str+'#choice_'+id_attribute).parent().addClass('picked');
	$('#choice_'+id_attribute+'_'+id_color).fadeTo('fast', 1, function(){$(this).fadeTo('normal', 0, function(){$(this).fadeTo('normal', 1, function(){});});});
	if($('#group_'+id_group+'_'+id_color+' option[value='+id_attribute+']').length > 0)
	{  //alert("hai1");
		$('#group_'+id_group+'_'+id_color+' option[value='+id_attribute+']').attr('selected', 'selected');
		$('#group_'+id_group+'_'+id_color+' option[value!='+id_attribute+']').removeAttr('selected');
		findCombination(false, id_color, false);

		// Enable Check out
		$(str+'.out-of-stock').addClass('hidden');
		$(str+'#check_out_btn').removeClass('hidden');
	}
	else
	{  
		// Disable Check out as no stock
		$(str+'.out-of-stock').removeClass('hidden');
		$(str+'#check_out_btn').addClass('hidden');
	}
}

function updateProductChoiceSelectSingle(id_attribute, id_group)
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
		findCombination();

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



function updateColor(id_attribute, second_click_ipa)
{
$('.fancy-quick').addClass('hidden');
	//$('.fancy-quick').removeClass('fancy');
	$('#fancy-quick-'+id_attribute).removeClass('hidden').show('fast');
	$('#fancy-quick-'+id_attribute).addClass('fancy');
//	$('#fancy-quick-'+id_attribute).removeClass('hidden');
//	$('#fancy-quick-'+id_attribute).addClass('fancy');
//	for(var i=0;i<color_ids.length;i++)
//	{
//		if(id_attribute != color_ids[i])
//		$('#fancy-quick-'+color_ids[i]).addClass('hidden');
//	}
	findCombination(false,id_attribute,second_click_ipa);
//$('#fancy-quick-'+id_attribute).show();
}

findCombination(true);
$(document).ready(function(){		
	
	$('#stock-remainder-size-close').click(function(){
		if(!$('#stock-remainder-alarm-shoe-sizes').hasClass('hidden')){
			$('#stock-remainder-alarm-shoe-sizes').addClass('hidden');
		}		
	});
			
	/*To load the sizes of the recommend-product upon click on Stock Alarm*/
	$('#stock-remainder-alarm').click(function(){
			if(!$('#stock-remainder-alarm-shoe-sizes').is(':parent')){
				var idProduct =  $(this).attr('data-product-id');
				$.ajax({
					type: 'GET',
					url: baseDir + 'modules/stockremainder/stock-remainder-show-size-list.php?action=display&productId='+idProduct,
					async: true,
					cache: false,
					dataType : "html",
					success: function(data)
					{
						if($('#stock-remainder-alarm-shoe-sizes').hasClass('hidden')){
							$('#stock-remainder-alarm-shoe-sizes').removeClass('hidden');
						}	
						$('#stock-remainder-alarm-shoe-sizes').html('');
						$('#stock-remainder-alarm-shoe-sizes').html(data);
						$('.ajax-js').each(function() {
							var jsfile = $(this).attr('jsfile');
							$.ajax({
								type: "GET",
								url: jsfile,
								dataType: "script"
							});
						});
					}
				});
			}else{
				if($('#stock-remainder-alarm-shoe-sizes').hasClass('hidden')){
					$('#stock-remainder-alarm-shoe-sizes').removeClass('hidden');
				}	
			}
			return;
		});
});

/*Storing the customer choosen shoe-size for stock remainder*/
function updateStockRemainderSizeSelect(element,idProduct, ipa, id_attribute, id_group, shoeSize)
{
	if(!$(element).parent().hasClass('picked')){
		var userConfirmation = confirm(confirmationMessage+': '+shoeSize)
		if (userConfirmation==true)  {
			// Visual effect
			$('#product-recommend-choices-group-'+id_group+' ul.product-recommend-choices li').removeClass('picked');
			$('#product-recommend-choice-'+id_attribute).parent().addClass('picked');
			$('#product-recommend-choice-'+id_attribute).fadeTo('fast', 1, function(){$(this).fadeTo('normal', 0, function(){$(this).fadeTo('normal', 1, function(){});});});

			if(!$(element).parent().hasClass('picked')){
				$(element).parent().addClass('picked');
			}

			$.ajax({
					type: 'GET',
					url: baseDir + 'modules/stockremainder/stock-remainder-show-size-list.php?action=save&productId='+idProduct+'&productAttributeId='+ipa+'&shoeSize='+shoeSize,
					async: true,
					cache: false,
					dataType : "html",
					success: function(data)
					{
						$('.shoe-size-recorded').removeClass('hidden');

					}
				});
		}else{
			return false;
		}
			
	}else{
		alert(alreadySelectedMessage+' .');
		return false;
	}
	
}

$(document).ready(function(){

	$(document).on('click', '.instal', function(){
		if($('input[name="instal"]:checked').val()){
			var instal = $('input[name="instal"]:checked').val();
			var each_instal_amt = $(this).attr('instal_amt');
			var total_amount = $(this).attr('total');

			var cms_arr = new Array(20, 21, 22);
			var url = '';
			for(var i=0; i < cms_arr.length;  i++)
			{
				url = baseDir+'agreements.php?id_cms='+cms_arr[i]+'&content_only=1&instalments='+instal;
				if(each_instal_amt)
				{
					url += '&each_instal_amt='+each_instal_amt;
				}
				url += '&total_amount='+total_amount;
				var res = ajaxRequest(url);
				var data = '<iframe src="'+url+'" name="agreement_'+(i+1)+'">'+res+'</iframe>';
				$('#agreement_'+(i+1)).empty();
				$('#agreement_'+(i+1)).html(data);
			}

			if($('input[name=sales_agreemnt]').is(':checked')){
				$('input[name=sales_agreemnt]').attr('checked', false);
			}

			$('#step_4').show();
			$('#step_5').hide();
			$('#step_6').hide();

			$('#submit_total').empty();
			$('#submit_total').append(total_amount+" TL");
			$('#finalTotal').attr('value',total_amount);
			$('#instlmnt').attr('value',instal);

		}
	});

	$("#agree_pre_sales, #agree_sales").click(function(){
		if($("#agree_pre_sales").attr('checked') == false || $("#agree_sales").attr('checked') == false){
			$('#step_5').hide();
			$('#step_6').hide();
		}else if($("#agree_pre_sales").attr('checked') == true && $("#agree_sales").attr('checked') == true){
			$('#step_5').show();
			$('#step_6').show();
		}
	});

});

function ajaxRequest(url){
	$.ajax({
			type: 'GET',
			url:url,
			async: true,
			cache: false,
			dataType: 'html',
			success: function(res)
			{
				return res;
			}
	});
}

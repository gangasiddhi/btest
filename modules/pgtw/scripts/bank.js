$(document).ready(function() {
	$('#show-instal-popup').attr('href','#show-popup-instal');

	$('#ccnum , #ccvv2, #cc-exp-year, #cc-exp-motnh').change(function(){
		var credit_card_no = $('#ccnum').val();
		var cc_exp_year = $('#cc-exp-year').val();
		var cc_exp_month = $('#cc-exp-motnh').val();
		var ccvv2 = $('#ccvv2').val();

		if(credit_card_no == '' || cc_exp_year == '' || cc_exp_month == '' || ccvv2 == ''){
			$('#step_3, #display-total').addClass('hidden');
			return false;
		}
		else{
			var url = baseDir+'modules/mediator/get-bank-code.php?ccno='+credit_card_no;
			$.ajax({
				type: 'GET',
				url:  url,
				dataType: 'json',
				success: function(res)
				{
					var bank_code = res['bankCode'];

					if(bank_code == 0 )
					{
						//alert('Please use the correct ID');
						return false;
					}

					/*if the bank id 111(Finansbank), 62(Garanti), 67(Yapikredi) then show the installments
					 *otherwise show without installments */
					if(bank_code == 111 || bank_code == 62  || bank_code == 67)
					{
						var path = baseDir+'modules/mediator/validation.php?bankCode='+bank_code;
						$('form').attr('action',path);
						$('.with-install').removeClass('hidden');
					}
					else{
						var path = baseDir+'modules/mediator/validation.php?bankCode='+111;
						$('form').attr('action',path);
						$('.without-install').removeClass('hidden');
					}
					$("a#show-instal-popup").attr('href',"#show-popup-instal");
					$('#step_3, #display-total').removeClass('hidden');
				}
			});
		}

	});

	$(document).on('click', '.instal', function() {
		if($('input[name="instal"]:checked').val()){
			/*Installments details*/
			var installments_count = $(this).attr('value');
			var each_instal_amt = $(this).attr('instal_amt');
			var total_amount = $(this).attr('total');

			/*Changing the agreements on installment selection*/
			$('a#agreement1').attr('href',baseDir+'agreements.php?id_cms=20&content_only=1&instalments='+installments_count+'&each_instal_amt='+each_instal_amt+'&total_amount='+total_amount);
			$('a#agreement2').attr('href',baseDir+'agreements.php?id_cms=21&content_only=1&instalments='+installments_count+'&each_instal_amt='+each_instal_amt+'&total_amount='+total_amount);
			$('a#agreement3').attr('href',baseDir+'agreements.php?id_cms=22&content_only=1&instalments='+installments_count+'&each_instal_amt='+each_instal_amt+'&total_amount='+total_amount);

			/*Changing the total amount on changing the installments*/
			$('#submit_total').empty();
			$('#submit_total').append(total_amount+" TL");
			$('#finalTotal').attr('value',total_amount);
			$('#instlmnt').attr('value',installments_count);
		}
	});

	/*Fancy box for Sales-Agreements & Installments popup*/
	$("a.agree").fancybox({
		'autoSize' : false,
		'width' : 800,
		'height' : 400,
		'margin' : 2,
		'padding': 5,
		'titleShow': false,
		'centerOnScroll': true,
		'overlayShow': true,
		'overlayColor': '#000',
		'type': 'iframe'
	});

	$("a#show-instal-popup").fancybox({
		'autoSize': false,
		'width': 800,
		'height': 400,
		'margin': 2,
		'padding': 5,
		'titleShow': false,
		'centerOnScroll': true,
		'overlayShow': true,
		'overlayColor': '#000',
		'type': 'inline'
	});
});

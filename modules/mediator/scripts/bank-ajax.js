function verifyGoDaddySSLSeal() {
    var bgHeight = "433",
        bgWidth = "592",
        sealId = "BN9fyWB5szjy9s8lJy6LglhYQopLNUA5NpFSRiALTNUVxh64epf93D7t",
        url = "https://seal.godaddy.com/verifySeal?sealID=" + sealId;

        window.open(url, 
                    "SealVerfication",
                    "location=yes,status=yes,resizable=yes,scrollbars=no,width=" 
                    + bgWidth + ",height=" + bgHeight);
}

function displayPaymentOptions(id) {
    var url ='';
	
    if (id === 'default_payment_option') {
        url =  baseDir + 'modules/mediator/display-payment.php';
    } else if (id === 'cod') {
        url =  baseDir + 'modules/cashondelivery/validation.php';
    } else if (id === 'turkcell_wallet') {
        url =  baseDir + 'modules/pgtw/display-payment.php';
    }

    $.ajax({
        type: 'GET',
        url:url,
        async: true,
        cache: false,
        dataType: 'html',
        success: function(res) {
            $('#payment_page').html(res);
            $("#sslseal").click(function(){
                verifyGoDaddySSLSeal();
                return false;
            });
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
}

$(window).ready(function() {
    setTimeout(function() {
        $('#default_payment_option').trigger('click');
    }, 1);

    /* TODO: if it's already clicked prevent reclick */

    $('.payment-options').click(function() {
        var id = $(this).attr('id');

        if (id === "default_payment_option") {
            $('#default_payment_option').removeClass('not_selected_1');
            $('#default_payment_option').addClass('selected_1');
            $('.cod_extra_shipping').addClass('hidden');
            
            /*To hide the installments options in the cart*/
            
            if(!$('tr.installments-option').hasClass('hidden'))
                $('.installments-option').addClass('hidden');
                        
            $('#spc-total-price').empty();
            $('#spc-total-price').append(totalPrice+" "+sign);
                        
            $('#submit_total').empty();
            $('#submit_total').append(totalPrice+" "+sign);
            $('#finalTotal').attr('value',totalPrice);
            
            if ($('#cod').hasClass('not_selected_2') === false) {
                $('#cod').addClass('not_selected_2');
                $('#cod').removeClass('selected_2');
            }

            if ($('#turkcell_wallet').hasClass('not_selected_3') === false) {
                $('#turkcell_wallet').addClass('not_selected_3');
                $('#turkcell_wallet').removeClass('selected_3');
            }

            $('.payment-list').show();
            
        } else if (id === "cod") {
            $('#cod').removeClass('not_selected_2');
            $('#cod').addClass('selected_2');
                        
            /*To hide the installments options in the cart*/
            if(!$('tr.installments-option').hasClass('hidden')){
                $('.installments-option').addClass('hidden');
            }
                        
            if ($('#default_payment_option').hasClass('selected_1') === true) {
                $('#default_payment_option').removeClass('selected_1');
                $('#default_payment_option').addClass('not_selected_1');
            }

            if ($('#turkcell_wallet').hasClass('selected_3') === true) {
                $('#turkcell_wallet').removeClass('selected_3');
                $('#turkcell_wallet').addClass('not_selected_3');
            }
        } else if (id === 'turkcell_wallet') {
            $('#turkcell_wallet').removeClass('not_selected_3');
            $('#turkcell_wallet').addClass('selected_3');
            $('.cod_extra_shipping').addClass('hidden');
                        
            /*To hide the installments options in the cart*/
            if(!$('tr.installments-option').hasClass('hidden')){
                $('.installments-option').addClass('hidden');
            }
                
            $('#spc-total-price').empty();
            $('#spc-total-price').append(totalPrice+" "+sign);
                        
            $('#submit_total').empty();
            $('#submit_total').append(totalPrice+" "+sign);
            $('#finalTotal').attr('value',totalPrice);
                        
            if ($('#default_payment_option').hasClass('selected_1') === true) {
                $('#default_payment_option').removeClass('selected_1');
                $('#default_payment_option').addClass('not_selected_1');
            }

            if ($('#cod').hasClass('selected_2') === true) {
                $('#cod').removeClass('selected_2');
                $('#cod').addClass('not_selected_2');
            }
        }

        displayPaymentOptions(id);

        return false;
    });
});

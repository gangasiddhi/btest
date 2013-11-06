$(document).ready(function() {

    $('.spc-address').addClass('selected');

    $('#address_step').click(function() {
        $('.spc-address').addClass('selected');
        $('.spc-payment').removeClass('selected');

        if ($('#address_step_first a span').hasClass('arrow-image-checkout-first-unselected')) {
            $('#address_step_first a  span').removeClass('arrow-image-checkout-first-unselected');
            $('#address_step_first a span').addClass('arrow-image-checkout-first');
        }

        $('#address_payment').show();
        $('#payment_methods').hide();
        $('#payment-checkout').hide();
        $('#address-checkout').show();

        // don't trigger click action
        return false;
    });

    $('#payment_step').click(function() {
        $('.spc-address').removeClass('selected');
        $('.spc-payment').addClass('selected');

        if ($('#address_step_first a span').hasClass('arrow-image-checkout-first')) {
            $('#address_step_first a span').removeClass('arrow-image-checkout-first');
            $('#address_step_first a span').addClass('arrow-image-checkout-first-unselected');
        }

        $('#payment_methods').show();
        $('#address_payment').hide();
        $('#payment-checkout').show();
        $('#address-checkout').hide();
        $('#payment-process').hide();

        // don't trigger click action
        return false;
    });

    $('#submitaddress').click(function() {
        $('.spc-address').removeClass('selected');
        $('.spc-payment').addClass('selected');

        if ($('#address_step_first a span').hasClass('arrow-image-checkout-first')) {
                $('#address_step_first a span').removeClass('arrow-image-checkout-first');
                $('#address_step_first a span').addClass('arrow-image-checkout-first-unselected');
            }
           
         
        if($('.spc-address').hasClass('selected')){
            $('#address_payment').show();            
        } 
        
        if($('.spc-payment').hasClass('selected')){
            $('#address_payment').hide();
            $('#payment-checkout').show();
            $('#payment-process').hide();
            $('#address-checkout').hide();
        }
        
        $('#payment_methods').show();
    });

    $('#submit-address-payment').click(function() {
        
        if( $('#submitaddress:visible').length > 0 ) {
            $('#submitaddress').trigger('click');
        } else if( $('#pay_button:visible').length > 0 ) {
            $('#pay_button').trigger('click');
        } else if( $('#cod-checkout-button:visible').length > 0 ) {
            if($("#agree_sales").attr('checked') == true) {
                if($('input[name="orderProcessing"]').hasClass('hidden')){
                    $('input[name="orderProcessing"]').removeClass('hidden');
                    $('input[name="paymentSubmit"]').addClass('hidden');
                }
                
            }
            $('#cod-checkout-button').trigger('click');
            
        }
        
       
        // don't trigger click action
        return false;
    });
});

/*@parameters
 *peration = d - Delivery
  operation =  e - Edit
  operation =  i - Invoice
  address_id id of the address to be edited.
 **/

function address_pop(operation, address_id)
{
    var params;

    if(operation == 'd')
        params = 'chkout=2&select_address=1';
    else if(operation == 'i')
        params = 'chkout=2&select_address=2';
    else if(operation == 'e')
        params = 'id_address='+address_id+'&chkout=2';
    $.fancybox.showLoading();
    $.ajax({

            type: 'GET',
            url: baseDir + 'address.php',
            async: true,
            cache: false,
            dataType: 'html',
            data:params,
            success: function(res)
            {
                $.fancybox(res, {
                    'autoSize' : false,
                    'width' : 500,
                    'height' : 550,
                    'margin' : 2,
                    'padding': 5,
                    'titleShow' : false,
                    'centerOnScroll' : true,
                    'overlayShow'	: true,
                    'overlayColor' : '#000'});
            },
            error: function (xhr, ajaxOptions, thrownError)
            {
                alert(xhr.status);
                alert(thrownError);
            }
    });
}

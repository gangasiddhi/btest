$(document).ready(function() {

    $('#address_step').click(function() {
        $('.spc-address').addClass('selected');
        $('.spc-payment').removeClass('selected');

        $('#address_payment').show();
        $('#payment_methods').hide();

        // don't trigger click action
        return false;
    });

    $('#payment_step').click(function() {
        $('.spc-address').removeClass('selected');
        $('.spc-payment').addClass('selected');

        $('#payment_methods').show();
        $('#address_payment').hide();

        // don't trigger click action
        return false;
    });

    $('#submitaddress').click(function() {
        $('.spc-address').removeClass('selected');
        $('.spc-payment').addClass('selected');

        $('#payment_methods').show();
        $('#address_payment').hide();
    });
});

/**
 * @parameters
 * operation: d - delivery
 * operation: e - edit
 * operation: i - invoice
 * address_id: id of the address to be edited.
 */
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

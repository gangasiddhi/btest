
$(document).ready(function()
{
	if (typeof(formatedAddressFieldsValuesList) != 'undefined') {
        updateAddressesDisplay(true);
    }

    /*Click on checkbox  Use same address as delivery address*/
    $('#addressesAreEquals').click(function(){
        $(".invoice-address-con").find(".payment_address").removeClass("current");
        updateAddressesDisplay();
    });
    
    /*The user clicks on radio button to choose address.Update the delivery or invoice address for cart*/
    $('.payment_address').click(function(){
        $(this).siblings(".payment_address").removeClass("current");
        $(this).addClass("current");

        $(this).siblings(".payment_address").find('.current_address').remove();
        $(this).find(".address_radio").prop("checked",true);
        $(this).append(selection);

        updateAddresses();
    });

    $(".edit-address").click(function(e){
        address_pop('e', $(this).attr("data-address-id"));
        e.stopPropagation();
    });

    $(".delete-address").click(function(e){
        e.stopPropagation();
        if(!confirm(deleteConfirm))
            e.preventDefault();
    });

    //Add gray background selected addresses
    $(".address_radio:checked").closest(".payment_address").addClass("current");
    
    /*When no address opens the pop inline address form */
    if($('.payment_addresses').is(':visible')==true && $('.address-type-con').is(':visible')==false){        
         address_inline("d");
    }
});


function address_inline(operation, address_id)
{
    var params;
    
    if(operation == 'd'){
        params = 'chkout=2&select_address=1&no_add=1';
    }
    
    if($('.new-address').is(':visible')==true){
        $('.new-address').remove();
    }    
    
    url=baseDir + 'address.php?'+params;
    
    $('.payment_addresses').addClass("address-align");
    $('.payment_addresses').html('<div align="center" style="padding-top:50px;cursor:wait;"><h4>Yükleniyor...</h4></div>').load(url, '', function(){
        $('#addressesAreEquals').click(function(){
            $(".invoice-address-con").find(".payment_address").removeClass("current");
            updateAddressesDisplay();
        });
    });    
    
}

/*update the display of the addresses*/
function updateAddressesDisplay(first_view)
{
	/*update content of delivery address*/
        if(first_view)
        {
            updateAddressDisplay('delivery');
            updateAddressDisplay('invoice');
        }

	var txtInvoiceTitle = "";

	try{
		var adrs_titles = getAddressesTitles();
		txtInvoiceTitle = adrs_titles.invoice;
	}
	catch (e)
	{

	}

	/*update content of invoice address
	if addresses have to be equals...*/
	if ($('#addressesAreEquals').is(':checked')) {
            $('.invoice-address-con:visible').hide('fast');
             if(!first_view) {
                 //$('input:radio[name=invoice_address]:checked').parent().removeClass('pink_circle_bg');
                 $('.addresses_invoice').find('.current_address').remove();
                 $('input:radio[name=invoice_address]:checked').removeAttr('checked');
                 updateAddresses();

             }
	}
	else
        {
               $('.invoice-address-con:hidden').show('fast');
	}

	return true;
}

/*Display of address*/
function updateAddressDisplay(addressType)
{
	if (formatedAddressFieldsValuesList.length <= 0)
		return false;

        var delivery_checked, edit, outer_div, new_ul, checked, checkradio, new_addrs, radio_pink_bg, deleteLink, addresss_buttons;

        for (var i=0; i<all_address_ids.length; i++)
        {
            outer_div = '<div id="'+addressType+'_address_'+all_address_ids[i]+'" class="payment_address'+(((i+1)%2) == 0 ? " ad_even" : "")+'"></div>';
            edit = '<p class="edit-address"  data-address-id="'+all_address_ids[i]+'" >'+address_edit+'</p>';
            deleteLink =  '<p class="add_bar"></p><a class="delete-address" href ="'+address_delete_link+'?id_address='+all_address_ids[i]+'&chkout=2&delete" title = "'+address_delete_title+'">'+address_delete_title +'</a>';
            addresss_buttons = '<div id="addresss_buttons">'+edit+deleteLink+'</div>';
            $('.addresses_'+ addressType).append(outer_div);
            new_ul = document.createElement('ul');

            new_ul.id= addressType+'_'+all_address_ids[i];
            if((all_address_ids[i] == id_cart_delivery_address && addressType == 'delivery') || ($('input[type=checkbox]#addressesAreEquals:checked').length == 0 && all_address_ids[i] == id_cart_invoice_address  && addressType == 'invoice'))
            {
                checked = 'checked="checked"';
                //radio_pink_bg = ' pink_circle_bg';
            }
            else
            {
                checked = '';
                //radio_pink_bg = '';
            }
            //checkradio = '<div class="radio'+radio_pink_bg+'"><input class="address_radio" id="'+addressType+'Address_'+all_address_ids[i]+'" type="radio" name="'+addressType+'_address" value="'+all_address_ids[i]+'" '+checked+' /></div>';
            checkradio = '<div class="radio"><input class="address_radio" id="'+addressType+'Address_'+all_address_ids[i]+'" type="radio" name="'+addressType+'_address" value="'+all_address_ids[i]+'" '+checked+' /></div>';
            $("#"+addressType+"_address_"+all_address_ids[i]).append(checkradio);
            $("#"+addressType+"_address_"+all_address_ids[i]).append(new_ul);
            $("#"+addressType+"_address_"+all_address_ids[i]).append(addresss_buttons);
            //$("#"+addressType+"_address_"+all_address_ids[i]).append(deleteLink);
            if((all_address_ids[i] == id_cart_delivery_address && addressType == 'delivery') || ($('input[type=checkbox]#addressesAreEquals:checked').length == 0 && all_address_ids[i] == id_cart_invoice_address  && addressType == 'invoice'))
                $("#"+addressType+"_address_"+all_address_ids[i]).append(selection);
            buildAddressBlock(all_address_ids[i], addressType, $('#'+addressType+'_'+all_address_ids[i]));
        }

        new_addrs =  ' <div class="add_new_address new-address'+((all_address_ids.length%2) != 0 ? " ad_even" : "")+'"><p><span class="new_addrs" onclick="address_pop(\'d\');">'+addAdresss+'</span></p></div>';
        $('.addresses_'+ addressType).append(new_addrs);

        /*change update link
	var link = $('ul#address_' + addressType + ' li.address_update a').attr('href');
	var expression = /id_address=\d+/;
	link = link.replace(expression, 'id_address='+idAddress);
	$('ul#address_' + addressType + ' li.address_update a').attr('href', link);*/
}

/*Update the delivery and invoice address in cart table via ajax*/
function updateAddresses()
{
        /*url: baseDir + 'order.php',*/
        var idAddress_delivery = parseInt($('input:radio[name=delivery_address]:checked').val());
        var idAddress_invoice = $('input[type=checkbox]#addressesAreEquals:checked').length == 1 ? idAddress_delivery :  parseInt($('input:radio[name=invoice_address]:checked').val());
        $.ajax({
           type: 'POST',
           url: order_page_link,
           async: true,
           cache: false,
           dataType : "json",
           data: 'processAddress=true&step=2&ajax=true&id_address_delivery=' + idAddress_delivery + '&id_address_invoice=' + idAddress_invoice+ '&token=' + static_token ,
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
				}
		},
           error: function(XMLHttpRequest, textStatus, errorThrown) {alert("TECHNICAL ERROR: unable to save adresses \n\nDetails:\nError thrown: " + XMLHttpRequest + "\n" + 'Text status: ' + textStatus);}
       });
   /*resizeAddressesBox();*/
}

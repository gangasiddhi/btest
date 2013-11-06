
function closeAddressPopUp() {
    $.fancybox.close(true);
}

var isValid = {
    'string': function(str) {
        return /^[a-zA-ZşŞıİçÇöÖüÜĞğ ]+$/.test(str);
    },
    alphaNumeric: function (str) {
         return /^[0-9a-zA-ZşŞıİçÇöÖüÜĞğ ]+$/.test(str);
    },
    address : function(str) {
        return !/[!<>?=+@{}_$%^]/.test(str);
    },
    phoneNumber: function(str, isContainsRegion) {
        if (isContainsRegion) {
            return /^\d{10,11}$/.test(str); // there is 0 or not
        }

        return /^\d{7}$/.test(str);
    },
    regionCode: function(str) {
        return /^\d{3,4}$/.test(str);
    }
};


function isAddressFormValid() {
    var alias, firstname, lastname, address1;

    alias = $('#alias').val();
    if(!alias || alias.length > 32 || !isValid.alphaNumeric(alias)) {
        return false;
    }

    firstname = $('#firstname').val();
    if(!firstname || firstname.length > 32 || !isValid.string(firstname)) {
        return false;
    }

    lastname = $('#lastname').val();
    if( !lastname || lastname.length > 32 || !isValid.string(lastname)) {
        return false;
    }

    address1 = $('#address1').val();
    if(!address1 || address1.length > 128 || !isValid.address(address1)){
        return false;
    }

    if(!$("#id_state").val()) {
        return false;
    }

    if(!$("#id_province").val()) {
        return false;
    }

    phone = $('#phone').val();
    if(!phone || phone.length > 10 || !isValid.phoneNumber(phone)) {
        return false;
    }

    return true;
}

function addressValidation( submitForm ) {
    var alias, firstname, lastname, address1, regionCode, phone;

    $('.clear').remove();
    $('.add_error').remove();
    //var addressExp = /^[A-Za-z-9, +-_&#'"]+$/;
    var  errString = '';
    var errHtml = '';
    var errLen = 0;
    var alphaExp = /^[a-zA-ZşŞıİçÇöÖüÜĞğ ]+$/;
    //var numericExpression = /^[0-9]+$/;
    var numericExpression = /^[+0-9. ()-]*$/;
    var aphanumeric = /^[0-9a-zA-ZşŞıİçÇöÖüÜĞğ ]+$/;
    //var addressExp = '/^[^!<>?=+@{}_$%]*$/u';
   //var addressExp = /^[A-Za-z-9, -&#'"]+$/;
   //var addressExp = /^[A-Za-z-9, +-_&#'"]+$/;
    alias = $('#alias').val();
    if(!alias || alias.length > 32 || !isValid.alphaNumeric(alias)) {
        errString = alias ? aliasIncorrect : aliasEmpty;
        errLen += errString.length;
        errHtml = '<span class="add_error">'+errString+'</span>';
        $('p.alias').append(errHtml);
    }


    firstname = $('#firstname').val();
    if(!firstname || firstname.length > 32 || !isValid.string(firstname)) {
        errString = firstname ? firstnameIncorrect : firstnameEmpty;
        errLen += errString.length;
        errHtml = '<span class="add_error">'+errString+'</span>';
        $('p.firstname').append(errHtml);
    }

    lastname = $('#lastname').val();
    if( !lastname || lastname.length > 32 || !isValid.string(lastname)) {
        errString = lastname ? lastnameEmpty: lastnameIncorrect ;
        errLen += errString.length;
        errHtml = '<span class="add_error">'+errString+'</span>';
        $('p.lastname').append(errHtml);
    }

    address1 = $('#address1').val();
    if(!address1 || address1.length > 128 || !isValid.address(address1)){
        errString = address1Error;
        errLen += errString.length;
        errHtml = '<span class="add_error">'+errString+'</span>';
        $('p.address1').append(errHtml);
    }

    if(!$("#id_state").val())
    {
        errString = stateEmpty;
        errLen += errString.length;
        errHtml = '<span class="add_error">'+errString+'</span>';
        $('p.state').append(errHtml);
    }

    if(!$("#id_province").val())
    {
        errString = provinceEmpty;
        errLen += errString.length;
        errHtml = '<span class="add_error">'+errString+'</span>';
        $('p.province').append(errHtml);
    }

    phone = $('#phone').val();
    regionCode = $('#regioncode').val();

    if(!phone || !isValid.phoneNumber(phone, false)) {
        if (phone) {
            errString = phoneIncorrect;
        } else {
            errString = (regionCode) ? phoneIncorrect : phoneEmpty;
        }

        errLen += errString.length;
        errHtml = '<span class="add_error">'+errString+'</span>';
        $('p.phone').append(errHtml);
        return false;
    }

    if(!regionCode || !isValid.regionCode(regionCode)) {
        errString = phoneIncorrect;
        errLen += errString.length;
        errHtml = '<span class="add_error">'+errString+'</span>';
        $('p.phone').append(errHtml);
        return false;
    }

    if(errLen == 0) {
        if (submitForm) {
            $("#submitAddress").trigger('click');
        }
        return true;
    }
    return false;
}

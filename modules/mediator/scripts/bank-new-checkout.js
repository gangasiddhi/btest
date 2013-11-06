var trigger_count = 0;
var req2cellPhone = 0;
var req2cardChoice = 0;
var req2installment = 0;
var req2finalTotal = 0;
var req2bonus = 0;
var creditCardTypes = {
    AMEX: 3,
    VISA:4,
    MASTER_CARD:5
};

function movetoNext(current, nextFieldID) {
    if (current.value.length >= current.maxLength) {
        document.getElementById(nextFieldID).focus();
    }
}

function popupInstallment() {
    var lc = 0;
    var pop_install = '';

    $.each(installmentsOptions, function(k, v) {
        var pop_div = '';
        var bank = baseDir + 'modules/mediator/img/' + k + '_head.jpg';
        var bank_bottom = baseDir + 'modules/mediator/img/' + k + '_bottom.jpg';
        var bottom_img='';
        
        $.each(v, function(i, r) {
            var installmentOptionName = i + installment;

            // 3+5 Installment & 3 Months Deferment
            
            bottom_img='<div style="margin-top: -4px;"><img src="' +bank_bottom + '" alt=""/></div>';
             
            pop_div += '<tr class="helight_' + lc + '"><td class="first-td">' + (i != 1 ? installmentOptionName : one_payment) +
                '</td><td class="next-td">' + installmentsAmount[k]['eachInstallmentAmountFor' + i + 'Installments'] +
                ' ' + sign + '</td><td class="next-td">' + installmentsAmount[k]['totalAmountFor' + i + 'Installments'] +
                ' ' + sign + '</td></tr>';
        });

        pop_install += '<div class="pop-bank-instal">';
        pop_install += '<table id="bank-pop' + lc + '" ' + (k === 'pgy' ? 'style="width: 200px;"' : '') +
            '><div calss="pop-bank-div"><thead><tr id="bank-pop-tr' + lc + '" style="width: auto;"><td colspan="1"><img src="' +
            bank + '" alt=""/></td></tr></thead>';
        pop_install += pop_div;
        pop_install += '</div></table>'+bottom_img;
        pop_install += '</div>';

        lc++;
    });

    return pop_install;
}

function installmentCalculation(bankModule) {
    var paymentModules = Object.keys(installmentsOptions);
    // ratios are same for no installments hence the game..
    var russianRoulette = paymentModules[Math.floor(Math.random() * paymentModules.length)];
    var def = bankModule || russianRoulette;
    var install = '';
    var installmentOption = 1;

    install = '<tr><td style="vertical-align: top"><input type="radio" instal_amt="' +
        installmentsAmount[def]['eachInstallmentAmountFor1Installments'] + '" total="' +
        installmentsAmount[def]['totalAmountFor1Installments'] + '" value ="' + installmentOption +
        '" name="instal" class="instal"/></td><td>' + one_payment + '</td><td>' + installmentsAmount[def]['eachInstallmentAmountFor1Installments'] +
        ' ' + sign + '</td><td>' +  installmentsAmount[def]['totalAmountFor1Installments'] + ' ' + sign + '</td></tr>';

    if ($.inArray(bankModule, Object.keys(installmentsOptions)) !== -1) {
        install = "";

        $.each(installmentsOptions[bankModule], function(i, r) {
            var installmentOptionName = i;

            /**
             * For orders with total amount >= 50 and payed by YKB cards
             * a special campaign applies:
             *
             * 3+5 Installment & 3 Months Deferment
             */

            install += '<tr><td style="vertical-align: top"><input type="radio" instal_amt="' +
                installmentsAmount[bankModule]['eachInstallmentAmountFor' + installmentOption + 'Installments'] +
                '" total="' + installmentsAmount[bankModule]['totalAmountFor' + i + 'Installments'] + '" value ="' +
                i + '" interest_amt="'+ installmentsAmount[bankModule]['interestAmountFor' + i + 'Installments'] +
                '" name="instal" class="instal"/></td><td style="vertical-align: top;">' +
                (i != 1 ? installmentOptionName : one_payment) +
                '</td><td style="vertical-align: top">' + installmentsAmount[bankModule]['eachInstallmentAmountFor' + i +
                'Installments'] + ' ' + sign + '</td><td style="vertical-align: top">' +  installmentsAmount[bankModule]['totalAmountFor' +
                i + 'Installments'] + ' ' + sign + '</td></tr>';
        });
    }

    return install;
}

function trigger_request() {
    var img_src = null;

    $.ajax({
        type: 'GET',
        url: baseDir + 'modules/pgtw/validation.php',
        async: true,
        cache: false,
        dataType: 'json',
        data: {
            req: 2,
            cell_phone: req2cellPhone,
            card_choice: req2cardChoice,
            finalTotal: req2finalTotal,
            installment_choice: req2installment,
            bonus: req2bonus
        },
        success: function(res2) {
            /* popup based on the result*/
            var status = res2.trans2Status;

            /**
             * const SUCCESS = '00';
             * const FAILURE = '01';
             * const RETRY = '04';
             */
            if (status == '04') {
                if (trigger_count <= 36) {
                    trigger_count++;

                    setTimeout("trigger_request()", 5000);
                }
            } else if (status == '01') {
                img_src = baseDir + '/modules/pgtw/img/Turkcell-Cuzdan-Error.png';

                $.fancybox('<img src="' + img_src + '" alt=""/>', {
                    autoSize: false,
                    height: 350,
                    width: 500,
                    padding : 0,
                    margin: 0,
                    scrolling: 'no',
                    titleShow: false,
                    centerOnScroll: true,
                    overlayColor: '#000',
                    showNavArrows: false,
                    hideOnContentClick: true,
                    hideOnOverlayClick: false,
                    showCloseButton: false,
                    enableEscapeButton: false,
                    onClosed: function() {
                        $('#fancybox-wrap').removeClass('tcw');
                    }
                });

                $('#fancybox-wrap').addClass('tcw');
            } else if (status == '00') {
                img_src = baseDir + '/modules/pgtw/img/Turkcell-Cuzdan-Success.png';

                $.fancybox('<img src="' + img_src + '" alt=""/>', {
                    autoSize: false,
                    height: 350,
                    width: 500,
                    padding: 0,
                    margin: 0,
                    scrolling: 'no',
                    titleShow: false,
                    centerOnScroll: true,
                    hideOnContentClick: true,
                    hideOnOverlayClick: false,
                    showCloseButton: false,
                    enableEscapeButton: false,
                    overlayColor: '#000',
                    showNavArrows: false,
                    onClosed: function () {
                        window.location = baseDir + 'order-confirmation.php?key=' + res2.customerSecurekey +
                            '&id_cart=' + res2.cartId + '&id_module=' + res2.moduleId + '&id_order=' + res2.orderId;
                    }
                });

                $('#fancybox-wrap').addClass('tcw');
            } else if (trigger_count > 36) {
                /* After 3 min displaying the error popup, that means try again.*/
                img_src = baseDir + '/modules/pgtw/img/Turkcell-Cuzdan-Error.png';

                $.fancybox('<img src="' + img_src + '" alt=""/>', {
                    autoSize: false,
                    height: 350,
                    width: 500,
                    padding: 0,
                    margin: 0,
                    scrolling: 'no',
                    titleShow: false,
                    centerOnScroll: true,
                    hideOnContentClick: true,
                    hideOnOverlayClick: false,
                    showCloseButton: false,
                    enableEscapeButton: false,
                    overlayColor: '#000',
                    showNavArrows: false,
                    onClosed: function () {
                        $('#fancybox-wrap').removeClass('tcw');
                    }
                });

                $('#fancybox-wrap').addClass('tcw');
            }
        }
    });
}

$(document).ready(function() {
    var callRequest = '';
    var inst_pop_up =  popupInstallment();
    inst_pop_up +="<br><div class='popup_text'> Diğer tüm banka ve kredi kartlarıyla da Butigo'dantek çekim olarak alışveriş yapabilirsiniz.</div>";

    $('#ccnum1, #ccnum2, #ccnum3, #ccnum4').change(function() {
        var credit_card_no = $('#ccnum1').val() + $('#ccnum2').val() + $('#ccnum3').val() + $('#ccnum4').val();
        $('#ccnum').val(credit_card_no);
    });

    $('#show-instal-popup').fancybox({
        autoSize: false,
        width: 718,
        height: 520,
        margin: 2,
        padding: 0,
        titleShow: false,
        centerOnScroll: true,
        overlayShow: true,
        overlayColor: '#000',
        content: inst_pop_up
    });

    $('#ccnum, #ccvv2, #cc-exp-year, #cc-exp-motnh').change(function() {
        var credit_card_no = $('#ccnum').val();
        var cc_exp_year = $('#cc-exp-year').val();
        var cc_exp_month = $('#cc-exp-motnh').val();
        var ccvv2 = $('#ccvv2').val();
        var rgx = /\d{15,16}/g;

        if (credit_card_no.length >= 15) {
            var url = baseDir + 'modules/mediator/get-bank-code.php?ccno=' + credit_card_no;

            if (! rgx.test(credit_card_no)) {
                alert(bank_credit_error);

                return;
            }

            $.ajax({
                type: 'GET',
                url: url,
                dataType: 'json',
                success: function(res) {
                    var bankModule = res['module'];

                    /* Installments Calculation*/
                    var install = installmentCalculation(bankModule);
                    var path = baseDir + 'modules/mediator/validation.php?bankModule=' + bankModule;

                    if ($.inArray(bankModule, Object.keys(installmentsOptions)) !== -1) {
                        $('form').attr('action', path);

                        /* Adding installments options */
                        $('.with-install').html(install);
                    } else {
                        /* Adding installments options */
                        $('.with-install').html(install);

                        path = baseDir + 'modules/mediator/validation.php?bankModule=pgf';

                        $('form').attr('action', path);
                    }
                }
            });
        }

        if (! $('.with-install').hasClass('hidden')) {
            $('.with-install').addClass('hidden');
        }

        if (! $('#installment_details > thead').hasClass('hidden')) {
            $('#installment_details > thead').addClass('hidden');
        }

        if (credit_card_no === '' || cc_exp_year === '' || cc_exp_month === '' || ccvv2 === '') {
            return false;
        } else {
            /* Change the total amount on changing the credit card*/
            $('#finalTotal').attr('value', totalPrice);

            $('#spc-total-price').empty();
            $('#spc-total-price').append(totalPrice + " " + sign);

            /* Showing the installment details*/
            $('#installment_details > thead').removeClass('hidden');
            $('.with-install').removeClass('hidden');
            $('#step_3').removeClass('hidden');
        }
    });

    $("#ccnum1").keyup(function(e){
        var card_number=$(this).val(),
            $icon_items=$('.credit-card-icon-con> .icon-item'),
            first_digit=card_number.split('')[0],
            icon_class;//keep which item will not be passive

        if (!first_digit || first_digit.length==0){
            $icon_items.addClass('passive');
            return;
        }

        if (first_digit.length>1)
            return;

        if (first_digit==creditCardTypes.VISA){
            icon_class='visa';

        } else if (first_digit==creditCardTypes.AMEX){
            icon_class='amex';

        } else if(first_digit==creditCardTypes.MASTER_CARD){
            icon_class='master-card';
        }

        $icon_items.removeClass('passive')
            .not('.'+icon_class).addClass("passive");
    });

    $(document).on('click', '.instal', function() {
        if ($('input[name="instal"]:checked').val()) {
           /*Installments details*/
            var installments_count = $(this).attr('value');
            var each_instal_amt = $(this).attr('instal_amt');
            var total_amount = parseFloat($(this).attr('total'));
            var interest_amount = parseFloat($(this).attr('interest_amt'));

            /* Changing the agreements on installment selection */
            $('a#agreement1').attr('href', baseDir + 'agreements.php?id_cms=20&content_only=1&instalments=' +
                installments_count + '&each_instal_amt=' + each_instal_amt + '&total_amount=' + total_amount);
            $('a#agreement2').attr('href', baseDir + 'agreements.php?id_cms=21&content_only=1&instalments=' +
                installments_count + '&each_instal_amt=' + each_instal_amt + '&total_amount=' + total_amount);
            $('a#agreement3').attr('href', baseDir + 'agreements.php?id_cms=22&content_only=1&instalments=' +
                installments_count + '&each_instal_amt=' + each_instal_amt + '&total_amount=' + total_amount);

            if (interest_amount >0) {
                interest_amount = parseFloat(interest_amount).toFixed(2);

                if ($('tr.installments-option').hasClass('hidden')) {
                    $('tr.installments-option').removeClass('hidden');
                }

                $('#interest-amount').empty();
                $('#installments-count').empty();
                $('#installments-count').append(installments_count);
                $('#interest-amount').append(interest_amount + " " + sign);
            } else {
                if (! $('tr.installments-option').hasClass('hidden')) {
                    $('.installments-option').addClass('hidden');
                }
            }

            /*Changing Total amount at the right side cart details*/
            $('#spc-total-price').empty();
            $('#spc-total-price').append(total_amount.toFixed(2)+" "+sign);

            /* Changing the total amount on changing the installments */
            $('#submit_total').empty();
            $('#submit_total').append(total_amount.toFixed(2) + " " + sign);
            $('#finalTotal').attr('value', total_amount);
            $('#instlmnt').attr('value', installments_count);
        }
    });

    /* Fancy box for Sales-Agreements & Installments popup */
    $("a.agree").fancybox({
        autoSize: false,
        width: 865,
        height: 400,
        margin: 2,
        padding: 5,
        titleShow: false,
        centerOnScroll: true,
        overlayShow: true,
        overlayColor: '#000',
        type: 'iframe'
    });

    /* Validating, whether all the fields are entered or not*/
    $('#pay_button').click(function(e) {
        var credit_card_input = $('#ccnum').val();
        var cc_exp_year = $('#cc-exp-year').val();
        var cc_exp_month = $('#cc-exp-month').val();
        var ccvv2 = $('#ccvv2').val();

        if (credit_card_input === '' || cc_exp_year === '00' || cc_exp_month === '00' || ccvv2 === '') {
            alert(missing_field_error);

            return false;
        }

        if (! $('input[name="check_agree"]:checked').length) {
            alert(error_msg);

            return false;
        }

        if (! $('.instal:checked').length) {
            alert(installment_error_msg);

            return false;
        }

        if ($('input[name="orderProcessing"]').hasClass('hidden')) {
            $('input[name="paymentSubmit"]').addClass('hidden');
            $('input[name="orderProcessing"]').removeClass('hidden');
        }
        if($('#payment-process').hasClass('hidden')){
            $('#payment-process').removeClass('hidden')
            $('#payment-checkout').hide();
            $('#address-checkout').hide();
            $('#payment-process').show();
        }
    });

    $('#id_cell_phone,.turkcell-card, #turkcell-bonus').change(function() {
        var phone_number = $('#id_cell_phone').val();
        var chosen_card = $("select[name='card_choice'] option:selected").val();

        $('input[name="paymentSubmit"]').attr('disabled', true);

        if (phone_number !== '' && chosen_card !== 0 && phone_number.length === 10) {
            $('input[name="paymentSubmit"]').attr('disabled', false);
        }
    });

    $('.turkcell-card').change(function() {
        var chosen_card = $("select[name='card_choice'] option:selected").val();

        $('#turkcell-bonus').hide();
        $('#turkcell-installments').hide();

        if (chosen_card == 2 || chosen_card == 3) {
            $('#turkcell-installments').show();
        } else {
            $('select[name="installment_choice"]')
                .find("option:contains('No Installments')")
                .attr("selected", "selected");

            $('#turkcell-installment').trigger('change');
        }

        if (chosen_card == 2) {
            $('#turkcell-bonus').show();
        }
    });

    $('#turkcell-installment').change(function() {
        var installments = $("select[name='installment_choice'] option:selected").val();

        if (installments) {
            /* Installments details */
            var installments_count = $("select[name='installment_choice'] option:selected").attr('value');
            var each_instal_amt = $("select[name='installment_choice'] option:selected").attr('instal_amt');
            var total_amount = $("select[name='installment_choice'] option:selected").attr('total');
            var interest_amount = $("select[name='installment_choice'] option:selected").attr('interest_amt');

            /* Changing the agreements on installment selection */
            $('a#agreement1').attr('href', baseDir + 'agreements.php?id_cms=20&content_only=1&instalments=' +
                installments_count + '&each_instal_amt=' + each_instal_amt + '&total_amount=' + total_amount);
            $('a#agreement2').attr('href', baseDir + 'agreements.php?id_cms=21&content_only=1&instalments=' +
                installments_count + '&each_instal_amt=' + each_instal_amt + '&total_amount=' + total_amount);
            $('a#agreement3').attr('href', baseDir + 'agreements.php?id_cms=22&content_only=1&instalments=' +
                installments_count + '&each_instal_amt=' + each_instal_amt + '&total_amount=' + total_amount);

            if (installments_count >= 6) {
                interest_amount = parseFloat(interest_amount).toFixed(2);

                if ($('tr.installments-option').hasClass('hidden')) {
                    $('tr.installments-option').removeClass('hidden');
                }

                $('#interest-amount').empty();
                $('#installments-count').empty();
                $('#installments-count').append(installments_count);
                $('#interest-amount').append(interest_amount + " " + sign);
            } else {
                if (! $('tr.installments-option').hasClass('hidden')) {
                    $('.installments-option').addClass('hidden');
                }
            }

            $('#spc-total-price').empty();
            $('#spc-total-price').append(total_amount + " " + sign);

            /* Changing the total amount on changing the installments */
            $('#submit_total').empty();
            $('#submit_total').append(total_amount + " " + sign);
            $('#finalTotal').attr('value', total_amount);
            $('#instlmnt').attr('value', installments_count);
        }
    });

    $('#turkcell-form').submit(function() {
        var img_src = baseDir + '/modules/pgtw/img/Turkcell-Cuzdan-Loading.gif';

        $.fancybox('<img src="' + img_src + '" alt=""/>', {
            className: 'tcw',
            autoSize: false,
            height: 350,
            width: 500,
            padding: 0,
            margin: 0,
            modal: true,
            scrolling: 'no',
            titleShow: false,
            centerOnScroll: true,
            overlayColor: '#000',
            showNavArrows: false
        });

        $('#fancybox-wrap').addClass('tcw');

        /* Request1 Calling*/
        var phoneNumber = $('#id_cell_phone').val();
        var chosenCard = $("select[name='card_choice'] option:selected").val();
        var installmentChosen = $("select[name='installment_choice'] option:selected").val();
        var bonus = $('input[name="bonus"]').val();
        var finalTotal = $('#finalTotal').val();

        $.ajax({
            type: 'GET',
            url: baseDir + 'modules/pgtw/validation.php',
            async: true,
            cache: false,
            dataType: 'json',
            data: {
                req: 1,
                cell_phone: phoneNumber,
                card_choice: chosenCard,
                finalTotal: finalTotal,
                installment_choice: installmentChosen,
                bonus: bonus
            },
            success: function(res) {
                if (res.transStatus == 'success') {
                    /* Request2 Calling */
                    req2cellPhone = res.cellPhone;
                    req2cardChoice = res.cardChoice;
                    req2installment = res.installment;
                    req2finalTotal = res.finalTotal;
                    req2bonus = res.bonus;
                    trigger_count = 1;

                    setTimeout("trigger_request()", 5000);
                } else {
                    var img_src = baseDir + '/modules/pgtw/img/Turkcell-Cuzdan-Error.png';

                    $.fancybox('<img src="' + img_src + '" alt=""/>', {
                        autoSize: false,
                        height: 350,
                        width: 500,
                        padding: 0,
                        margin: 0,
                        scrolling: 'no',
                        titleShow: false,
                        centerOnScroll: true,
                        hideOnContentClick: true,
                        hideOnOverlayClick: false,
                        showCloseButton: false,
                        enableEscapeButton: false,
                        overlayColor: '#000',
                        showNavArrows: false,
                        onClosed: function () {
                            $('#fancybox-wrap').removeClass('tcw');
                        }
                    });

                    $('#fancybox-wrap').addClass('tcw');
                }
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
                alert("TECHNICAL ERROR: unable to contact bank.\n\nDetails:\nError thrown: " +
                    XMLHttpRequest + "\n" + 'Text status: ' + textStatus);

                $.fancybox.close();

                $('#fancybox-wrap').removeClass('tcw');
            }
        });

        return false;
    });
});

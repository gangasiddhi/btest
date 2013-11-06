/* 
 * valentines-day script for the valentine's Day Form Placeholders
 */

//console.log('valentine day script');

$(function(){
    
    $('#customer_name').focusin(function(){
        if($.trim($(this).val()) == 'Bir Dost') {
            $(this).val('');
        }
    //console.log('ada focuslandı');
    });
    $('#customer_name').focusout(function(){
        if($.trim($(this).val()) == '') {
            $(this).val('Bir Dost');  
        }
    //console.log('ada focus kapatıldı');
    });
    $('#emailSubject').focusin(function(){
        if($.trim($(this).val()) == "14 Şubat'ı onunla geçirmek istiyor") {
            $(this).val('');
        }
    //console.log('ada focuslandı');
    });
    $('#emailSubject').focusout(function(){
        if($.trim($(this).val()) == '') {
            $(this).val("14 Şubat'ı onunla geçirmek istiyor");  
        }
    //console.log('ada focus kapatıldı');
    });
    
});


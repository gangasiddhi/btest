$(document).ready(function() {
 $('dd').hide();

 $('.toggle_dd').click(function(){
 if($(this).next("dd").is(':visible') ){
$(this).removeClass('open').next("dd").slideUp('slow');
 }
 else{
 $(this).addClass('open').next("dd").slideDown('slow');
 }

 });});

 function expandAll() {
 $('dd').slideDown('slow').prev('dt').addClass('open');
 }

function collapseAll() {
 $('dd').slideUp('slow').prev('dt').removeClass('open');
 }


var myMessages = ['error','info','warning','success'];
function hideAllMessages()
{
                 var messagesHeights = new Array(); // this array will store height for each

                 for (i=0; i<myMessages.length; i++)
                 {
                                  messagesHeights[i] = $('.' + myMessages[i]).outerHeight(); // fill array
                                  $('.' + myMessages[i]).css('top', -messagesHeights[i]); //move element outside viewport
                 }
}
function showMessage(type)
{
       // $('.'+ type +'-trigger').click(function(){
                  hideAllMessages();
                  $('.'+type).animate({top:"0"}, 500);
      //  });
		//	$('.'+ type +'-trigger').trigger('click');

}
$(document).ready(function(){

                 // Initially, hide them all
                 hideAllMessages();
                 // Show message
                 for(var i=0;i<myMessages.length;i++)
                 {
                        showMessage(myMessages[i]);
                 }

                 // When message is clicked, hide it
                 $('#error-close').click(function(){
//                                  $(this).animate({top: -$(this).outerHeight()}, 500);
								  $('.message').hide();
                  });

});
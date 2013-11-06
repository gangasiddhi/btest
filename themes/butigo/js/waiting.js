/*function checkWaitingPopup(c_name, c_val, c_exp)
{
	var c_client_val = getCookie(c_name);

	if (c_client_val==null || c_client_val=="")
		setTimeout(function(){
			$("a.fbox-hiw").trigger('click');
			setCookie(c_name, c_val, c_exp);
		}, 12000);
}*/

$(document).ready(function() {
	//HIW auto popup
	checkWaitingPopup("hiw_v", 1, 2);
});

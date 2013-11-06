<?php
//	Author: iPlussoft, Brunei. http://www.iplussoft.com
//	###do not edit beyond this line###

$importerinfo = Array();
require("config.php");

function validate_email($email) {
	if (!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $email)) {
		return false;
	} else {
		return true;
	}
}

$lib = stripslashes(@$_GET['lib']);
$response = null;

global $emailtmp;
$emailtmp=null;
$username=stripslashes(@$_POST['username']);
if(!strpos($username,"@")){$username.="@gmail.com";}

$iname=null;
foreach ($importerinfo[1] as $name => $path) {
	if ($lib==$path[1]) {
		$iname=$name;
		break;
	}
}

if ($iname) {
	require($importerinfo[1][$iname][1]);
	$contactemails = import_contacts(stripslashes(@$_POST['username']),stripslashes(@$_POST['password']));
	$error='<p style="margin:10px; font:12px arial,sans-serif; color:black;">Giriş yapılamadı. Lütfen kullanıcı adınızı ve şifrenizi kontrol edip, tekrar deneyiniz.</p>';
}

if (is_array($contactemails)) {
	$response .= '<h1>Son olarak arkadaşlarınızı seçip<br/>"Davet Et" butonuna tıklayınız.</h1>
					<form method="POST" action="'.htmlspecialchars($postpath).'">
					  <div style="background:#eee; border-bottom:2px solid #fbafe7; width:100%">
						<p style="margin:0; padding:10px; font:12px arial,sans-serif; color:black;">
							<a class="b" onclick="javascript: select_all_win(1);" href="javascript: void(0);">Tümünü Seç</a> | <a class="b" onclick="javascript: select_all_win(0);" href="javascript: void(0);">Tümünü Kaldır</a>
						</p>
					  </div>
					  <div style="overflow:auto; height:265px">
						<table id="list" border="0" style="border-collapse:collapse;" cellpadding="0" width="100%" cellspacing="0">';
	foreach($contactemails as $id => $contact){
		$response .= '
						<tr style="font:normal 12px/14px arial,helvetica,sans-serif; height:24px; border-bottom:1px solid #f5f5f5">
							<td width="20"><input type="hidden" name="name[]" value="'.htmlspecialchars(@$contact[0]).'"><input type="hidden" name="email[]" value="'.htmlspecialchars(@$contact[1]).'"><input type="checkbox" name="selcontacts[]" value="'.htmlspecialchars($id).'" checked></td>
							<td width="180">'.htmlspecialchars(@$contact[0]).'</td>
							<td width="180">'.htmlspecialchars(@$contact[1]).'</td>
						</tr>
		';
		htmlspecialchars($contact[0]).' &ndash; '.htmlspecialchars($contact[1]).'<br/>';
	}
	$response .= '		</table>
					  </div>
					  <div style="padding:10px 10px 0; border-top:2px solid #fbafe7">
						<input type="submit" id="sendButton" onmouseover="javascript:className = \' ajax_hover send\';" onmouseout="javascript:className = \'send button\';" class="send button" name="SelectContacts" value="Davet Gonder"/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span style="font:12px arial,sans-serif">'.htmlspecialchars(count($contactemails)).' kontakt bulundu</span>
					  </div>
					  <input type="hidden" name="fromemail" value="'.htmlspecialchars($username).'">
					</form>';
} else {
	$response = $error;
}

?>
<html>
<head>
<style type="text/css">
	h1 { background:#F63FC5; font:normal 20px/24px Helvetica, Arial, Verdana, sans-serif; border-bottom:1px solid #01BFF7; margin:0; padding:15px 5px; text-align:center; color:#fff }
	img { border:0 }
	#ajax { float:left; width:100% }
	input.send, .ajax_hover{cursor: pointer;display: inline-block;font: bold 13px/100% Arial,Helvetica,sans-serif; outline: medium none;text-align: center;text-decoration: none;float: left;height: 32px;width:auto;padding: 0 1em 0.3em 1em;border:none;}
        input.send, .ajax_hover:hover {
	background-color: #21c7f7;
        color: #fff;
	background: -webkit-gradient(linear, left top, left bottom, from(#21c7f7), to(#21c7f7));
	background: -moz-linear-gradient(top,  #21c7f7,  #21c7f7);
	filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#21c7f7', endColorstr='#21c7f7');
}
 input.button{
	color: #fff;
	background: -webkit-gradient(linear, left top, left bottom, from(#5ddbff), to(#21c7f7));
	background: -moz-linear-gradient(top,  #5ddbff,  #21c7f7);
	filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#5ddbff', endColorstr='#21c7f7');
}
input.button:hover, .ajax_hover {
	background-color: #21c7f7;
}
input.button:active {
	color: #fff;
	background: -webkit-gradient(linear, left top, left bottom, from(#5ddbff), to(#5ddbff));
	background: -moz-linear-gradient(top,  #5ddbff,  #5ddbff);
	filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#5ddbff', endColorstr='#5ddbff');
}
</style>
<script type="text/javascript">
<!--
function get_response_ref(url) {
	document.getElementById('ajax').innerHTML = '<div align="center">Loading<br/><img src="ajax-loader.gif"></div>';
	document.getElementById('loader').innerHTML = '<iframe src="'+url+'" style="width: 1px; height: 1px;"></iframe>';
}
function select_all_win(CheckValue) {
	var objCheckBoxes = document.forms[0].elements['selcontacts[]'];
	if(!objCheckBoxes)
		return;
	var countCheckBoxes = objCheckBoxes.length;
	if(!countCheckBoxes)
		objCheckBoxes.checked = CheckValue;
	else
		for(var i = 0; i < countCheckBoxes; i++)
			objCheckBoxes[i].checked = CheckValue;
}
-->
</script>
</head>
<body onload="javascript: if (parent!=self) parent.handle_response_ref(document, 'responsedata', 'ajax');" marginwidth="0" marginheight="0" leftmargin="0" topmargin="0" bgcolor="#FFFFFF">
<div id="responsedata"><?php echo $response;?></div>
<div id="ajax"></div>
<div id="loader" name="loader" style="display: none; width: 1px; height: 1px;">&nbsp;</div>
</body>
</html>

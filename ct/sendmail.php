<html>
<head>
<title>Mails</title>
<style><!--
BODY {font: normal 11px Arial, Helvetica, Verdana, sans-serif;}
--></style>
</head>
<body>
<?php
/*
This sample send mail script is provided by Xceog.Net for free.
Please contact us for more information.
-------------------------------------------------------------------------
*/

include("config.php");
//include_once('../swift/Swift.php');
//include_once('../swift/Swift/Connection/SMTP.php');
//include_once('../swift/Swift/Connection/NativeMail.php');
//include_once('../swift/Swift/Plugin/Decorator.php');


if(@$storeemail==1){
	// Make a MySQL Connection
	mysql_connect($mysqldomain, $mysqlusername, $mysqlpassword) or die(mysql_error());
	mysql_select_db($mysqldbname) or die(mysql_error());
}

function validateemail($email) {
	if (!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $email)) {return false;}else{return true;}
}

function sendmail($to,$subject,$message,$from) {
	//set sender's email here
	//$headers = "From: $from\r\nMIME-Version: 1.0\r\nContent-Type: text/HTML; charset=utf-8\r\nReply-To: $from\r\n";
	$headers = "From: $from\n";
	/*$headers .= "Reply-To: $from\n";
	$headers .= "Return-Path: $from\n";*/
	$headers .= "MIME-Version: 1.0\n";
	$headers .= "Content-Type: text/html; charset=UTF-8\n";
	if (mail($to, $subject, $message, $headers, $from)){return true;}else{return false;}
}

/*function swiftSendMail($content, $subject, $type, $to, $from, $smtpChecked = 0, $smtpServer = '', $smtpLogin = '', $smtpPassword = '', $smtpPort = 25, $smtpEncryption = 'off')
{
	$swift = NULL;
	$result = NULL;
	try
	{
		if($smtpChecked)
		{

			$smtp = new Swift_Connection_SMTP($smtpServer, $smtpPort, ($smtpEncryption == "off") ? Swift_Connection_SMTP::ENC_OFF : (($smtpEncryption == "tls") ? Swift_Connection_SMTP::ENC_TLS : Swift_Connection_SMTP::ENC_SSL));
			$smtp->setUsername($smtpLogin);
			$smtp->setpassword($smtpPassword);
			$smtp->setTimeout(5);
			$swift = new Swift($smtp);
		}
		else
		{
			$swift = new Swift(new Swift_Connection_NativeMail());
		}

		//$message = new Swift_Message($subject, $content, $type);
		$message = new Swift_Message($subject);
		$message->attach(new Swift_Message_Part($content, 'text/html', '8bit', 'utf-8'));

		if ($swift->send($message, $to, $from))
		{
			$result = true;
		}
		else
		{
			$result = 999;
		}
		$swift->disconnect();
	}
	catch (Swift_Connection_Exception $e)
	{
		$result = $e->getCode();
	}
	catch (Swift_Message_MimeException $e)
	{
		$result = $e->getCode();
	}
	return $result;
}*/

//== Set message
$msgfile="mailcontent.html"; //enter the filename which points to your mail content file in html format e.g. "mailmessage.html"
//========================== do not edit beyond this line
ob_start(); require($msgfile); $message=ob_get_contents(); ob_end_clean();
//== End retrieval
$message = str_replace('{link}', '?ref_by='.@$_COOKIE['bu_refid'].'&utm_campaign=invitefriendsvariation1&utm_medium=viral&utm_source=invitefriendsaddbook&utm_content='.@$_COOKIE['bu_refid'], $message);
//$message = 'Test Thing';

$selcontacts = @$_POST['selcontacts'];
$name = @$_POST['name'];
$email = @$_POST['email'];
//$from = stripslashes(@$_POST['fromemail']);
$from = @$_COOKIE['bu_refname'] . " <invite@butigo.com>";
//$from = "invite@butigo.com";
//you are recommended to send using your domain's email.

if(!$selcontacts){
	$selcontacts = array();
	if(stripslashes(@$_POST['manualinvite'])=="1"){
		$tid=0;
		foreach ($email as $t) {
			array_push($selcontacts,$tid);
			$tid++;
		}
	}
}

if(!$name){$name = array();}
if(!$email){$email = array();}

$i=0;
$contactcount=0;
foreach ($selcontacts as $contacts) {
	$username = @$name[$contacts];
	$contactemail = @$email[$contacts];
	if(validateemail($contactemail)) {

		if(@$storeemail==1) {
			//DB Saves Email Addresses
			$ok=1;
			$mysql=mysql_query("SELECT * FROM contacts WHERE email='".addslashes($contactemail)."' LIMIT 1");
			while($ary=@mysql_fetch_array($mysql)){$ok=0;}
			if($ok==1){
				mysql_query("INSERT INTO contacts
				(email) VALUES('".addslashes($contactemail)."') ")
				or die(mysql_error());
			}
		}

		//put your subject in PHP Alphanumeric Format e.g. "Hello. This is a test..."
		$subject = $username.", seni Butigo'ya davet ediyorum";
		$message_final = str_replace('{firstname_friend}', $username, $message);

		if(@sendmail($contactemail, $subject, $message_final, $from)) {
		//if(swiftSendMail($message_final, $subject, 'text/html', $contactemail, $from) === true) {
			echo htmlspecialchars($contactemail,ENT_QUOTES)." &ndash; <span style='color:green'>Mail Başarıyla gönderildi.</span><br/>";
			$contactcount++;
		} else {
			echo htmlspecialchars($contactemail,ENT_QUOTES)." &ndash; <span style='color:red'>Email Gönderimi Başarısız oldu.</span><br/>";
		}

		$i++;
	}
}

echo "<div style=\"margin-top:15px; padding:5px 0; background:#f4f4f4;\"><strong>Toplam ".htmlspecialchars($contactcount,ENT_QUOTES)." email adresine başarıyla davet gönderildi. Bu pencereyi kapatmak için sağ üst köşedeki çarpı işaretini kullanabilirsiniz.</strong></div>";
?>
</body>
</html>
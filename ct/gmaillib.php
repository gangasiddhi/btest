<?php
set_time_limit(0);
/*For Use By Xceog Default (Index.php). You may remove if index.php is not used*/
$setpicture="gmail.jpg";
$emaildomain="gmail.com";
/*End A.Conf*/


function validateemail($email){
if (!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $email)) {return false;}else{return true;}
}


function unlinkcookie(){
global $cookiepath;
/*Remove Cookie File After Session !important*/
global $cookiepath; @unlink($cookiepath);
return;
}


function curlsetopt($url,$post="",$follow=1,$debugmode=0,$header=0){
global $curlstatus,$cookiepath;
$ch=curl_init();
curl_setopt($ch,CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.1.8) Gecko/20100202 Firefox/3.5.8 GTB6 (.NET CLR 3.5.30729)");
curl_setopt($ch,CURLOPT_URL,$url);
curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
curl_setopt($ch,CURLOPT_COOKIEJAR,$cookiepath);
curl_setopt($ch,CURLOPT_COOKIEFILE,$cookiepath);
curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,0);
curl_setopt($ch,CURLOPT_HEADER,$header);
curl_setopt($ch,CURLOPT_FOLLOWLOCATION,$follow);
if($post){curl_setopt($ch, CURLOPT_POST,1); curl_setopt($ch,CURLOPT_POSTFIELDS,$post);}
$returned=curl_exec($ch);
$curlstatus=curl_getinfo($ch);
curl_close($ch);

if($debugmode){echo "<br/>==========================================================================================<br/><b>Calling URL:</b> ".htmlspecialchars($url,ENT_QUOTES)."<br/><b>Cookie Path:</b> ".htmlspecialchars($cookiepath,ENT_QUOTES)."<br/>==========================================================================================<br/>".htmlspecialchars($returned,ENT_QUOTES)."<br/><br/>==========================================================================================<br/><br/>"; exit;}
return $returned;
}

function import_contacts($email,$password){
global $curlstatus,$cookiepath,$emailtmp;
include("config.php");

$ct=0;
while(file_exists($cookiepath."xgcurlcookie".$ct.".xgc")===true){$ct++;}
$cookiepath.="xgcurlcookie".$ct.".xgc";

//Automatically inject @domain.com into email if @ is not detected
if(!strpos($email,"@")){$email.="@gmail.com";}
$emailtmp=$email;

/*Get email domain*/
preg_match('/.*\@([^\@]*)/',$email,$getdomain);
$getdomain=strtolower(trim(@$getdomain[1]));

$xreturn=curlsetopt("https://accounts.google.com/o/oauth2/auth?client_id=372197488838.apps.googleusercontent.com&redirect_uri=http://localhost/butigo/ct/gmailInvite.php&scope=https://www.google.com/m8/feeds/&response_type=code",0,1,0);

if($writelogs == 1)
{	$errorLogFile = @fopen(_PS_ROOT_DIR_.'/log/invite_firends_track_gmail.txt', "a");
	fwrite($errorLogFile,'GMAIL- before login');
	fwrite($errorLogFile, sprintf("\n%s:- %s\n",date("D M j G:i:s T Y"),$xreturn."\n"));
	fclose($errorLogFile);
}

/*Get Form Hidden Inputs*/
$inputs=conv_hiddens($xreturn);

/*Get Form POST action page*/
/*Note that the link returned is a relative link not absolute link*/
preg_match('/<form[^>]+action\="([^"]*)"[^>]*>/',$xreturn,$getlink);
//<form method="post" action="https://accounts.google.com/ServiceLoginAuth" id="gaia_loginform">
$xreturn=curlsetopt("https://accounts.google.com/ServiceLoginAuth","Email=".urlencode($email)."&Passwd=".urlencode($password).conv_hiddens2txt($inputs),1,0,1);

if($writelogs == 1)
{
	$errorLogFile = @fopen(_PS_ROOT_DIR_.'/log/track_gmail_step1.txt', "a");
	fwrite($errorLogFile,'GMAIL- after login step 1');
	fwrite($errorLogFile, sprintf("\n%s:- %s%s%s\n",date("D M j G:i:s T Y"),urlencode($email)."&passwd=".urlencode($password).conv_hiddens2txt($inputs),$xreturn,$getlink."\n"));
	fwrite($errorLogFile, sprintf("\n%s:- %s\n",date("D M j G:i:s T Y"),$xreturn."\n"));
	fclose($errorLogFile);
}

if(!seek_val($xreturn,'GALX')){
$xreturn=curlsetopt("http://mail.google.com/mail/?ui=2",0,1,0);
//$xreturn=curlsetopt("https://mail.google.com/mail/h/?zy=e&f=1",0,1,0);

if($writelogs == 1)
{
	$errorLogFile = @fopen(_PS_ROOT_DIR_.'/log/track_gmail_step2.txt', "a");
	fwrite($errorLogFile,'GMAIL - after login step 2');
	fwrite($errorLogFile, sprintf("\n%s:- %s\n",date("D M j G:i:s T Y"),$xreturn."\n"));
	fclose($errorLogFile);
}

$xreturn=curlsetopt("https://mail.google.com/mail/h/?&v=cl&pnl=a",0,1,0);

if($writelogs == 1)
{
	$errorLogFile = @fopen(_PS_ROOT_DIR_.'/log/track_gmail_step3.txt', "a");
	fwrite($errorLogFile,'GMAIL - after login step 3');
	fwrite($errorLogFile, sprintf("\n%s:- %s\n",date("D M j G:i:s T Y"),$xreturn."\n"));
	fclose($errorLogFile);
}

preg_match_all('|<td[^><]*>\n<b>\n<a href\="\?\&v\=ct\&ct\_id\=[^"]*" id\="[^"]*">\n([^><]*)<\/a>\n<\/b>\n<\/td>\n<td[^><]*>\n([^><]*)\n\&nbsp\;\n<\/td>|',$xreturn,$records,PREG_SET_ORDER);

if($writelogs == 1)
{
	$errorLogFile = @fopen(_PS_ROOT_DIR_.'/log/track_gmail_pregm1_s4.txt', "a");
	fwrite($errorLogFile,'GMAIL - first preg_match_all step 4');
	//fwrite($errorLogFile, sprintf("\n%s:- %s\n",date("D M j G:i:s T Y"),$records."\n"));
	fwrite("emails array = " . print_r($records,true) . "\n");
	fclose($errorLogFile);
}

if (!$records)
{
	preg_match_all('|<td[^><]*> <b> <a href\="\?\&v\=ct\&ct\_id\=[^"]*" id\="[^"]*"> ([^><]*)<\/a> <\/b> <\/td> <td[^><]*> ([^><]*) \&nbsp\; <\/td>|',$xreturn,$records,PREG_SET_ORDER);
	if($writelogs == 1)
	{
		$errorLogFile = @fopen(_PS_ROOT_DIR_.'/log/track_gmail_pregm2_s4.txt', "a");
		fwrite($errorLogFile,'GMAIL - second preg_match_all step 4');
		//fwrite($errorLogFile, sprintf("\n%s:- %s\n",date("D M j G:i:s T Y"),$records."\n"));
		fwrite("emails array = " . print_r($records,true) . "\n");
		fclose($errorLogFile);
	}
}

$tmp = array();
foreach($records as $record){
$contact=array($record[1],$record[2]);
/*Filter out blank email and invalid email address !important*/
if(@$contact[1]&&validateemail(@$contact[1])){array_push($tmp,$contact);}
}

$contactemails=$tmp;

if($writelogs == 1)
{
	$errorLogFile = @fopen(_PS_ROOT_DIR_.'/log/track_gmail_contacts.txt', "a");
	fwrite($errorLogFile,'GMAIL - all contacts step 5');
	fwrite($errorLogFile, sprintf("\n%s:- %s\n",date("D M j G:i:s T Y"),$contactemails."\n"));
	fclose($errorLogFile);
}

unlinkcookie();
return $contactemails;
}

//if Account is not valid account
unlinkcookie();
return false;
}

function seek_val($html,$seek){
preg_match('|<input[^>]+name\="'.preg_quote($seek,'|').'"[^>]+value\="([^"]*)"[^>]*>|',$html,$getinputs);
return @$getinputs[1];
}

function conv_hiddens($html){
preg_match_all('|<input[^>]+type="hidden"[^>]+name\="([^"]+)"[^>]+value\="([^"]*)"[^>]*>|',$html,$getinputs,PREG_SET_ORDER);
return $getinputs;
}


function conv_hiddens2txt($getinputs){
$ac=null;
foreach($getinputs as $eachinput){$ac.="&".urlencode(html_entity_decode(@$eachinput[1]))."=".urlencode(html_entity_decode(@$eachinput[2]));}
return $ac;
}
?>
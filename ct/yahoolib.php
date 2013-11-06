<?php
set_time_limit(0);
/*For Use By Xceog Default (Index.php). You may remove if index.php is not used*/
$setpicture="yahoo.jpg";
$emaildomain="yahoo.com";
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
curl_setopt($ch,CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.1.8) Gecko/20100202");
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
if(!strpos($email,"@")){$email.="@yahoo.com";}
$emailtmp=$email;

/*Get email domain*/
preg_match('/.*\@([^\@]*)/',$email,$getdomain);
$getdomain=strtolower(trim(@$getdomain[1]));

$xreturn=curlsetopt("https://login.yahoo.com/config/login_verify2?&.src=ym",0,1,0);
$inputs=conv_hiddens($xreturn);

if($writelogs == 1)
{
	$errorLogFile = @fopen(_PS_ROOT_DIR_.'/log/invite_friends_yahoo.txt', "a");
	fwrite($errorLogFile,'YAHOO - before login');
	fwrite($errorLogFile, sprintf("\n%s:- %s\n",date("D M j G:i:s T Y"),$xreturn."\n"));
	fwrite($errorLogFile, sprintf("\n%s:- %s\n",date("D M j G:i:s T Y"),$inputs."\n"));
	fclose($errorLogFile);
}
$xreturn=curlsetopt("https://login.yahoo.com/config/login?","login=".urlencode($email)."&passwd=".urlencode($password)."&.done=http%3a//mail.yahoo.com&.save=Sign+In".conv_hiddens2txt($inputs),1,0);

if($writelogs == 1)
{
	$errorLogFile = @fopen(_PS_ROOT_DIR_.'/log/yahoo_login_step1.txt', "a");
	fwrite($errorLogFile,'YAHOO - after login step 1');
	fwrite($errorLogFile, sprintf("\n%s:- %s%s\n",date("D M j G:i:s T Y"),urlencode($email)."&passwd=".urlencode($password)."&.done=http%3a//mail.yahoo.com&.save=Sign+In".conv_hiddens2txt($inputs),$xreturn."\n"));
	fwrite($errorLogFile, sprintf("\n%s:- %s\n",date("D M j G:i:s T Y"),$xreturn."\n"));
	fclose($errorLogFile);
}

$xreturn=curlsetopt("https://login.yahoo.com/config/verify?.done=http%3a//mail.yahoo.com",0,1,0);

if($writelogs == 1)
{
	$errorLogFile = @fopen(_PS_ROOT_DIR_.'/log/yahoo_login_stepv2.txt', "a");
	fwrite($errorLogFile,'YAHOO - after login step 2');
	fwrite($errorLogFile, sprintf("\n%s:- %s\n",date("D M j G:i:s T Y"),$xreturn."\n"));
	fclose($errorLogFile);
}
$xreturn=curlsetopt("http://address.yahoo.com/yab/us?A=B",0,1,0);

if($writelogs == 1)
{
	$errorLogFile = @fopen(_PS_ROOT_DIR_.'/log/yahoo_login_step3.txt', "a");
	fwrite($errorLogFile,'YAHOO - after login step 3');
	fwrite($errorLogFile, sprintf("\n%s:- %s\n",date("D M j G:i:s T Y"),$xreturn."\n"));
	fclose($errorLogFile);
}

if(!strncmp(@$curlstatus['url'],"http://address.yahoo.com/yab/us?",32)){

$inputs=conv_hiddens($xreturn);

$xreturn=curlsetopt("http://address.mail.yahoo.com/?_src=&VPC=tools_print",".src=&VPC=print&field%5Ballc%5D=1&field%5Bcatid%5D=0&field%5Bstyle%5D=quick&submit%5Baction_display%5D=Display+for+Printing",1,0);

if($writelogs == 1)
{
	$errorLogFile = @fopen(_PS_ROOT_DIR_.'/log/yahoo_login_step4.txt', "a");
	fwrite($errorLogFile,'YAHOO - after login step 4');
	fwrite($errorLogFile, sprintf("\n%s:- %s\n",date("D M j G:i:s T Y"),$xreturn."\n"));
	fclose($errorLogFile);
}

/*Get the Mapping of the contact table cells*/
$ftext='<table class="qprintable" border="0" cellpadding="0" cellspacing="1" width="600">';
$fpos=strpos($xreturn,$ftext);
$spos=strrpos($xreturn,'<tr class="qprintfoot">');

/*Focus on the important area*/
$xreturn=substr($xreturn,$fpos+strlen($ftext),$spos-$fpos-strlen($ftext));

if($writelogs == 1)
{
	$errorLogFile = @fopen(_PS_ROOT_DIR_.'/log/yahoo_login_step5.txt', "a");
	fwrite($errorLogFile,'YAHOO - after login step 5');
	fwrite($errorLogFile, sprintf("\n%s:- %s\n",date("D M j G:i:s T Y"),$xreturn."\n"));
	fclose($errorLogFile);
}

$contactemails=explode('<td valign="top" width="200">',$xreturn);



$tmp=array();
foreach($contactemails as $contact){

preg_match('/\<b\> ([^<>]*) \<\/b\>/',$contact,$getname);
$getname=html_entity_decode(trim(@$getname[1]));

if(!$getname){
preg_match('/\<font color\=\"green\"\>\<i\>\(([^<>]*)\)\<\/i\>\<\/font\>/',$contact,$getnames);
$getname=html_entity_decode(trim(@$getnames[1]));
}

preg_match('/\&nbsp\;\-\&nbsp\;\<small\>([^<>]*)\<\/small\>/',$contact,$getemail);
$getemaila=html_entity_decode(@$getemail[1]);

preg_match('/\<div\>([^<>]*)\<\/div\>/',$contact,$getemail);
$getemailb=html_entity_decode(@$getemail[1]);

$email=null;
if($getemaila){$email=$getemaila."@".$getdomain;}
if($getemailb){$email=$getemailb;}

$contact=array($getname,$email);

/*Filter out blank email and invalid email address !important*/
if(@$contact[1]&&validateemail(@$contact[1])){array_push($tmp,$contact);}}
$contactemails=$tmp;

if($writelogs == 1)
{
	$errorLogFile = @fopen(_PS_ROOT_DIR_.'/log/yahoo_login_emails.txt', "a");
	fwrite($errorLogFile,'YAHOO - after login step 6 - contact emails');
	//fwrite($errorLogFile, sprintf("\n%s:- %s\n",date("D M j G:i:s T Y"),$contactemails."\n"));
	fwrite("emails array = " . print_r($records,true) . "\n");
	fclose($errorLogFile);
}

unlinkcookie();
return $contactemails;
}

//if Account is not valid account
unlinkcookie();
return false;
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
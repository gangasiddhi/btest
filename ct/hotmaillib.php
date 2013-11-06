<?php
set_time_limit(0);
/*For Use By Xceog Default (Index.php). You may remove if index.php is not used*/
$setpicture="hotmail.jpg";
$emaildomain="hotmail.com";
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
curl_setopt($ch,CURLOPT_USERAGENT, "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10_6_7; da-dk) AppleWebKit/533.21.1 (KHTML, like Gecko) Version/5.0.5 Safari/533.21.1");
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
if(!strpos($email,"@")){$email.="@hotmail.com";}
$emailtmp=$email;


/*Get email domain*/
preg_match('/.*\@([^\@]*)/',$email,$getdomain);

$getdomain=strtolower(trim(@$getdomain[1]));


$xreturn=curlsetopt("https://login.live.com/ppsecure/post.srf",0,1,0,1);

if($writelogs == 1)
{
	$errorLogFile = @fopen(_PS_ROOT_DIR_.'/log/invite_friends_hotmail.txt', "a");
	fwrite($errorLogFile,'HOTMAIL - before login');
	fwrite($errorLogFile, sprintf("\n%s:- %s\n",date("D M j G:i:s T Y"),$xreturn."\n"));
	fclose($errorLogFile);
}

/*Get Form Hidden Inputs*/
$inputs=conv_hiddens($xreturn);


$xreturn=curlsetopt("https://login.live.com/ppsecure/post.srf?bk=1310356782","login=".urlencode($email)."&passwd=".urlencode($password).conv_hiddens2txt($inputs),1,0,1);

if($writelogs == 1)
{
	$errorLogFile = @fopen(_PS_ROOT_DIR_.'/log/hotmail_login_step1.txt', "a");
	fwrite($errorLogFile,'HOTMAIL - after login step 1');
	fwrite($errorLogFile, sprintf("\n%s:- %s%s\n",date("D M j G:i:s T Y"),urlencode($email)."&passwd=".urlencode($password)."&.done=http%3a//mail.yahoo.com&.save=Sign+In".conv_hiddens2txt($inputs),$xreturn."\n"));
	fwrite($errorLogFile, sprintf("\n%s:- %s\n",date("D M j G:i:s T Y"),$xreturn."\n"));
	fclose($errorLogFile);
}

preg_match('/<form[^>]+name=\"fmHF\"[^>]+action\="([^"]*)"[^>]*>/',$xreturn,$ismsn);
if (@$ismsn[1]) $xreturn=curlsetopt(@$ismsn[1],0,1,0,1);

if($writelogs == 1 && @$ismsn[1])
{
	$errorLogFile = @fopen(_PS_ROOT_DIR_.'/log/hotmail_login_step_ismsn_2.txt', "a");
	fwrite($errorLogFile,'HOTMAIL - after login step 2');
	fwrite($errorLogFile, sprintf("\n%s:- %s\n",date("D M j G:i:s T Y"),$xreturn."\n"));
	fclose($errorLogFile);
}

/*
preg_match('/window\.location\.replace\("([^"]*)"\)\;/',$xreturn,$getlink);
$xreturn=curlsetopt(@$getlink[1],0,1,0,1);
preg_match('/http\:\/\/([^\.]*w\.[^\.]*\.mail\.live\.com)/',$curlstatus['url'],$checklinklive);
*/

$xreturn=curlsetopt('https://mail.live.com/?rru=inbox',0,1,0,1);

if($writelogs == 1)
{
	$errorLogFile = @fopen(_PS_ROOT_DIR_.'/log/hotmail_login_step_inbox_3.txt', "a");
	fwrite($errorLogFile,'HOTMAIL - after login step 3');
	fwrite($errorLogFile, sprintf("\n%s:- %s\n",date("D M j G:i:s T Y"),$xreturn."\n"));
	fclose($errorLogFile);
}

preg_match('/https\:\/\/([^\.]*\.mail\.live\.com)/',$curlstatus['url'],$checklinklive);

if(@$checklinklive[1]){
$checklinklive[1]='https://'.@$checklinklive[1].'/';

/*If MSN Live*/

//=== Pass the message at login page
$xreturn=curlsetopt(@$checklinklive[1].'mail/MessageAtLogin.aspx?nwi=1',0,1,0,1);

if($writelogs == 1)
{
	$errorLogFile = @fopen(_PS_ROOT_DIR_.'/log/hotmail_login_step4.txt', "a");
	fwrite($errorLogFile,'HOTMAIL - after login step 4');
	fwrite($errorLogFile, sprintf("\n%s:- %s\n",date("D M j G:i:s T Y"),$xreturn."\n"));
	fclose($errorLogFile);
}

$inputs=conv_hiddens($xreturn);
$xreturn=curlsetopt(@$checklinklive[1].'mail/MessageAtLogin.aspx?nwi=1%2c1&n=2019338777',"TakeMeToInbox=Continue".conv_hiddens2txt($inputs),1,0);

if($writelogs == 1)
{
	$errorLogFile = @fopen(_PS_ROOT_DIR_.'/log/hotmail_login_step5.txt', "a");
	fwrite($errorLogFile,'HOTMAIL - after login step 5');
	fwrite($errorLogFile, sprintf("\n%s:- %s\n",date("D M j G:i:s T Y"),$xreturn."\n"));
	fwrite($errorLogFile, sprintf("\n%s:- %s\n",date("D M j G:i:s T Y"),$inputs."\n"));
	fclose($errorLogFile);
}

//== end pass
preg_match('|\"\/mail\/EditMessageLight\.aspx\&\#63\;n\&\#61\;([0-9]+)\"|',$xreturn,$getn);

$xreturn=curlsetopt(@$checklinklive[1]."mail/EditMessageLight.aspx?n=".urlencode(@$getn[1]),"",1,0,1);

if($writelogs == 1)
{
	$errorLogFile = @fopen(_PS_ROOT_DIR_.'/log/hotmail_login_step6.txt', "a");
	fwrite($errorLogFile,'HOTMAIL - after login step 6');
	fwrite($errorLogFile, sprintf("\n%s:- %s\n",date("D M j G:i:s T Y"),$xreturn."\n"));
	fclose($errorLogFile);
}

preg_match('|\"ContactList\.aspx\?n\=([0-9]+)\&mt\=([^\"]+)\"|',$xreturn,$getinfo);
$getinfo[1] = urldecode(@$getinfo[1]);
$getinfo[2] = urldecode(@$getinfo[2]);

$xreturn=curlsetopt(@$checklinklive[1]."mail/ContactList.aspx?n=".urlencode(@$getinfo[1])."&mt=".urlencode(@$getinfo[2]),"",1,0,1);

if($writelogs == 1)
{
	$errorLogFile = @fopen(_PS_ROOT_DIR_.'/log/hotmail_login_step7.txt', "a");
	fwrite($errorLogFile,'HOTMAIL - after login step 7');
	fwrite($errorLogFile, sprintf("\n%s:- %s\n",date("D M j G:i:s T Y"),$xreturn."\n"));
	fclose($errorLogFile);
}

preg_match("!\{contactData\:\[([^\}\{]*\]\]\]\]\,)!",$xreturn,$getcontactsary);

preg_match_all("!\[[^,]*,[^,]*,'([^']*)',[^,]*,[^,]*,[^,]*,\[\[[^,]*,\['([^']*)'\]\]\]\]!Uis",@$getcontactsary[1],$contacts,PREG_SET_ORDER);

$tmp=array();
foreach ($contacts as $contact){
	$getname=conv($contact[1]);
	$email=conv($contact[2]);

	$contact=array($getname,$email);

	/*Filter out blank email and invalid email address !important*/
	if(@$contact[1]&&validateemail(@$contact[1])){array_push($tmp,$contact);}
}

$contactemails=$tmp;

if($writelogs == 1)
{
	$errorLogFile = @fopen(_PS_ROOT_DIR_.'/log/hotmail_login_emails.txt', "a");
	fwrite($errorLogFile,'HOTMAIL - contact list after login step 8');
	fwrite("emails array = " . print_r($contacts,true) . "\n");
	fclose($errorLogFile);
}

unlinkcookie();
return $contactemails;
}


//if Account is not MSN Accounts
unlinkcookie();
return false;
}


function isexist($ary,$dt){
foreach($ary as $scont){if(@$scont[1]==$dt){return true;}}
return false;
}


function conv_hiddens($html){
preg_match_all('|<input[^>]+type="hidden"[^>]+name\="([^"]+)"[^>]+value\="([^"]*)"[^>]*>|',$html,$getinputs,PREG_SET_ORDER);
return $getinputs;
}


function conv_hiddens2txt($getinputs){
$ac=null;
foreach($getinputs as $eachinput){if(@$eachinput[2]) $ac.="&".urlencode(html_entity_decode(@$eachinput[1]))."=".urlencode(html_entity_decode(@$eachinput[2]));}
return $ac;
}

function conv($str) {
$str = preg_replace("/\\\x([a-zA-Z0-9]{2})/e",
	"unichr('\\1')",
	$str);
return html_entity_decode($str,ENT_NOQUOTES,'UTF-8');
}

function unichr($hex) {
$dec = hexdec($hex);

  if ($dec < 128) {
    $utf = chr($dec);
  } else if ($dec < 2048) {
    $utf = chr(192 + (($dec - ($dec % 64)) / 64));
    $utf .= chr(128 + ($dec % 64));
  } else {
    $utf = chr(224 + (($dec - ($dec % 4096)) / 4096));
    $utf .= chr(128 + ((($dec % 4096) - ($dec % 64)) / 64));
    $utf .= chr(128 + ($dec % 64));
  }
  return $utf;
}

?>
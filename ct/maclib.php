<?php
set_time_limit(0);
/*For Use By Xceog Default (Index.php). You may remove if index.php is not used*/
$setpicture="mac.jpg";
$emaildomain="mac.com";
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
curl_setopt($ch,CURLOPT_USERAGENT, "XGContacts Importer v2.0");
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
if(!strpos($email,"@")){$email.="@mac.com";}
$emailtmp=$email;

/*Get email domain*/
preg_match('/.*\@([^\@]*)/',$email,$getdomain);
$getdomain=strtolower(trim(@$getdomain[1]));

$xreturn=curlsetopt("http://www.mac.com/WebObjects/Webmail.woa",0,1,0);

/*Get Form Hidden Inputs*/
$inputs=conv_hiddens($xreturn);

/*Get Form POST action page*/
/*Note that the link returned is a relative link not absolute link*/
preg_match('/<form[^>]+id="CALoginForm"[^>]+action\="([^"]*)"[^>]*>/',$xreturn,$getlink);

$xreturn=curlsetopt(@$getlink[1],"username=".urlencode($email)."&password=".urlencode($password).conv_hiddens2txt($inputs),1,0);

if(substr(@$curlstatus['url'],0,45)=="http://www.mac.com/WebObjects/Webmail.woa/wa/"){

$xreturn=curlsetopt("http://www.mac.com/WebObjects/AddressBook.woa/wa/default",0,1,0);

preg_match_all('|<a[^><]* class="sendMail"[^><]* title="([^"]*) &lt;([^"]*)&gt;"[^><]*>|',$xreturn,$contactemails,PREG_SET_ORDER);

/*Cancel out the first array value and Filter*/
$tmp=array();
foreach($contactemails as $contact){
array_shift($contact);

/*Decode HTML Entities*/
$contact[0]=html_entity_decode(@$contact[0]);
$contact[1]=html_entity_decode(@$contact[1]);

/*Filter out blank email and invalid email address !important*/
if(@$contact[1]&&validateemail(@$contact[1])){array_push($tmp,$contact);}}
$contactemails=$tmp;

unlinkcookie();
return $contactemails;
}

//if Account is not valid account
unlinkcookie();
return false;
}


function conv_hiddens($html){
preg_match_all('|<input[^>]+type="hidden"[^>]+value\="([^"]+)"[^>]+name\="([^"]*)"[^>]*>|',$html,$getinputs,PREG_SET_ORDER);
return $getinputs;
}


function conv_hiddens2txt($getinputs){
$ac=null;
foreach($getinputs as $eachinput){$ac.="&".urlencode(html_entity_decode(@$eachinput[2]))."=".urlencode(html_entity_decode(@$eachinput[1]));}
return $ac;
}
?>
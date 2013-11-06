<?php
set_time_limit(0);
/*For Use By Xceog Default (Index.php). You may remove if index.php is not used*/
$setpicture="msn.jpg";
$emaildomain="msn.com";
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
curl_setopt($ch,CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1) Gecko/20061010 Firefox/3.0");
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
if(!strpos($email,"@")){$email.="@msn.com";}
$emailtmp=$email;


/*Get email domain*/
preg_match('/.*\@([^\@]*)/',$email,$getdomain);

$getdomain=strtolower(trim(@$getdomain[1]));


$xreturn=curlsetopt("https://login.live.com/ppsecure/post.srf",0,1,0);


/*Get Form Hidden Inputs*/
$inputs=conv_hiddens($xreturn);

$xreturn=curlsetopt("https://login.live.com/ppsecure/post.srf?wa=wsignin1.0&rpsnv=11&ct=1253843243&rver=6.0.5285.0&wp=MBI&wreply=http:%2F%2Fmail.live.com%2Fdefault.aspx&lc=1033&id=64855&mkt=en-us&bk=1253843165","login=".urlencode($email)."&passwd=".urlencode($password)."&LoginOptions=2".conv_hiddens2txt($inputs),0,0,1);

preg_match('/<form[^>]+name=\"fmHF\"[^>]+action\="([^"]*)"[^>]*>/',$xreturn,$ismsn);
if (@$ismsn[1]) $xreturn=curlsetopt(@$ismsn[1],0,1,0,1);

preg_match('/window\.location\.replace\("([^"]*)"\)\;/',$xreturn,$getlink);
$xreturn=curlsetopt(@$getlink[1],0,1,0,1);

preg_match('/http\:\/\/([^\.]*w\.[^\.]*\.mail\.live\.com)/',$curlstatus['url'],$checklinklive);

if(@$checklinklive[1]){
$checklinklive[1]='http://'.@$checklinklive[1].'/';

/*If MSN Live*/

//=== Pass the message at login page
$xreturn=curlsetopt(@$checklinklive[1].'mail/MessageAtLogin.aspx?nwi=1',0,1,0,1);

$inputs=conv_hiddens($xreturn);
$xreturn=curlsetopt(@$checklinklive[1].'mail/MessageAtLogin.aspx?nwi=1%2c1&n=2019338777',"TakeMeToInbox=Continue".conv_hiddens2txt($inputs),1,0);

//== end pass

preg_match('|\"\/mail\/EditMessageLight\.aspx\&\#63\;n\&\#61\;([0-9]+)\"|',$xreturn,$getn);

$xreturn=curlsetopt(@$checklinklive[1]."mail/EditMessageLight.aspx?n=".urlencode(@$getn[1]),"",1,0,1);

preg_match('|\"ContactList\.aspx\?n\=([0-9]+)\&mt\=([^\"]+)\"|',$xreturn,$getinfo);
$getinfo[1] = urldecode(@$getinfo[1]);
$getinfo[2] = urldecode(@$getinfo[2]);

$xreturn=curlsetopt(@$checklinklive[1]."mail/ContactList.aspx?n=".urlencode(@$getinfo[1])."&mt=".urlencode(@$getinfo[2]),"",1,0,1);

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
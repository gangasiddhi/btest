<?php
set_time_limit(0);
/*For Use By Xceog Default (Index.php). You may remove if index.php is not used*/
$setpicture="aol.jpg";
$emaildomain="aol.com";
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
curl_setopt($ch,CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 6.1; en-US; rv:1.9.1.9) Gecko/20100315 Firefox/3.5.9 GTB7.0 (.NET CLR 3.5.30729)");
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
if(!strpos($email,"@")){$emailtmp=$email."@aol.com";}else{$emailtmp=$email;}

/*Get email domain*/
//preg_match('/.*\@([^\@]*)/',$email,$getdomain);
//$getdomain=strtolower(trim(@$getdomain[1]));

$xreturn=curlsetopt("http://my.screenname.aol.com/_cqr/login/login.psp?sitedomain=sns.webmail.aol.com&lang=en&locale=us&authLev=0&siteState=ver%3a4|rt%3aSTANDARD|at%3aSNS|ld%3awebmail.aol.com|uv%3aAOL|lc%3aen-us|mt%3aAOL|snt%3aScreenName|sid%3a3fab8b83-fad9-4d5f-b4e9-0d72574b9591&offerId=newmail-en-us-v2&seamless=novl",0,1,0);

/*Get Form Hidden Inputs*/
$inputs=conv_hiddens($xreturn);

/*Get Form POST action page*/
/*Note that the link returned is a relative link not absolute link*/
preg_match('/<form[^>]+action\="([^"]*)"[^>]*>/',$xreturn,$getlink);

preg_match('/<input type="hidden" name="usrd" value="([^"]*)">/',$xreturn,$getusr);

$xreturn=curlsetopt(@$getlink[1],"sitedomain=sns.webmail.aol.com&siteId=&lang=en&locale=us&authLev=0&siteState=ver%253A4%257Crt%253ASTANDARD%257Cat%253ASNS%257Cld%253Awebmail.aol.com%257Cuv%253AAOL%257Clc%253Aen-us%257Cmt%253AAOL%257Csnt%253AScreenName%257Csid%253Afc33ba95-36c2-4424-a630-ea91551d495a&isSiteStateEncoded=true&mcState=initialized&uitype=std&use_aam=0&_sns_fg_color_=&_sns_err_color_=&_sns_link_color_=&_sns_width_=&_sns_height_=&_sns_bg_color_=&offerId=newmail-en-us-v2&seamless=novl&regPromoCode=&idType=SN&doSSL=&redirType=&xchk=false&tab=aol&usrd=".urlencode(@$getusr[1])."&loginId=".urlencode($email)."&password=".urlencode($password),1,0,1);

preg_match('/&mcAuth=([^\'&]*)[&|\']/',$xreturn,$getmcauth);

$xreturn=curlsetopt("http://webmail.aol.com/_cqr/LoginSuccess.aspx?sitedomain=sns.webmail.aol.com&authLev=0&siteState=ver%3A4%7Crt%3ASTANDARD%7Cat%3ASNS%7Cld%3Awebmail.aol.com%7Cuv%3AAOL%7Clc%3Aen-us%7Cmt%3AAOL%7Csnt%3AScreenName%7Csid%3A616a41d1-3548-4ba1-bdf4-c9e07aa878bd&lang=en&locale=us&uitype=std&offerId=newmail-en-us-v2&mcAuth=".@$getmcauth[1],0,1,0,1);

$xreturn=curlsetopt("'http://my.screenname.aol.com/_cqr/login/login.psp?sitedomain=sns.webmail1.webmail.aol.com&lang=en&locale=us&authLev=0&siteState=ver%3a4%7crt%3aSTANDARD%7cat%3aSNS%7cld%3awebmail1.webmail.aol.com%7cuv%3aAOL%7clc%3aen-us%7cmt%3aAOL%7csnt%3aScreenName%7csid%3ade3c100b-3ee6-4b0e-91d5-98704a6090cf&offerId=newmail-en-us-v2&seamless=n",0,1,0,1);
preg_match('/&mcAuth=([^\'&]*)[&|\']/',$xreturn,$getmcauth);

$xreturn=curlsetopt("http://webmail.aol.com/_cqr/LoginSuccess.aspx?sitedomain=sns.webmail.aol.com&authLev=0&siteState=ver%3A4%7Crt%3ASTANDARD%7Cat%3ASNS%7Cld%3Awebmail.aol.com%7Cuv%3AAOL%7Clc%3Aen-us%7Cmt%3AAOL%7Csnt%3AScreenName%7Csid%3A616a41d1-3548-4ba1-bdf4-c9e07aa878bd&lang=en&locale=us&uitype=std&offerId=newmail-en-us-v2&mcAuth=".@$getmcauth[1],0,1,0,1);

$xreturn=curlsetopt("http://webmail.aol.com",0,1,0,1);

preg_match('!http\:\/\/mail\.aol\.com\/([^\/]*)\/!is',$xreturn,$getversion);

if($curlstatus['url']=="http://webmail.aol.com"){

$xreturn=curlsetopt("http://mail.aol.com/".@$getversion[1]."/aim-2/en-us/Lite/Today.aspx",0,0,0,1);
$xreturn=curlsetopt("http://mail.aol.com/".@$getversion[1]."/aim-2/en-us/Lite/ContactList.aspx?folder=Inbox&showUserFolders=False",0,0,0,1);

preg_match("|<input type=hidden name=\'user\' value=\'([^\']*)\' />|",$xreturn,$getuserid);

$xreturn=curlsetopt("http://mail.aol.com/".@$getversion[1]."/aim-2/en-us/Lite/ABExport.aspx?command=all","user=".urlencode(@$getuserid[1]).'&file=contacts&csv');

/*Match new lines*/
preg_match_all('|([^\n]*)\n|',$xreturn,$records,PREG_SET_ORDER);

$tmp=array(); $newfields=array();
foreach($records as $record){

$currentrecord=count($newfields);
$newfields[$currentrecord]=array();

$stat=0; $i=0; $storetmp=null; $skip=0;
while($i<=strlen($record[1])){

if($skip==0){
if(substr($record[1],$i,1)=="\""&&substr($record[1],$i,2)!="\"\""){
if($stat==1){$stat=0;}else{$stat=1;}
}elseif(substr($record[1],$i,1)=="\""&&substr($record[1],$i,2)=="\"\""){$skip=1;}else{$skip=0;}

if($stat==0&&(substr($record[1],$i,1)==","||substr($record[1],$i,1)=="")){
$storetmp=trim($storetmp);
if(substr($storetmp,0,1)=="\""&&substr($storetmp,strlen($storetmp)-1,1)=="\""){
$storetmp=substr($storetmp,1,strlen($storetmp)-2); //strip the limit quotes off
}
array_push($newfields[$currentrecord],$storetmp); $storetmp=null;
}else{$storetmp.=substr($record[1],$i,1);}

}else{$skip=0;}

$i++;
}
//end while
}
//end foreach

$getary=array("FirstName", "LastName", "ScreenName","E-mail","E-mail2");
$returnary=array();

$i=0;
while($i<count($newfields[0])){

$ib=0;
while($ib<count($getary)){
if($newfields[0][$i]==$getary[$ib]){$returnary[$ib]=$i;}
$ib++;
}

$i++;
}

/*Cancel out the first line (CSV Header)*/
array_shift($newfields);

$tmp=array();
foreach($newfields as $contact){

$email=@$contact[@$returnary[3]];

$getname=@$contact[@$returnary[0]];
if ($getname) $getname.=' ';
$getname.=@$contact[@$returnary[1]];

if ($email && !validateemail($email)) $email=@$contact[@$returnary[4]];
if ($email && !validateemail($email)) $email=@$contact[@$returnary[2]]."@aol.com";

$contact=array($getname,$email);

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
preg_match_all('|<input[^>]+type="hidden"[^>]+name\="([^"]+)"[^>]+value\="([^"]*)"[^>]*>|',$html,$getinputs,PREG_SET_ORDER);
return $getinputs;
}


function conv_hiddens2txt($getinputs){
$ac=null;
foreach($getinputs as $eachinput){$ac.="&".urlencode(html_entity_decode(@$eachinput[1]))."=".urlencode(html_entity_decode(@$eachinput[2]));}
return $ac;
}
?>
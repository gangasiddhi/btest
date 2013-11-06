<?php
set_time_limit(0);
/*For Use By Xceog Default (Index.php). You may remove if index.php is not used*/
$setpicture="indiatimes.jpg";
$emaildomain="indiatimes.com";
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
if(!strpos($email,"@")){$emailtmp=$email."@indiatimes.com";}else{$emailtmp=$email;}

/*Get email domain*/
preg_match('/.*\@([^\@]*)/',$email,$getdomain);
$getdomain=strtolower(trim(@$getdomain[1]));

$xreturn=curlsetopt("http://jsso.indiatimes.com/sso/IndiatimesEmailLogin?ru=http://mb.indiatimes.com/public/login.jsp&nru=http://mb.indiatimes.com/public/login_redo.jsp&siteid=ebdccde5269865f726086aac8047fc06","login=".urlencode($email)."&passwd=".urlencode($password)."&Sign+in=Sign+In",1,0);
preg_match('/<INPUT TYPE\=hidden NAME\="login" VALUE\="([^"><]*)">/',$xreturn,$getlogin);

$mode = 0;
preg_match('/http\:\/\/emailprofile\.indiatimes\.com\/win\.aspx\?ru\=http\:\/\/([^\.]*)\.indiatimes\.com\/it\/login\.jsp\&/',@$curlstatus['url'],$getd);
if(@$getd[1])$xreturn=curlsetopt("http://".@$getd[1].".indiatimes.com/it/login.jsp",0,1,0);
if(!@$getd[1])preg_match('/http\:\/\/([^\.]*)\.indiatimes\.com\/mail\/h\/home/',@$curlstatus['url'],$getd);
if(!@$getd[1])preg_match('/http\:\/\/([^\.]*)\.indiatimes\.com\/h\/home/',@$curlstatus['url'],$getd);
if(@$getd[1]){
$mode = 1;
}elseif(!strncmp(@$curlstatus['url'],"http://infinite.indiatimes.com/cgi-bin/gateway",46)){
$mode = 2;
}

if ($mode != 0) {

if ($mode == 1) {
$xreturn=curlsetopt("http://".@$getd[1].".indiatimes.com/service/home/~/Contacts?auth=co&fmt=csv",0,1,0);
$getary=array("nickname","firstName","middlename","lastname","email");

}elseif($mode == 2) {
preg_match('/<INPUT TYPE=hidden NAME="login" VALUE="([^"]*)">/',$xreturn,$getlogin);
$xreturn=curlsetopt("http://infinite.indiatimes.com/cgi-bin/infinitemail.cgi/addressbook.csv?login=".urlencode(@$getlogin[1])."&command=addimpexp&button=Export+to+CSV+Format",0,1,0);
$getary=array("Nickname","First Name","Middle Name","Last Name","E-mail Address");
}

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
}
if(substr($record[1],$i,1)=="\""&&substr($record[1],$i,2)=="\"\""){$skip=1;}else{$skip=0;}

if($stat==0&&(substr($record[1],$i,1)==","||substr($record[1],$i,1)==";"||substr($record[1],$i,1)=="")){
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

$getnamea=@$contact[@$returnary[1]];
$getnameb=@$contact[@$returnary[2]];
$getnamec=@$contact[@$returnary[3]];

$email=@$contact[@$returnary[4]];

$getname=@$contact[@$returnary[0]];

if($getnamec||$getnamea||$getnameb){
$getname=null;
if($getnamec){$getname.=$getnamec;}
if($getnamea){if($getname==null){$getname.=$getnamea;}else{$getname.=", ".$getnamea;}}
if($getnameb){if($getname==null){$getname.=$getnameb;}else{$getname.=", ".$getnameb;}}
}

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
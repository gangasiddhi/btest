<?php
set_time_limit(0);
/*For Use By Xceog Default (Index.php). You may remove if index.php is not used*/
$setpicture="rediffmail.jpg";
$emaildomain="rediffmail.com";
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
if(!strpos($email,"@")){$email.="@rediffmail.com";}
$emailtmp=$email;

/*Get email domain*/
preg_match('/.*\@([^\@]*)/',$email,$getdomain);
$getdomain=strtolower(trim(@$getdomain[1]));

$xreturn=curlsetopt("http://mail.rediff.com/cgi-bin/login.cgi","login=".urlencode($email)."&passwd=".urlencode($password)."&FormName=existing",1,0);

$iris = 1;
preg_match('/(http\:\/\/f([0-9]+)plus\.rediff\.com\/[^"]*\?login\=([^&"]*)[^"]*&session_id\=([^&"]*)[^"]*)/',$xreturn,$getlink);

if(!@$getlink[1]){
preg_match('/(http\:\/\/f([0-9]+)mail\.rediff\.com\/[^"]*\?login\=([^&"]*)[^"]*&session_id\=([^&"]*)[^"]*)/',$xreturn,$getlink);
$iris = 0;
}

if(@$getlink[1]){
$xreturn=curlsetopt(@$getlink[1],0,1,0);
preg_match("!var strELSKey \= '([^']*)'\;!mis",$xreturn,$getels);


if($iris){
$xreturn=curlsetopt("http://f".@$getlink[2]."plus.rediff.com/prism/exportaddrbook?service=moutlook","els=".@$getels[1]."&exporttype=moutlook",1,0);
}else{
$xreturn=curlsetopt("http://f".@$getlink[2]."mail.rediff.com/prism/exportaddrbook?service=moutlook","els=".@$getels[1]."&exporttype=moutlook",1,0);
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

$getary=array("First Name", "Middle Name", "Last Name","E-mail Address");
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
if ($getname) $getname.=' ';
$getname.=@$contact[@$returnary[2]];

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
?>
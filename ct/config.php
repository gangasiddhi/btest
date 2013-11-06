<?php
/*
Copyright reserved (c) 2009 iPlussoft.com
Please refer to forum at http://www.iplussoft.com/help or
contact support@iplussoft.com for technical assistances.

!important*
Configuration files -> Importer - config.php, Mailer - form-script/configuration.php
======================================================================================================
*/

/*Set Cookie Path - please use full local server path !important - end the path with a slash*/
/*Please refer to phpinfo page for the correct path. E.g. cPanel user, path would be /home/username/tmp/ */
@ini_set('error_reporting', E_NONE);
@ini_set('display_errors', 'off');

@ini_set('memory_limit', '64M');
@ini_set('default_charset', 'utf-8');

$cookiepath="./tmp/";

/*Set Form POST action page !integrate to page?*/
$postpath="sendmail.php";

$currentDir = dirname(__FILE__);
define('_PS_ROOT_DIR_',             realpath($currentDir.'/..'));
$writelogs = 0;


/*Default !important*/
$importerinfo=Array();
$importerinfo[1]["Hotmail"]=array("p_hotmail","hotmaillib.php");
$importerinfo[1]["MSN"]=array("p_msn","msnlib.php");
$importerinfo[1]["AOL"]=array("p_aol","aollib.php");
$importerinfo[1]["Yahoo!"]=array("p_yahoo","yahoolib.php");
/*$importerinfo[1]["Gmail"]=array("p_gmail","gmaillib.php");
$importerinfo[1]["Lycos"]=array("p_lycos","lycoslib.php");
$importerinfo[1]["Mail.com"]=array("p_mail","maillib.php");
$importerinfo[1]["Rediffmail"]=array("p_rediffmail","rediffmaillib.php");
$importerinfo[1]["Indiatimes"]=array("p_indiatimes","indiatimeslib.php");
$importerinfo[1][".Mac"]=array("p_mac","maclib.php");
$importerinfo[1]["FastMail"]=array("p_fastmail","fastmaillib.php");
$importerinfo[1]["ICQ Mail"]=array("p_icq","icqlib.php");
$importerinfo[1]["ABV.BG"]=array("p_abv","abvlib.php");

$importerinfo[2]["Outlook CSV"]=array("p_outlook","outlooklib.php");
$importerinfo[2]["Thunderbird CSV"]=array("p_thunderbird","thunderbirdlib.php");
$importerinfo[2]["MSN Messenger CTT"]=array("p_msnmessenger","msnmessengerlib.php");*/
/*End Importer Filename Set*/


/*Check Existence of Cookie Path*/
if(!file_exists($cookiepath)){echo "<pre>Cannot find <b>".htmlspecialchars($cookiepath,ENT_QUOTES)."</b>. Please make sure it is valid!
The cookie path can be set in <b>config.php</b>.</pre>"; exit;}


/*
Remove the lines below if your server OS does not support chmod.
Remember to allow full permission for the script !important
-------------------------------------------------------------------------------------------
*/

/*Check Cookie Path Chmod*/
if(substr(sprintf('%o',@fileperms($cookiepath)),-3)!="777"){
echo "<pre>Chmod Directory: <b>".htmlspecialchars($cookiepath,ENT_QUOTES)."</b> is not set to 0777.
Poses a security risk. Cookie files will not be deleted after each session!</pre>";

/*Set Chmod Start*/
if(@chmod($cookiepath,0777)){echo "<pre>Chmod Directory: <b>".htmlspecialchars($cookiepath,ENT_QUOTES)."</b> is set to 0777 by the script. Refresh the page to take effect.
If this message still appears, please remove the line below in <b>config.php</b> and try again.</pre>";
}else{
echo "<pre>Chmod Directory: <b>".htmlspecialchars($cookiepath,ENT_QUOTES)."</b> failed to be set to 0777 by the script. Please set Chmod to 0777 manually.</pre>";
}
/*Set Chmod End*/

}

?>
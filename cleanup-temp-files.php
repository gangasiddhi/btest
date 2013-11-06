<?php
/*
* 2007-2011 PrestaShop 
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2011 PrestaShop SA
*  @version  Release: $Revision: 6599 $
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

include(dirname(__FILE__).'/config/config.inc.php');
include(dirname(__FILE__).'/init.php');

$cron_user = Tools::getValue('crnu');
$cron_pass = Tools::getValue('crnp');
if(!$cron_user)
	$cron_user = $argv[1];
if(!$cron_pass)
	$cron_pass = $argv[2];
$cron_pass = Tools::encrypt($cron_pass);

function recursiveRemoveDirectory($directory, $file_to_keep = '', $keep_directory = FALSE)
{
	// if the path has a slash at the end we remove it here
	if(substr($directory,-1) == '/')
	{
		$directory = substr($directory,0,-1);
	}

	// if the path is not valid or is not a directory ...
	if(!file_exists($directory) || !is_dir($directory))
	{
		// return false and exit the function
		return FALSE;

	// else if the path is not readable
	}elseif(!is_readable($directory))
	{
		// return false and exit the function
		return FALSE;

	// else if the path is readable
	}else{

		// open the directory
		$handle = opendir($directory);

		// scan through the items inside
		while (FALSE !== ($item = readdir($handle)))
		{
			// if the filepointer is not the current directory
			// or the parent directory
			if($item != '.' && $item != '..' && $item != $file_to_keep)
			{
				// build the new path to delete
				$path = $directory.'/'.$item;

				// if the new path is a directory
				if(is_dir($path)) 
				{
					// call this function with the new path (recursive)
					recursiveRemoveDirectory($path);

				// else if the new path is a file
				}else{
					// remove the file
					unlink($path);
				}
			}
		}
		// close the directory
		closedir($handle);

		// if the option to keep the directory is set to false
		if($keep_directory == FALSE)
		{
			// try to delete the now empty directory
			if(!rmdir($directory))
			{
				// return false if not possible
				return FALSE;
			}
		}
		// return success
		return TRUE;
	}
}

if($cron_user == _CRON_USER_ && $cron_pass == _CRON_PASSWD_)
{
	// recursively remove the temp dir in 'download', but keep the index.php file and the temp dir
	recursiveRemoveDirectory(dirname(__FILE__).'/download/temp', 'index.php', TRUE);
	echo "success";
}
else
{
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
	header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");

	header("Cache-Control: no-store, no-cache, must-revalidate");
	header("Cache-Control: post-check=0, pre-check=0", false);
	header("Pragma: no-cache");

	header("Location: ../");
	exit;
}

?>

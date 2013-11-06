{*
* 2007-2011 PrestaShop 
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
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
*  @version  Release: $Revision: 6594 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{*if IE 7}
	<link rel="stylesheet" type="text/css" href="ie7.css">
{/if*}
{if isset($errors) && $errors}
<div id="mainerrorblock">
	<div class="error message">
		<a id="error-close"></a>
		<div id="er">{l s='ERROR'}</div>
		<ol>
		{foreach from=$errors key=k item=error}
			<li>{$error}</li>
		{/foreach}
		</ol>
	</div>
{/if}
{if isset($info) && $info}
	<div class="info message">
		<a id="error-close"></a>
		<div id="inf">{l s='INFO'}</div>
		<ol>
		{foreach from=$info key=k item=info}
			<li>{$info}</li>
		{/foreach}
		</ol>
	</div>
{/if}
{if isset($warning) && $warning}
	<div class="warning message">
		<a id="error-close"></a>
		<div id="war">{l s='WARNING'}</div>
		<ol>
		{foreach from=$warning key=k item=warning}
			<li>{$warning}</li>
		{/foreach}
		</ol>
	</div>
{/if}
{if isset($success) && $success}
	<div class="success message">
		<a id="error-close"></a>
		<div id="success">{l s='success'}</div>
		<ol>
		{foreach from=$success key=k item=success}
			<li>{$success}</li>
		{/foreach}
		</ol>
	</div>
{/if}
</div>
		
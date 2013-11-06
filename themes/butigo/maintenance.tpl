<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="{$lang_iso}" lang="{$lang_iso}">
	<head>
		<title>{$meta_title|escape:'htmlall':'UTF-8'}</title>	
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
{if isset($meta_description)}
		<meta name="description" content="{$meta_description|escape:'htmlall':'UTF-8'}" />
{/if}
{if isset($meta_keywords)}
		<meta name="keywords" content="{$meta_keywords|escape:'htmlall':'UTF-8'}" />
{/if}
		<meta name="robots" content="{if isset($nobots)}no{/if}index,follow" />
		<link rel="shortcut icon" href="{$img_ps_dir}favicon.ico" />
		<link href="{$css_dir}maintenance.css" rel="stylesheet" type="text/css" />
<!--[if IE]>
		<link href="{$css_dir}ie.css" rel="stylesheet" type="text/css" />
<![endif]-->
		<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
<script type="text/javascript">
{literal}
$(function() {
	var theWindow        = $(window),
		$bg              = $("#bg"),
		aspectRatio      = $bg.width() / $bg.height();
	function resizeBg() {
		if ( (theWindow.width() / theWindow.height()) < aspectRatio ) {
			$bg.removeClass().addClass('bgheight');
		} else {
			$bg.removeClass().addClass('bgwidth');
		}
	}
	theWindow.resize(function() {
		resizeBg();
	}).trigger("resize");
});
{/literal}
</script>
	</head>

	<body>
		<div id="outer-wrapper">

			<img src="{$img_dir}maintenance-bg.jpg" id="bg" alt="" />

			<div id="inner-wrapper">
				<div id="container">
					<div id="logo">
						<h1>
							<a href="{$link->getPageLink('index.php')}" title="{$shop_name|escape:'htmlall':'UTF-8'}">{$shop_name|escape:'htmlall':'UTF-8'}</a>
						</h1>
					</div>

					<div id="invite" >
						<div id ="content" >
							<h2>{l s='Sistemlerimize iyileştirme yapmak için güncelleniyoruz'}</h2>
							<p>{l s='1 saat içinde tekrar yayında olacağız. Teşekkürler. Butigo Ekibi.'}</p>
						</div>
						{*<div id="ipad">
							<p>{l s='Unutmadan:İlk gün 10 kişi daha 1’er çift hediye ayakkabı kazanacak.'}</p>
						</div>*}
					</div>
				</div><!--container-->
			</div><!-- inner-wrapper -->

			<span style="clear:both;">&nbsp;</span>

		</div> <!-- outer-wrapper -->
		<span style="clear:both;">&nbsp;</span>
	</body>
</html>
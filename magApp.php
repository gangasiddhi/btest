<?php
require_once(dirname(__FILE__) . '/config/config.inc.php');

$base_css_path = _THEME_CSS_DIR_;
$base_js_path = _THEME_JS_DIR_;
$base_img_path = _THEME_IMG_DIR_;
$base_path = __PS_BASE_URI__;

$view = $_GET['view'];
$mag = $_GET['mag'];
if($mag == 1)
{
	for($i = 1; $i <= 23; $i++) {
		$bigImages[$i] = 'big/bm'.$i.'.jpg';
	}
	$count = 23;

	$thumbnails = array(
					"1" =>  'thumb/bm1.jpg',
					"2" =>  'thumb/bm2.jpg',
					"3" =>  'thumb/bm3.jpg',
					"4" =>	'thumb/bm4.jpg',
					"10" => 'thumb/bm10.jpg',
					"11" => 'thumb/bm11.jpg',
					"12" => 'thumb/bm12.jpg',
					"13" => 'thumb/bm13.jpg',
					"14" => 'thumb/bm14.jpg',
					"19" => 'thumb/bm19.jpg',
					"22" => 'thumb/bm22.jpg',
					"23" => 'thumb/bm23.jpg'
				);
				$issue = 'SAYI 1';
				$date = '19 Nisan 2013';
}
elseif($mag == 2){
	for($i = 1; $i <= 32; $i++) {
				$bigImages[$i] = 'big-new/bm'.$i.'.jpg';
			}
	$count = 32;

	$thumbnails = array(
				"1" =>  'thumb-new/1.jpg',
				"2" =>  'thumb-new/2.jpg',
				"3" =>  'thumb-new/3.jpg',
				"4" => 'thumb-new/4.jpg',
				"11" => 'thumb-new/5.jpg',
				"12" => 'thumb-new/6.jpg',
				"14" => 'thumb-new/7.jpg',
				"24" => 'thumb-new/8.jpg',
				"26" => 'thumb-new/9.jpg',
				"28" => 'thumb-new/10.jpg',
				"31" => 'thumb-new/11.jpg',
				"32" => 'thumb-new/12.jpg'
			);
			$issue = 'SAYI 2';
			$date = '31 Mayıs 2013';
}
elseif($mag == 3){
	for($i = 1; $i <= 29; $i++) {
				$bigImages[$i] = 'mag-big/bm'.$i.'.jpg';
			}
	$count = 29;

	$thumbnails = array(
				"1" =>  'mag-thumb/1.jpg',
				"2" =>  'mag-thumb/2.jpg',
				"4" =>  'mag-thumb/3.jpg',
				"10" => 'mag-thumb/4.jpg',
				"11" => 'mag-thumb/5.jpg',
				"12" => 'mag-thumb/6.jpg',
				"17" => 'mag-thumb/7.jpg',
				"20" => 'mag-thumb/8.jpg',
				"23" => 'mag-thumb/9.jpg',
				"27" => 'mag-thumb/10.jpg',
				"28" => 'mag-thumb/11.jpg',
				"29" => 'mag-thumb/12.jpg'
			);
			$issue = 'SAYI 3';
			$date = '22 Temmuz 2013';
}
else{
	for($i = 1; $i <= 31; $i++) {
				$bigImages[$i] = 'mag-big4/'.$i.'.jpg';
			}
	$count = 31;

	$thumbnails = array(
				"1" =>  'mag-thumb4/1.jpg',
				"2" =>  'mag-thumb4/2.jpg',
				"3" =>  'mag-thumb4/3.jpg',
				"4" =>	'mag-thumb4/4.jpg',
				"10" => 'mag-thumb4/5.jpg',
				"11" => 'mag-thumb4/6.jpg',
				"15" => 'mag-thumb4/7.jpg',
				"21" => 'mag-thumb4/8.jpg',
				"25" => 'mag-thumb4/9.jpg',
				"29" => 'mag-thumb4/10.jpg',
				"30" => 'mag-thumb4/11.jpg',
				"31" => 'mag-thumb4/12.jpg'
			);
			$issue = 'SAYI 4';
			$date = '31 Ekim 2013';
}


$archivethumbnails = array(
			"1" => 'cover-thumb/cover-thumb-1.jpg',
			"2" => 'cover-thumb/cover-thumb-2.jpg',
			"3" => 'cover-thumb/cover-thumb-3.jpg',
			"4" => 'cover-images/cover-4.jpg'
		);

echo '<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link rel="stylesheet" type="text/css" href="'.$base_css_path.'magApp.css">
		<script type="text/JavaScript" src="'._PS_JS_DIR_.'jquery/jquery-1.9.1.min.js"></script>
		<script type="text/JavaScript" src="'.$base_js_path.'jquery.bxslider.js"></script>
		<script type="text/JavaScript" src="'.$base_js_path.'magazine-new.js"></script>
	</head>
	<body>
	<div class="magazineContainer data-total-item-count="'.$count.'">
		<div class="slideContent1">
			<div class="header">
				<img src="'.$base_img_path.'magazine/butigo-mag_03.png" alt="Magazin"/>';
				if($view == 'archive'){
					echo '<span class="headerContent">ARŞİV</span>';
				}
				else{
					echo '<span class="headerContent">İÇERİK - <em>'.$date.'</em></span>
						<span class="headerMagazineId">'.$issue.'</span>';
				}
			echo '</div>';
			if($view == 'archive'){
				echo '<div class="archiveThumbnails">';
				foreach ($archivethumbnails as $key => $value)
				{
					echo '<div class="issueImage">
							<a href="'.$base_path.'magApp.php?mag='.$key.'">
							<img src="'.$base_img_path.'magazine/'.$value.'" alt="" data-index="'.$key.'">
							</a>
							<div class="imageText"> Sayı '.$key.'</div>
						</div>';
				}
				echo '</div>';
			}
			else{
				echo '<div class="thumbnails">';
				foreach ($thumbnails as $key => $value)
				{
					echo '<img src="'.$base_img_path.'magazine/'.$value.'" alt="" data-index="'.$key.'">';
				}
				echo '</div>';
		  }
		echo '</div>
		<div class="slideContent2">
			<div class="container">
				<div class="bxSliderItem" data-slide-width="781" data-min-slides="1" data-auto-slide="1" data-slide-pause="5000">';
				foreach ($bigImages as $key => $value)
				{
					echo '<div class="slide">
                        <figure class="loading">
                            <img src="'.$base_img_path.'magazine/'.$value.'" alt="">
                        </figure>
                    </div>';
				}
			echo '</div>
			</div>
		</div>
	  </div>
	  <div class="magazineFooter">
		<a href="#" class="galleryView" title="İÇERİK"></a>
		<div class="divider"></div>
		<a href="'.$base_path.'magApp.php?view=archive" class="archiveView" title="ARŞİV"></a>';
		if($view == 'archive'){
			echo '<div class="archiveFooter">ARŞİV</div>';
		}
		else{
			echo '<div class="pager">
				<a href="#" class="prev" title="Geri"></a>
				<span><em>1</em> / '.$count.'</span>
				<a href="#" class="next" title="ileri"></a>
			</div>
		  </div>';
		}
	echo '</body>
		</html>';
?>

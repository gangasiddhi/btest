{if isset($campaignImage) && $logged == 1 && $page_name != 'showroom' && $page_name != 'order' && $page_name != 'order-confirmation' && $page_name != 'stylesurvey' && $page_name != 'stylesurvey'}
	<script type="text/javascript">
		{literal}
			function trigger_disc_info(img_src, imgWidth, imgHeight, couponCode, couponCodeText, discountExpiry) {
				$.fancybox('<img src="'+img_src+'" alt=""/><div style="position: absolute;top: 80px;text-align:center;width:100%;"><p style="color:#5b5b5b;font-size:15px; padding: 0 0 10px 0;text-transform:uppercase">'+couponCodeText+':</p><p style="color:#F2018A;font-size:66px;font-weight:bold">'+couponCode+'</p><p style="color:#5b5b5b;font-size:15px; padding:35px 0 0 0;">'+discountExpiry+'</p></div>',
				{
					'autoSize' : false,
					'width' : imgWidth,
					'height' : imgHeight,
					'padding' : 0,
					'margin' : 0,
					'scrolling' : 'no',
					'titleShow' : false,
					'centerOnScroll' : true,
					'hideOnOverlayClick' : false,
					'hideOnContentClick' : true,
					'overlayColor' : '#000',
					'showNavArrows' : false,
					'onClosed'	: function (){
						setCookieWithPath("campaign", 1, 24*60*60, "/");
					}
				});
			}

			$(document).ready(function() {
				var c_client_val = getCookie("campaign");
				var couponCode = "{/literal}{$couponCode}{literal}"; 
				var couponCodeText = "{/literal}{$couponCodeText}{literal}";
				var discountExpiry = "{/literal}{$discountExpiry}{literal}";
				var imgWidth = 477, imgHeight = 241; /*Default image size*/
				var img_src = "{/literal}{$campaignImage}{literal}";
				var img = new Image();
				img.src = img_src;
				/* To get the image size on load*/
				img.onload = function () {
					imgWidth = this.width;
					imgHeight = this.height;
				}
				if (c_client_val== null || c_client_val== ""){
					setTimeout(function (){trigger_disc_info(img_src, imgWidth, imgHeight,couponCode, couponCodeText, discountExpiry)}, 2);
					setCookieWithPath("campaign", 1, 24*60*60, "/");
				}

			});
		{/literal}
	</script>
{/if}
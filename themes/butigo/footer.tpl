{if !$content_only}

</div>{* middle *}

</div>{* wrapper *}
</div>{* outer-wrapper *}
{if $page_name == 'ozgur-masur'}
	</div>
	</div>
{/if}
{if $page_name == 'authentication'}
	</div>
{/if}
</div>{* main-wrapper *}

{if $checkout_footer != 1}
<div id ="footer-wrapper">
	{$HOOK_FOOTER_TOP}

	<div id="footer">
		<div id="footer-inner">
			<div id="footer-top">
				{$HOOK_FOOTER}
			</div>
			<div id="questions">
				<img alt="{l s='phone'}" src="{$img_dir}phone.png"/>
				<p>{l s='Questions? Call 1.888.508.1888 Monday - Friday 8 a.m. - 5 p.m. (Pacific).'}</p>
			</div>
			<p id="copyright">
				{l s='* Within the contiguous United States. Excludes APO / FPO addresses. | '}&copy; {$copy_year} <a href="{if isset($HOOK_JOIN_NOW) && $HOOK_JOIN_NOW && !$logged} {$link->getPageLink('surveyvsregister.php')} {else} {$link->getPageLink('index.php')} {/if}">{l s='ShoeDazzle.com.'}</a> {l s='All rights reserved.'}
			</p>
		</div>
	</div>
</div>
{else}
<div id ="footer-wrapper-new">
	{$HOOK_FOOTER_TOP}

	<div id="footer">
		<div id="footer-inner-left">
            <span id="footer-left-heading">Butigo.com &#169; 2013</span><br />
            <span class="footer-left-sub-heading">{l s='HomePage'}</span><br />
            <span class="footer-left-sub-heading">{l s='My Cart'}</span>

            <a href="{$link7}" target="_blank" class="poilcy-link1-footer">{l s='Shipment and Return Policy'}</a>
            <a href="{$link8}" target="_blank" class="poilcy-link2-footer">{l s='Privacy Policy'}</a>
		</div>

		<div id="footer-inner-right">
            <span id="footer-right-heading">{l s='Contact Us'}</span>
            <span class="footer-right-sub-text">{l s='(216) 418 26 26'}</span>
            <span href="#" class="footer-right-sub-text">{l s='destek@butigo.com'}</span>
		</div>
	</div>
</div>
{/if}

{*a container for all footer javascripts*}
<div class="hidden">
{*Accessories banner popup*}
{*   {if $show_jand}
	   <script type="text/javascript">
		   {literal}
			   $(document).ready(function() {
			   setTimeout(
				   $.fancybox('{/literal}<a href="{$link->getPageLink('accessoriesed-products.php')}"><img src="{$img_dir}accessories_popup.jpg" alt=""/></a>{literal}',
				   {
					   'autoSize' : false,
					   'width' : 750,
					   'height' : 469,
					   'padding' : 1,
					   'margin' : 0,
					   'scrolling' : 'no',
					   'titlePosition' : 'over',
					   'titleShow' : false,
					   'centerOnScroll' : true,
					   'hideOnOverlayClick' : false,
					   'hideOnContentClick' : true,
					   'overlayColor' : '#000',
					   'showNavArrows' : false
				   }),1);
			   });
		   {/literal}
	   </script>
   {/if} *}

{$HOOK_FOOTER_EXTERNAL_SCRIPTS}

</div>
{/if}{*end if not content_only*}


</body>
</html>

{if $record}
	<div id="second-header">
		<h2>{l s='Record your testimonial'}</h2>
	</div>
	<div style="width:957px; margin:10px auto 0;">
		<iframe src="http://record.videogenie.com/media/?uid=ce1b52dd-553e-4539-9149-9652b085245f"
				border="0" frameborder="0" scrolling="no" allowtransparency="true"
				style="height:550px; width:960px"></iframe>
	</div>
{else}
	<div id="second-header">
		<h2>{l s='Testimonials'}</h2>
	</div>
    <div class="testimonial-container">
		<div id="vgenie-video">
			<div id="play-video">
				<iframe src="http://watch.videogenie.com/media/embeddedCompilation.do?uid=42725519-4bd6-479d-bdec-205e67ab640b&amp;logo=small&amp;width=440"
						frameborder="0" allowtransparency="true" scrolling="no"
						style="height:380px; width:440px; overflow:hidden; margin:0;"></iframe>
			</div>
			<a href="{$link->getPageLink('testimonials.php')}?record=1" title="{l s='Record video'}"><img src="{$img_dir}testimonial/vgenie-video-footer.gif" alt="{l s='Record video'}"/></a>
		</div>
		<div id="vgenie-img">
                    <img src="{$img_dir}testimonial/vgenie-video-header.gif" alt="{l s='video details'}"/>
                     {if !$logged}
                        <div id="get_profile">
                            {if isset($HOOK_JOIN_NOW) && $HOOK_JOIN_NOW}
                                    {$HOOK_JOIN_NOW}
                            {else} 
                                <a href="{$link->getPageLink('stylesurvey.php')}" class = "buttonlarge blue" title="{l s='Get Style Profile'}">
                                    {*<img src="{$img_dir}buttons/vgenie-get-profile-btn.png" alt="{l s='Get Style Profile'}"/>*}{l s='Get Style Profile'}
                                </a>
                            {/if}
                        </div>
                     {/if}
		</div>
	</div>
{/if}

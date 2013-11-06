{assign var="totalItemCount" value=$bigImages|@count}
{assign var="magazineBaseUrl" value=$img_dir|cat:"/magazine/"}

<div class="magazineContainer" data-total-item-count="{$totalItemCount}">
        <div class="slideContent1">
            <div class="header">
                <img src="http://img-butigo.mncdn.com/themes/butigo/css/../img/magazine/butigo-mag_03.png" alt="Magazin"/>
				{if $archiveView == 'archive'}
					<span class="headerContent">ARŞİV</span>
				{else}
					<span class="headerContent">İÇERİK - <em>{$date}</em></span>
					<span class="headerMagazineId">{$issue}</span>
				{/if}
            </div>

			{if $archiveView == 'archive'}
				<div class="archiveThumbnails">
					{foreach from=$archivethumbnails item=imageUrl key=k}
						<div class="issueImage">
							<img src="{$magazineBaseUrl}{$imageUrl}" alt="" data-index="{$k}">
							<div class="imageText"> Sayı {$k}</div>
						</div>
					{/foreach}
				</div>
			{else}
				 <div class="thumbnails">
					{foreach from=$thumbnails item=imageUrl key=k}
						<img src="{$magazineBaseUrl}{$imageUrl}" alt="" data-index="{$k}">
					{/foreach}
				</div>
			{/if}

        </div> <!-- .slideContent1 -->


        <div class="slideContent2">
            <div class="container">
                <div class="bxSliderItem" data-slide-width="781" data-min-slides="1" data-auto-slide="1" data-slide-pause="5000">
                {foreach from=$bigImages item=imageUrl key=k}
                    <div class="slide">
                        <figure class="loading">
                            <img src="{$magazineBaseUrl}{$imageUrl}" alt="">
                        </figure>
                    </div>
                {/foreach}
                </div>
            </div> <!-- container -->
        </div> <!-- .slideContent2 -->
</div> <!-- .magazineContainer -->

<div class="magazineFooter">
    <a href="#" class="galleryView" title="İÇERİK"></a>
	<div class="divider"></div>
	<a href="#" class="archiveView" title="ARŞİV"></a>

	{if $archiveView == 'archive'}
		<div class="archiveFooter">ARŞİV</div>
	{else}
		<div class="pager">
			<a href="#" class="prev" title="Geri"></a>
			<span><em>1</em> / {$totalItemCount}</span>
			<a href="#" class="next" title="ileri"></a>
		</div>
	{/if}
</div>

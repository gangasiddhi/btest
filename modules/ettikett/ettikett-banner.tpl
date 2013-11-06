{* Content to be displayed upon Sharing (Shared) *}

{if $etkt_shared}
	<div id="ettikett-share">
		<div class="ettikett-shared-heading">
				<p class="title">{l s='CONGRATULATIONS' mod='ettikett'}!</p>
				<p>{l s='By sharing this order' mod='ettikett'}</p>
			</p>
		</div>

		<div class="ettikett-discount-value">
			<p class="amount">{convertPrice price=$discount}</p>
			<p>{l s='is won' mod='ettikett'}!</p>
		</div>

		<div class="ettikett-shared-footer">
			<p>
				<span>{l s='Discount will be added automatically on your next checkout.' mod='ettikett'}</span>
			</p>
			<p>
				<a href="{$link->getPageLink('lookbook.php')}"><img src="{$module_dir}assets/button.gif" alt="{l s='Start Shopping' mod='ettikett'}"></a>
			</p>
		</div>
	</div>
{/if}

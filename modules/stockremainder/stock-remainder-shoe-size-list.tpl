<script type="text/javascript">
	var confirmationMessage = "{l s='Please Confirm the size selected' mod='stockremainder' js=1}";
	var alreadySelectedMessage = "{l s='Already Selected' mod='stockremainder' js=1}";
</script>
<span class="ajax-js" jsfile="{$path}js/stock-remainder.js"></span>
<div id="stock-remainder-size-close"><img src="{$path}img/stock-remainder-close.png" alt="{l s='close'}" title="{l s='close'}" /></div>
{if isset($groups)}
	{foreach from=$groups key=id_attribute_group item=group}
		{if $id_attribute_group != 2 && $group.attributes|@count}
		<div class="stock-remainder-choices-group" id="stock-remainder-choices-group-{$id_attribute_group|intval}">
			<label>{l s='Choose Sizes' mod='stockremainder'}{*$group.name|escape:'htmlall':'UTF-8'*}</label>
			{assign var='groupName' value='group_'|cat:$id_attribute_group}
			<ul class="stock-remainder-choices">
				{foreach from=$group.attributes key=id_attribute item=group_attribute}
						{*<li class ="{if $group_attribute|in_array:$attr_q}no_stock{else}stock{/if}{if $customerShoeSizeDefault == $group_attribute} picked{/if}">
							{if $group_attribute|in_array:$attr_q}
										{$group_attribute}
							{else}*}
								{if $url_product_attribute>0 }{* && $color_shoe_combination == 1*}
										{foreach from=$color_combination key=id_color item=color_comb}
										{foreach from=$color_comb.shoesize key=id_size item=shoesize}
											{if $id_color==$id_attribute_color && $id_attribute==$id_size }
												<li class ="{if $shoesize.quantity>0}stock{else}no_stock{/if}{if $customerShoeSizeDefault == $group_attribute && $shoesize.quantity>0} picked{/if}">
													{if $shoesize.quantity > 0}
														<a id="stock-remainder-choice-{$id_attribute|intval}" class="stock-remainder-choice" data-id-attribute="{$id_attribute|intval}" data-id-attribute-group="{$id_attribute_group|intval}" {*onclick="updateProductRecommendChoiceSelect(this,{$id_attribute|intval}, {$id_attribute_group|intval});"*} title="{$group_attribute|escape:'htmlall':'UTF-8'}">
															{$group_attribute|escape:'htmlall':'UTF-8'}
														</a>
													{else}
														{$group_attribute|escape:'htmlall':'UTF-8'}
													{/if}
												</li>
											{/if}
										{/foreach}
										{/foreach}
								{else}
									{foreach from=$combinations key=id_product_attribute item=productAttribute}
										{foreach from=$productAttribute.attributes_values item=productAttributeValues}
											{if $productAttributeValues == $group_attribute}
												{assign var=ipa value=$id_product_attribute}
											{/if}
										{/foreach}
									{/foreach}
									{assign var=customerAlreadySelected value=0}
									{foreach from=$customerSelectedSizesForAlarm item=customerStockAlaram}										
										{if $customerStockAlaram == $ipa}
											{assign var=customerAlreadySelected value=1}										
										{/if}
									{/foreach}
									<li class ="{if $group_attribute|in_array:$attr_q}stock{else}no_stock{/if}{if $customerShoeSizeDefault == $group_attribute || $customerAlreadySelected == 1} picked{/if}">
										{if $group_attribute|in_array:$attr_q}
											<a id="stock-remainder-choice-{$id_attribute|intval}" class="stock-remainder-choice" data-id-attribute="{$id_attribute|intval}" data-id-attribute-group="{$id_attribute_group|intval}" onclick="updateStockRemainderSizeSelect(this,{$productId},{$ipa},{$id_attribute|intval}, {$id_attribute_group|intval}, {$group_attribute|escape:'htmlall':'UTF-8'});" title="{$group_attribute|escape:'htmlall':'UTF-8'}">
												{$group_attribute|escape:'htmlall':'UTF-8'}
											</a>											
										{else}
											{$group_attribute|escape:'htmlall':'UTF-8'}
										{/if}
									</li>
								{/if}
							{*{/if}
						</li>*}
				{/foreach}
			</ul>
			<label class="shoe-size-recorded hidden">{l s='Size Recorded' mod='stockremainder'}</label>
		</div>
		{/if}
	{/foreach}
{/if}
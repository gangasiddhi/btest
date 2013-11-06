{*<div class="product-recommend-color-size-container"> *} 
{*<img class="product-recommend-size-close" src="{$themePath}img/product/product-recommend-close.png"/>*}
<span class="ajax-js" jsfile="{$path}js/product-recommend-size.js"></span>
{if isset($groups)}
	{foreach from=$groups key=id_attribute_group item=group}
		{if $id_attribute_group != 2 && $group.attributes|@count}
		<div class="product-recommend-size-close"><img src="{$themePath}img/product/product-recommend-close.png" alt="" data-product-id="{$productId}"/></div>
		<div class="product-recommend-choices-group" id="product-recommend-choices-group-{$id_attribute_group|intval}">
			<label>{$group.name|escape:'htmlall':'UTF-8'}</label>
			{assign var='groupName' value='group_'|cat:$id_attribute_group}
			<ul class="product-recommend-choices">
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
														<a id="product-recommend-choice-{$id_attribute|intval}" class="product-recommend-choice" data-id-attribute="{$id_attribute|intval}" data-id-attribute-group="{$id_attribute_group|intval}" {*onclick="updateProductRecommendChoiceSelect(this,{$id_attribute|intval}, {$id_attribute_group|intval});"*} title="{$group_attribute|escape:'htmlall':'UTF-8'}">
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
									<li class ="{if $group_attribute|in_array:$attr_q}no_stock{else}stock{/if}{if $customerShoeSizeDefault == $group_attribute} picked{/if}">
										{if $group_attribute|in_array:$attr_q}
											{$group_attribute|escape:'htmlall':'UTF-8'}
										{else}
										<a id="product-recommend-choice-{$id_attribute|intval}" class="product-recommend-choice" data-id-attribute="{$id_attribute|intval}" data-id-attribute-group="{$id_attribute_group|intval}" onclick="updateProductRecommendChoiceSelect(this,{$productId},{$ipa},{$id_attribute|intval}, {$id_attribute_group|intval});" title="{$group_attribute|escape:'htmlall':'UTF-8'}">
											{$group_attribute|escape:'htmlall':'UTF-8'}
										</a>
										{/if}
									</li>
								{/if}
							{*{/if}
						</li>*}
				{/foreach}
			</ul>
		</div>
		{/if}
	{/foreach}
{/if}
{*</div>*}
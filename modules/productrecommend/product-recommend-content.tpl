<script type="text/javascript">
	//<![CDATA[
	var moduleDir = '{$path}';
	var langId = {$langId};
	var productId = '{$product->id}';
	var productRecommendCompleteArray = [];
	productRecommendCompleteArray['productId'] = '{$product->id}';
	{if $previousRecommendations->recommend == 1}
		productRecommendCompleteArray['recommend'] = 1;
	{else}
		productRecommendCompleteArray['recommend'] = 0;
	{/if}
	
	{if $previousRecommendations->recommend_type == 'category'}
		productRecommendCompleteArray['recommendType'] = "category";
	{else if $previousRecommendations->recommend_type == 'products'}
		productRecommendCompleteArray['recommendTyp'] = "category";
	{/if}
	
	{if $previousRecommendations->category_id}
		productRecommendCompleteArray['categoryId'] = '{$previousRecommendations->category_id}';
	{/if}
		
	{if $previousProductRecommendList}
		productRecommendCompleteArray['productIdList'] = '{$recommandProductListString}';
	{else}
		productRecommendCompleteArray['productIdList'] = '';
	{/if}
		//]]>
</script>
<link rel="stylesheet" type="text/css" href="{$baseUri}css/jquery.autocomplete.css" />
<link rel="stylesheet" type="text/css" href="{$path}css/product-recommend.css" />
<script type="text/javascript" src="{$baseUri}js/jquery/jquery.autocomplete.js"></script>
<script type="text/javascript" src="{$path}js/product-recommend.js"></script>
<div class="hidden">
	<span class="ajax-js" jsfile="{$path}js/product-recommend-table-row-move.js"></span>
</div>

{*Heading*}
<table cellpadding="5">
	<tbody>
		<tr>
			<td colspan="2">
				<b>{l s='Recommend the Products along with this product'}</b></br></br>				
			</td>
		</tr>
	</tbody>
</table>

<hr style="width:100%;"><br>
{*ReommendActive & Recommend Type*}
<table cellpadding="5">
	<tbody>
		<tr>
			<td class="col-left"><label>{l s='Recommend'}</label></td>
			<td><input type="radio" name="recommendActive" value="1" {if $previousRecommendations->recommend == 1}checked="checked"{/if}/>{l s='Enable'}</td>
			<td><input type="radio" name="recommendActive" value="0" {if $previousRecommendations->recommend == 0}checked="checked"{/if}/>{l s='disable'}</td>
		</tr>
		<tr>
			<td class="col-left"><label>{l s='Recommend Type'}</label></td>
			<td><input type="radio" name="recommendType" value="category" {if $previousRecommendations->recommend_type == 'category'}checked="checked"{/if}/>&nbsp;{l s='Category'}&nbsp;&nbsp;</td>
			<td><input type="radio" name="recommendType" value="products" {if $previousRecommendations->recommend_type == 'products'}checked="checked"{/if}/>&nbsp;{l s='Products'}</td>
		</tr>
	</tbody>
</table>	

{*Categories List*}
<div id="recommend-category-container" class="hidden">
	<label>{l s='Categories'}</label>
	<table>
		<tbody>
			<tr id="tr_categories">
				<td colspan="2">
					{$categoryTree}
				</td>
			</tr>
		</tbody>
	</table>

	<div>
		{*<p>
		<select name="recommendCategory" id="recommend-category">
		<option value="">{l s='Select the category'}</option>
		{foreach from=$categories item=subCategory name=subCat}			
		{foreach from=$subCategory item=category name=cat}				
		<option {if $previousRecommendations->category_id == $category.infos.id_category}selected="selected"{/if} value="{$category.infos.id_category}">{$category.infos.name}</option>					
		{/foreach}			
		{/foreach}
		</select>
		</p>*}
		<label>{l s='Products in the Selected Category' mod='productrecommend'}</label>
		<p>
			<select multiple id="select" style="width:300px;height:160px;">								
			</select><br><br>  
		</p>
		<p style="margin:0 0 10px 200px;">
			<span id="add-category-recommend-product" class="button" style="padding:5px;margin:0 10px">
				{l s='Add' mod='productrecommend'}
			</span>
			<span id="add-all-category-recommend-product" class="button" style="padding:5px;margin:0 10px">
				{l s='Select All' mod='productrecommend'}
			</span>
			<span id="referesh-category-recommend-product-list" class="button" style="padding:5px;margin:0 10px">
				{l s='Referesh' mod='productrecommend'}
			</span>
		</p>
	</div>
</div>

{*Products List*}
<div id="recommend-products-container" class="hidden">	
	<label>{l s='Products'}</label>
	<div id="ajax_choose_product">	
		<p>
			<input type="text" id="recommand_product_autocomplete_input" value="" autocomplete="on" class="ac_input">
			{*<a href="#" id="add-product-recommend-product"><img title="Add an accessory" alt="Add an accessory" src="../img/admin/add.gif"></a>*}
			{l s='Begin typing the first letters of the product name, then select the product from the drop-down list'}
		</p>
	</div>
</div>

{*Recommend Product List*}
<div>
	<table id="final-recommend-product-list" class="table" style="width:100%;">
		<caption><label>{l s='Recommended Product List'}</label></caption>
		<thead><th>{l s='Products'}</th style="width:50px;text-align:center"><th>{l s='position'}</th><th>{l s='Delete'}</th></thead>
	<tbody>
		<tr style="display:none">
			<td>
				<p>
					<span class="move down-arrow"  direction="down"></span>
					<span class="move up-arrow" direction="up"></span>
				</p>
			</td>			
		</tr>
		{if $previousProductRecommendList}
			{foreach from=$previousProductRecommendList item=productIdList name=recommendList}
				<tr id='position-{$smarty.foreach.recommendList.index}' value="{$productIdList.id_product}_{$productIdList.id_product_attribute}_{$productIdList.name}_{$productIdList.position}">
					<td>{$productIdList.name}</td>
					<td>
						{*if $smarty.foreach.recommendList.first}
							<p>
								<span class="move down-arrow"  direction="down"></span>
								<span class="move" direction="up"></span>
							</p>
						{elseif $smarty.foreach.recommendList.last}
							<p>
								<span class="move" direction="down"></span>
								<span class="move up-arrow"  direction="up"></span>
							</p>
						{else*}
							<p>
								<span class="move down-arrow"  direction="down"></span>
								<span class="move up-arrow" direction="up"></span>
							</p>										
						{*/if*}						
					</td>
					<td style="width:50px;text-align:center">
						<input class ="recommend-product-value" value="{$productIdList.id_product}_{$productIdList.id_product_attribute}_{$productIdList.name}_{$productIdList.position}" style="cursor:pointer;background-image:url('../img/admin/delete.gif');border:none;width:16px;height:16px;background-color:#FFFFF0;text-indent: -9999px" onClick="deleteProduct(this);return false;" />
					</td>
				</tr>
			{/foreach}
		{/if}
	</tbody>
	</table>
</div>
<p style="margin:10px 200px;">
	<span id="final-recommend-products-list-save" class="button" style="padding:5px;">
		{l s='Save' mod='productrecommend'}
	</span>
</p>
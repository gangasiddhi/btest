{*<script type="text/javascript">
//<![CDATA[
	{literal}
		$(document).ready(function() {
			$('.favorite_flag').hover(
				function () {
				 $("#fav_text").show();
			},
				function () {
				$("#fav_text").hide();
			 }
		 );
		});
	{/literal}
//]]>
</script>*}
<div id="fav">
	<input type="hidden" name="qty" id="quantity_wanted" value="1"/>
	{if $in_myfavorite}
		<a href="javascript:;" id="faved"  class="favorite_flag in_myfavorite"  onclick="FavProductRemove('single_product', 'delete', '{$id_product|intval}',{$id_product_attribute}, true)">
			<span id="fav_text">{l s='Remove From Favourites' mod='blockfavourites'}</span>
		</a>
	{else}
		{if $id_product_attribute}
			<a href="#" id="to_fav" class="favorite_flag"  onclick="FavlistCart('single_product', 'add', '{$id_product|intval}', {*if $('#idCombination').val()} {else*} {$id_product_attribute}{*/if*}, document.getElementById('quantity_wanted').value, true); return false;" onmouseover = "$('#fav_text').show(); return false" onmouseout = "$('#fav_text').hide(); return false">
				{*<span id="fav_text">{l s='Add to Favourites' mod='blockfavourites'}</span>*}
				<span  id="fav_text">{l s='Add to' mod='blockfavourites'}<br>{l s='Favourites' mod='blockfavourites'}<img width="5" height="10" src="{$img_dir}buttons/pinkarrow.png" class="arrow" alt="Pinkarrow"></span>
			</a>
		{else}
			<a href="#" id="to_fav" class="favorite_flag"  onclick="FavlistCart('single_product', 'add', '{$id_product|intval}', $('#idCombination').val(), document.getElementById('quantity_wanted').value, true); return false;" onmouseover = "$('#fav_text').show(); return false" onmouseout = "$('#fav_text').hide(); return false">
				{*<span id="fav_text">{l s='Add to Favourites' mod='blockfavourites'}</span>*}
				<span  id="fav_text">{l s='Add to' mod='blockfavourites'}<br>{l s='Favourites' mod='blockfavourites'}<img width="5" height="10" src="{$img_dir}buttons/pinkarrow.png" class="arrow" alt="Pinkarrow"></span>
			</a>
		{/if}
	{/if}
	<div id="ajax_response"></div>
</div>
<script type="text/javascript">
/*<![CDATA[*/
	var baseDir = '{$base_dir_ssl}';
/*]]>*/
</script>

{*include file="$tpl_dir./errors.tpl"*}

<div id = "container">
	<h3>{l s='Order history'}</h3>
	{if $slowValidation}
		<p class="warning">
			{l s='If you have just placed an order, it may take a few minutes for it to be validated. Please refresh the page if your order is missing.'}
		</p>
	{/if}
	<div class="block-center" id="block-history">
		{if $orders && count($orders)}
		<table id="order-list" class="history_list">
			<thead>
				<tr>
					<th class="first_item">{l s='Order Date'}</th>
					<th class="item">{l s='Order'} &#35;</th>
					<th class="item">{l s='Total price'}</th>
					{*<th class="item">{l s='Items'}</th>
					<th class="item">{l s='Color'}</th>
					<th class="item">{l s='Payment'}</th>*}
					<th class="item">{l s='Status'}</th>
					{*<th class="item">{l s='Invoice'}</th>*}
					<th class="last_item">&nbsp;&nbsp;</th>
				</tr>
			</thead>
			<tbody>
			{foreach from=$orders item=order name=myLoop}
				<tr class="{if $smarty.foreach.myLoop.first}first_item{elseif $smarty.foreach.myLoop.last}last_item{else}item{/if} {if $smarty.foreach.myLoop.index % 2}alternate_item{/if}">
					<td class="history_date bold">{dateFormat date=$order.date_add full=0}</td>
					<td class="history_link bold">
						{if isset($order.invoice) && $order.invoice && isset($order.virtual) && $order.virtual}<img src="{$img_dir}icon/download_product.gif" class="icon" alt="{l s='Products to download'}" title="{l s='Products to download'}" />{/if}
						<a class="color-myaccount" href="javascript:showOrder(1, {$order.id_order|intval}, 'order-detail');">{l s='#'}{$order.id_order|string_format:"%06d"}</a>
					</td>
					<td class="history_price"><span class="price">{displayPrice price=$order.total_paid_real currency=$order.id_currency no_utf8=false convert=false}</span></td>
					{*<td class="history_method">{$order.payment|escape:'htmlall':'UTF-8'}</td>*}
					<td class="history_state">{if isset($order.order_state)}{$order.order_state|escape:'htmlall':'UTF-8'}{/if}</td>
					{*<td class="history_invoice">
					{if (isset($order.invoice) && $order.invoice && isset($order.invoice_number) && $order.invoice_number) && isset($invoiceAllowed) && $invoiceAllowed == true}
						<a href="{$link->getPageLink('pdf-invoice.php', true)}?id_order={$order.id_order|intval}" title="{l s='Invoice'}"><img src="{$img_dir}icon/pdf.gif" alt="{l s='Invoice'}" class="icon" /></a>
						<a href="{$link->getPageLink('pdf-invoice.php', true)}?id_order={$order.id_order|intval}" title="{l s='Invoice'}">{l s='PDF'}</a>
					{else}-{/if}
					</td>*}
					<td class="history_detail">
						<a class="color-myaccount" href="javascript:showOrder(1, {$order.id_order|intval}, 'order-detail');">{l s='details'}</a>
						{*<a href="{if isset($opc) && $opc}{$link->getPageLink('order-opc.php', true)}{else}{$link->getPageLink('order.php', true)}{/if}?submitReorder&id_order={$order.id_order|intval}" title="{l s='Reorder'}">
							<img src="{$img_dir}arrow_rotate_anticlockwise.png" alt="{l s='Reorder'}" title="{l s='Reorder'}" class="icon" />
						</a>*}
					</td>
				</tr>
			{/foreach}
			</tbody>
		</table>
		{if $paginationParams.totalItem > $paginationParams.itemPerPage}
            <div id="history-pagination" class="pagination butigo-pagination pink-pagination"></div>
        {/if}
		<div id="block-order-detail">&nbsp;</div>
		{else}
			<p class="warning">{l s='You have not placed any orders.'}</p>
		{/if}
	</div>
</div>
<script type="text/javascript">
{if $paginationParams.totalItem > $paginationParams.itemPerPage}
    {literal}
        $(function(){
            var paginationCallbacks = function(pageno, $pagination) {
                if (pageno == {/literal}{$paginationParams.pageNo-1}{literal}) return;
                pageno = parseInt(pageno) + 1;
                goToUrl(location.pathname+'?pageno='+ pageno );
            }

            $("#history-pagination").pagination({/literal}{$paginationParams.totalItem}{literal}, {
                callback: paginationCallbacks
                ,prev_text:'{/literal}{l s="Prev"}{literal}'
                ,next_text:'{/literal}{l s="Next"}{literal}'
                ,items_per_page:{/literal}{$paginationParams.itemPerPage}{literal}
                ,num_display_entries:12
                ,num_edge_entries:2
                ,current_page : {/literal}{$paginationParams.pageNo-1}{literal}
            });
        });
    {/literal}
{/if}
</script>


{include file="$tpl_dir./my-account-sidebar.tpl"}

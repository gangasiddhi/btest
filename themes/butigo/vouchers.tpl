<script type="text/javascript">
    var baseDir = '{$base_dir_ssl}';
</script>

<div class="customerVouchersPageContainer">
    <h3>{l s='My Vouchers'}</h3>

    {if $vouchers && count($vouchers)}
        <table class="customerVouchersTable">
            <tr>
                <th class="name">{l s='Name'}</th>
                <th class="value">{l s='Value'}</th>
                <th class="description">{l s='Description'}</th>
                <th class="cumulable">{l s='Cumulable'}</th>
                <th class="cumulable_reduction">{l s='Cumulable with Discounted Products'}</th>
                <th class="valid">{l s='Valid Until'}</th>
                <th class="date_to">{l s='Status'}</th>
            </tr>

            {foreach from=$vouchers item=voucher name=vl}
                {if $smarty.foreach.vl.index % 2 == 1}
                    {assign var="class" value="even"}
                {else}
                    {assign var="class" value="odd"}
                {/if}

                {if $voucher.quantity_for_user <= 0}
                    {assign var="class" value="spent"}
                {/if}

                <tr class="{$class}">
                    <td class="name">{$voucher.name}</td>
                    <td class="value">
                        {$voucher.value}

                        {if $voucher.id_discount_type == 1}
                            %
                        {else}
                            TL
                        {/if}
                    </td>
                    <td class="description">{$voucher.description}</td>
                    <td class="cumulable">
                        {if $voucher.cumulable}
                            <img src="{$img_dir}icon/yes.gif">
                        {else}
                            <img src="{$img_dir}icon/no.gif">
                        {/if}
                    </td>
                    <td class="cumulable_reduction">
                        {if $voucher.cumulable_reduction}
                            <img src="{$img_dir}icon/yes.gif">
                        {else}
                            <img src="{$img_dir}icon/no.gif">
                        {/if}
                    </td>
                    <td class="date_to">{$voucher.date_to}</td>
                    <td class="active">
                        {if $voucher.quantity_for_user > 0}
                            {if $voucher.active}
                                <img src="{$img_dir}icon/yes.gif">
                            {else}
                                <img src="{$img_dir}icon/no.gif">
                            {/if}
                        {else}
                            <img src="{$img_dir}icon/no.gif">
                        {/if}
                    </td>
                </tr>
            {/foreach}
        </table>
    {else}
        <p class="warning">{l s='Unfortunetely you do not have any vouchers.'}</p>
    {/if}
    {if $paginationParams.totalItem > $paginationParams.itemPerPage}
        <div id="voucher-pagination" class="pagination butigo-pagination pink-pagination"></div>
    {/if}
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

            $("#voucher-pagination").pagination({/literal}{$paginationParams.totalItem}{literal}, {
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

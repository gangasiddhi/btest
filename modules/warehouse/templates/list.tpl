{if isset($css)}
    {foreach from=$css item=uri}
        <link href="{$uri}" rel="stylesheet" type="text/css" media="all" />
    {/foreach}
{/if}

<script>
    var currentUrl = "{$currentIndex}&token={$token}";
    var numberOfPurchases = {$numberOfPurchases};
    var prevText = "{l s='Prev'}";
    var nextText = "{l s='Next'}";
    var itemPerPage = {$itemPerPage};
    var page = {$page};
    var referencePlaceholder = "{l s='Product Reference'}";
    var quantityPlaceholder = "{l s='Quantity'}";
    var errProductFields = "{l s='Please fill all fields regarding product reference and quantity!'}";
</script>

{if isset($js)}
    {foreach from=$js item=uri}
        <script type="text/javascript" src="{$uri}"></script>
    {/foreach}
{/if}

<div class="container">
    <div class="top">
        <a href="#" class="add-new-supplier-purchase">
            <img src="../img/admin/add.gif" border="0" />
            {l s='Add New'}
        </a>
    </div>

    <div id="supplier-purchase-container" class="alignRight{if ! $editablePurchase} hidden{/if}">
        <form id="supplier-purchase-form" method="POST" action="{$currentIndex}&token={$token}{if $editablePurchase}&editPurchase={$editablePurchase.0.id_purchase}{/if}">
            <input type="hidden" name="data" id="supplier-purchase-form-data">

            <div class="supplier-purchase-form-row first">
                <label>{l s='Supplier'}</label>
                <select name="supplier" class="supplier-select-box">
                    {foreach item=s from=$suppliers}
                        <option value="{$s.id_supplier}"{if $editablePurchase.0.id_supplier == $s.id_supplier} selected{/if}>{$s.name}</option>
                    {/foreach}
                </select>
            </div>

            <div class="supplier-purchase-form-row fields">
                <label>{l s='Products'}</label>

                {foreach item=e from=$editablePurchase name=ep}
                    <div class="supplier-purchase-form-inner-row{if $smarty.foreach.ep.first} first{/if}">
                        <input class="product-reference" type="text" placeholder="{l s='Product Reference'}" value="{$e.reference}">
                        -
                        <input class="product-quantity" type="text" placeholder="{l s='Quantity'}" value="{$e.quantity}">

                        <a href="#" class="supplier-purchase-form-add-new-inner-row">
                            <img src="../img/admin/add.gif">
                        </a>

                        <a href="#" class="supplier-purchase-form-delete-inner-row">
                            <img src="../img/admin/forbbiden.gif">
                        </a>
                    </div>
                {foreachelse}
                    <div class="supplier-purchase-form-inner-row first">
                        <input class="product-reference" type="text" placeholder="{l s='Product Reference'}">
                        -
                        <input class="product-quantity" type="text" placeholder="{l s='Quantity'}">

                        <a href="#" class="supplier-purchase-form-add-new-inner-row">
                            <img src="../img/admin/add.gif">
                        </a>
                    </div>
                {/foreach}
            </div>

            <div class="supplier-purchase-form-row right last">
                <input type="submit" class="button" name="submitPurchase" value="{l s='Submit'}">
            </div>
        </form>
    </div>

    <div id="supplier-purchases" class="alignLeft">
        <h3>{l s='Supplier Purchases'}</h3>

        <table class="table" cellpadding="0" cellspacing="0">
            <thead>
                <tr class="nodrag nodrop">
                    <th>{l s='Purchase'}</th>
                    <th>{l s='Warehouse'}</th>
                    <th>{l s='Supplier'}</th>
                    <th>{l s='Qty'}</th>
                    <th>{l s='Success'}</th>
                    <th>{l s='Fail'}</th>
                    <th>{l s='Status'}</th>
                    <th>{l s='Date'}</th>
                    <th>{l s='Actions'}</th>
                </tr>
            </thead>
            <tbody>
                {foreach item=p from=$purchases}
                    <tr>
                        <td class="center">{$p.id_purchase}</td>
                        <td>{$p.id_warehouse}</td>
                        <td>{$p.supplier}</td>
                        <td class="center">{$p.poQuantity}</td>
                        <td class="center">{$p.success}</td>
                        <td class="center">{$p.fail}</td>
                        <td class="center">
                            {if $p.id_status }
                                <img src="../img/admin/ok.gif">
                            {else}
                                <img src="../img/admin/time.gif">
                            {/if}
                        </td>
                        <td>{$p.date_add}</td>
                        <td class="center">
                            <a href="{$currentIndex}&token={$token}&editPurchase={$p.id_purchase}">
                                <img src="../img/admin/edit.gif" alt="{l s='Edit'}" title="{l s='Edit'}">
                            </a>
                        </td>
                    </tr>
                {foreachelse}
                    <tr>
                        <td colspan="5">
                            {l s='No purchases have been made lately.'}
                        </td>
                    </tr>
                {/foreach}
            </tbody>
        </table>

        <div id="supplier-purchase-pagination" class="pagination butigo-pagination green-pagination"></div>
    </div>

    <div class="clear">&nbsp;</div>
</div>

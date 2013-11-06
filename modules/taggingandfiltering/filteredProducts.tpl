{if $products}
    {include file="$tpl_dir./product-list.tpl" products=$products}
{else}
    <h4 style="text-align: center">{l s='There are no products available for your choice' mod='taggingandfiltering'}</h4>
{/if}
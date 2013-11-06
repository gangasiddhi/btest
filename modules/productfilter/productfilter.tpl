<p id="filter-option-shoesize"><img src="{$img_dir}dropdown-arrow-pink.png"/><span>{l s='Shoe Sizes' mod='productfilter'}</span></p>
<ul class="shoe-size-list {*hidden*}">
    <li><input class="shoe-size-checkbox" name="filterByShoeSize" id="all" type='checkbox' value="all" onClick="$(this).addClass('checkbox-selected');"/><span>{l s='All' mod='productfilter'}</span></li>
    {foreach from=$shoeSizes key=shoeId item=shoeSize}
        {assign var="selectedCustomerShoeSize" value=0}
        {foreach from=$customerSelectedShoeSizes key=csId item=csShoeSize}
            {if $csShoeSize == $shoeSize}
                {assign var="selectedCustomerShoeSize" value=$shoeSize}
            {/if}
        {/foreach}
        <li><input {if $selectedCustomerShoeSize != 0}checked="checked"{/if} class="shoe-size-checkbox" id="{$shoeSize}" name="filterByShoeSize" type='checkbox' value="{$shoeSize}" onClick="$(this).addClass('checkbox-selected');"/><span>{$shoeSize}</span></li>
    {/foreach}
</ul>
{*DONT DELETE THIS CODE, THIS IS USED IN FUTURE*}
{*<div style="margin-bottom: 10px;"></div>
<p id="filter-option-color"><img src="{$img_dir}dropdown-arrow-pink.png"/><span>{l s='Colors'}</span></p>
<ul class="color-list hidden">
    {foreach from=$colors key=colorId item=color}
        {assign var="selectedCustomercolor" value=''}
        {foreach from=$customerSelectedColors key=cscId item=cscolor}
            {if $cscolor == $color}
                {assign var="selectedCustomercolor" value=$color}
            {/if}
        {/foreach}
        <li><input {if $selectedCustomercolor != ''}checked="checked"{/if} class="color-checkbox" name="filterByColor" type='checkbox' value="{$color}"/><span>{$color}</span></li>
    {/foreach}
</ul>*}

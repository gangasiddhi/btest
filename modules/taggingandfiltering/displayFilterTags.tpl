<div id="filter-option-shoesize"><h3><img src="{$img_dir}dropdown-arrow-pink.png"/><span>{l s='Shoe Sizes' mod='taggingandfiltering'}</span></h3></div>
<ul class="shoe-size-list {*hidden*}">
    <li><input class="shoe-size-checkbox checkFilter" name="filterByShoeSize" id="all" type='radio' value="all"/><span>{l s='All' mod='taggingandfiltering'}</span></li>
    {foreach from=$shoeSizes key=shoeId item=shoeSize}
        {assign var="selectedCustomerShoeSize" value=0}
        {foreach from=$customerSelectedShoeSizes key=csId item=csShoeSize}
            {if $csShoeSize == $shoeSize}
                {assign var="selectedCustomerShoeSize" value=$shoeSize}
            {/if}
        {/foreach}
        <li><input {if $selectedCustomerShoeSize != 0}checked="checked"{/if} class="shoe-size-checkbox checkFilter" id="{$shoeSize}" name="filterByShoeSize" type='radio' value="{$shoeSize}"/><span>{$shoeSize}</span></li>
    {/foreach}
</ul>

{foreach from=$filters key='id_filter' item='filter'}
    <div class="filter">
        <p>
            <img src="{$img_dir}dropdown-arrow-pink.png"/><span>{$filter.filter_name}</span>
            <ul>
                {if $filter.filter_mode}
                    {foreach from=$filter.tags key='id_tag' item='tag' name="singleSelectTags"}
                        <li class="tags {$filter.filter_name}{if $smarty.foreach.singleSelectTags.index >= 10} hidden{/if}">
                            <input class="singleClick checkFilter" type="radio" value="{$id_tag}" name="{$filter.filter_name}">
                            <label for="{$filter.filter_name}">{$tag}</label>
                        </li>
                    {/foreach}
					{if $filter.tags|@count > 10}
						<li class="show-more-tags" filterTag="{$filter.filter_name}">{l s='More..' mod='taggingandfiltering'}</li>
					{/if}
                {else}
                    {foreach from=$filter.tags key='id_tag' item='tag' name="multipleSelectTags"}
                        <li class="tags {$filter.filter_name}{if $smarty.foreach.multipleSelectTags.index >= 10} hidden{/if}">
                            <input class="multipleClick checkFilter" type="checkbox" value="{$id_tag}" name="{$filter.filter_name}[]">
                            <label for="{$filter.filter_name}">{$tag}</label>
                        </li>
                    {/foreach}
					{if $filter.tags|@count > 10}
						<li class="show-more-tags" filterTag="{$filter.filter_name}">{l s='More..' mod='taggingandfiltering'}</li>
					{/if}
                {/if}
            </ul>
        </p>
    </div>
{/foreach}
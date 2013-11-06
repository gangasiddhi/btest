{* Displays links when the user is logged in for showroom, style points, account information *}
<div id ="acc-nav">
    <ul>
        {if $logged && ! isset($no_butigim_link)}
		<li {if $page_name == 'showroom'}class="current"{/if}>
			<a href="{$link->getPageLink('showroom.php')}" class="showroom"
				title="{l s='My Showroom' mod='blockmyaccount'}">{l s='My Showroom' mod='blockmyaccount'}</a>
		</li>
        {/if}

        <li {if $page_name == 'lookbook'}class="current"{/if}>
            <a href="{$link->getPageLink('lookbook.php')}" class="lookbook"
                title="{l s='LookBooks' mod='blockmyaccount'}">{l s='LookBooks' mod='blockmyaccount'}</a>
        </li>

        {if $shoe_enabled == 1}
        <li {if $page_name == 'category' && isset($shop_by) && $shop_by== $shoe_link_rewrite}class="current"{/if}
            onmouseover="$('div#category-navblock').show()" onmouseout="$('div#category-navblock').hide()">

            <a href="{$link->getCategoryLink($shoe_link_rewrite)}" class="category"
                title="{l s='Ayakkabı' mod='blockmyaccount'}">{l s='Shoes' mod='blockmyaccount'}</a>

            <div id = "shoe_block">
                {if isset($categories)}
                    <div id="category-navblock">
                        <ul class="cat_links_block">
                            {foreach from=$categories key=cat_id item=eachCategory name=catLoop}
                                <li {if $category->id==$cat_id}class=current_category{/if}>
                                    <a href="{$eachCategory.link}">{$eachCategory.name}</a>
                                </li>
                            {/foreach}
                        </ul>
                    </div>
                {/if}
            </div>
        </li>
        {/if}

        {if $hand_enabled == 1}
		<li {if $page_name == 'category' && isset($shop_by) && $shop_by == $hand_link_rewrite}class="current"{/if}>
			<a href="{$link->getCategoryLink($hand_link_rewrite)}" class="category"
				title="{l s='Canta' mod='blockmyaccount'}">{l s='Canta' mod='blockmyaccount'}</a>
		</li>
        {/if}

		{if $jewelry_enabled == 1}
		<li {if $page_name == 'category' && isset($shop_by) && $shop_by == $jewelry_link_rewrite}class="current"{/if}>
            <a href="{$link->getCategoryLink($jewelry_link_rewrite)}" class="category"
				title="{l s='Jewelry' mod='blockmyaccount'}">{l s='Jewelry' mod='blockmyaccount'}</a>
		</li>
        {/if}

		<li {if $page_name == 'ozgur-masur'}class="current"{/if}>
			<a href="{$link->getPageLink('ozgur-masur.php')}" class="lookbook"
                title="{l s='Ozgur Masur' mod='blockmyaccount'}">{l s='Ozgur Masur' mod='blockmyaccount'}</a>
		</li>

        {if isset($daily_deal_link)}
        <li {if $page_name == 'd_deal'} class="current"{/if}>
            {*<div class="newSign"></div>*}
            <a href="{$link->getPageLink('d_deal.php')}" class="category"
                title="{l s='Daily Deal' mod='blockmyaccount'}">{l s='Daily Deal' mod='blockmyaccount'}</a>
        </li>
        {/if}

        {if $sandals_enabled == 1}
		<li {if $page_name == 'category' && isset($shop_by) && $shop_by == $sandals_link_rewrite}class="current"{/if}>
	                <div class="newSign"></div>
            <a href="{$link->getCategoryLink($sandals_link_rewrite)}" class="category"
				title="{l s='Sandals' mod='blockmyaccount'}">{l s='Sandals' mod='blockmyaccount'}</a>
		</li>
        {/if}
        <li {if $page_name == 'category'}class="current"{/if}>
            {*<div class="newSign"></div>*}
            <a href="{$link->getCategoryLink('bot-cizme-modelleri')}" class="category" title="{l s='Bot Modelleri' mod='blockmyaccount'}">{l s='Bot Modelleri' mod='blockmyaccount'}</a>
        </li>

        {if $low_heels_enabled == 1}
		<li {if $page_name == 'category' && isset($shop_by) && $shop_by == $low_heels_link_rewrite}class="current"{/if}>
			<div class="newSign"></div>
            <a href="{$link->getCategoryLink($low_heels_link_rewrite)}" class="category"
				title="{l s='Low Heels' mod='blockmyaccount'}">{l s='Low Heels' mod='blockmyaccount'}</a>
		</li>
        {/if}

        {if $accessories_enabled == 1}
		<li {if $page_name == 'category' && isset($shop_by) && $shop_by == $accessories_link_rewrite}class="current"{/if}>
			<div class="newSign"></div>
			<a href="{$link->getCategoryLink($accessories_link_rewrite)}" class="category"
				title="{l s='Accessories' mod='blockmyaccount'}">{l s='Accessories' mod='blockmyaccount'}</a>
		</li>
        {/if}

        {if $bridalshoes_enabled == 1}
		<li {if $page_name == 'category' && isset($shop_by) && $shop_by == $bridalshoes_link_rewrite}class="current"{/if}>
			<a href="{$link->getCategoryLink($bridalshoes_link_rewrite)}" class="category"
				title="{l s='Bridal Shoes' mod='blockmyaccount'}">{l s='Bridal Shoes' mod='blockmyaccount'}</a>
		</li>
        {/if}

        {*if $ivanaEnabled == 1}
		<li id="ivana-menu" {if $page_name == 'celebrity'}class="current"{/if}>
			<a href="{$link->getPageLink('celebrity.php')}" class="celebrity"
				title="{l s='Ivana Boutique' mod='blockmyaccount'}">{l s='Ivana Boutique' mod='blockmyaccount'}</a>
		</li>
        {/if*}

        {*if $clothEnabled == 1}
		<li id="cloth-menu" {if $page_name == 'cloth'}class="current"{/if}>
			<a href="{$link->getPageLink('cloth.php')}" class="celebrity"
				title="{l s='Cloth Boutique' mod='blockmyaccount'}">{l s='Cloth Boutique' mod='blockmyaccount'}</a>
		</li>
        {/if*}

        {*if $ozgurEnabled == 1}
		<li id="cloth-menu" {if $page_name == 'celebrity2'}class="current"{/if}>
			<a href="{$link->getPageLink('celebrity2.php')}" class="celebrity"
				title="{l s='Ozgur Boutique' mod='blockmyaccount'}">{l s='Ozgur Boutique' mod='blockmyaccount'}</a>
		</li>
        {/if*}

        {*if $reducedEnabled == 1}
		<li id="reduced-menu" {if $page_name == 'reduced'}class="current"{/if}>
			<a href="{$link->getPageLink('reduced.php')}" class="reduced"
				title="{l s='Discount Boutique' mod='blockmyaccount'}">{l s='Discount Boutique' mod='blockmyaccount'}</a>
		</li>
        {/if*}

        {*if $dealEnabled == 1}
		<li id="deal-menu" {if $page_name == 'deal'}class="current"{/if}>
			<a href="{$link->getPageLink('deal.php')}" class="deal"
				title="{l s='Butigo Deal' mod='blockmyaccount'}">{l s='Butigo Deal' mod='blockmyaccount'}</a>
		</li>
        {/if*}

        {if $accessoriesedProductsEnabled == 1}
		<li {if $page_name == 'accessoriesed-products'}class="current"{/if}>
			<a href="{$link->getPageLink('accessoriesed-products.php')}" class="accessoriesedProducts"
				title="{l s='Accessoriesed Products' mod='blockmyaccount'}">{l s='Accessoriesed Products' mod='blockmyaccount'}</a>
		</li>
        {/if}

        {if $discount_enabled == 1}
		<li {if $page_name == 'category' && isset($shop_by) && $shop_by == $discount_link_rewrite}class="current"{/if}>
			<div class="newSign"></div>
			<a href="{$link->getCategoryLink($discount_link_rewrite)}" class="category highlight"
				title="{l s='Discounted Outlet' mod='blockmyaccount'}">{l s='Discounted Outlet' mod='blockmyaccount'}</a>
		</li>
        {/if}

        <li {if $page_name == 'stylists'}class="current"{/if}>
            <a href="{$link->getPageLink('stylists.php')}" class="stylists"
                title="{l s='My Stylists' mod='blockmyaccount'}">{l s='My Stylists' mod='blockmyaccount'}</a>
        </li>

		<li {if $page_name == 'magazine'}class="current"{/if}>
			<a href="{$link->getPageLink('magazine.php?view=archive')}" class="magazine"
				title="{l s='Butigo Mag' mod='blockmyaccount'}">{l s='Butigo Mag' mod='blockmyaccount'}</a>
		</li>

        {*<li {if $page_name == 'blog'}class="current"{/if}>
            <a href="{$base_dir}blog" class="blog" title="{l s='Blog' mod='blockmyaccount'}">{l s='Blog' mod='blockmyaccount'}</a>
        </li>*}
    </ul>
</div>
{* end of acc-nav *}

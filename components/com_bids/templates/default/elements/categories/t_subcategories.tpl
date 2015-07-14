{foreach from=$subcategories item=scat}
	<div class="auction_treecatsub_head">
		<div class="cat_link">
			<a href="{$scat->view}">{$scat->title}</a>
            <a href="{$scat->view}">
                <img src="{$ROOT_HOST}/components/com_bids/images/product.gif" border="0" alt=""/>
            </a>
            {if $is_logged_in}
                <a href="{$scat->link_watchlist}">
                    {if $scat->watchListed_flag}
                        <img src="{$ROOT_HOST}/components/com_bids/images/f_watchlist_0.jpg" border="0" alt=""/>
                        {else}
                        <img src="{$ROOT_HOST}/components/com_bids/images/f_watchlist_1.jpg" border="0" alt=""/>
                    {/if}
                </a>
            {/if}
            ({'COM_BIDS_NO_OF_AUCTIONS'|translate:$scat->nrAuctions} | {'COM_BIDS_NO_OF_SUBCATS'|translate:$scat->nrSubcategories})
            {if $scat->nrSubcategories}
                <a style="outline: none;" href="#"
                   onclick='toggleBullet({$scat->id});jQuery("#auction_cats_{$scat->id}").slideToggle("slow");return false;'><img
                        id="bullet_{$scat->id}" src="{$ROOT_HOST}/components/com_bids/images/category_bullet_rev.jpg"
                        title="Hide subcategories" alt="Hide subcategories" /></a>
            {/if}
		</div>
	</div>

	<div id="auction_cats_{$scat->id}" class="auction_treecatsub">
		{if $scat->nrSubcategories}
			{include file="elements/categories/t_subcategories.tpl" subcategories=$scat->subcategories}
		{/if}
	</div>
{/foreach}

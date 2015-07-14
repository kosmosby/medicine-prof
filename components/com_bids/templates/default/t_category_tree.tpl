{include file='snippets/t_javascript_language.tpl'}
{include file="../configs/config_template.tpl"}

{import_js_block}
{literal}
function toggleBullet(cat_id){
	if( document.getElementById("bullet_"+cat_id).src == JS_ROOT_HOST+"/components/com_bids/images/category_bullet.jpg" ) 
		document.getElementById("bullet_"+cat_id).src=JS_ROOT_HOST+"/components/com_bids/images/category_bullet_rev.jpg"; 
	else 
		document.getElementById("bullet_"+cat_id).src=JS_ROOT_HOST+"/components/com_bids/images/category_bullet.jpg";	
}
{/literal}
{/import_js_block}
{set_css}

{include file='elements/category/t_category_mode.tpl'}

{foreach from=$categories item=cat}
	<div class="auction_treecat">
		<div class="cat_link">
			<a href="{$cat->view}">{$cat->title}</a>
            <a href="{$cat->view}">
                <img src="{$ROOT_HOST}/components/com_bids/images/product.gif" border="0" alt=""/>
            </a>
            {if $is_logged_in}
                <a href="{$cat->link_watchlist}">
                    {if $cat->watchListed_flag}
                        <img src="{$ROOT_HOST}/components/com_bids/images/f_watchlist_0.jpg" border="0" alt=""/>
                        {else}
                        <img src="{$ROOT_HOST}/components/com_bids/images/f_watchlist_1.jpg" border="0" alt=""/>
                    {/if}
                </a>
            {/if}
            ({'COM_BIDS_NO_OF_AUCTIONS'|translate:$cat->nrAuctions} | {'COM_BIDS_NO_OF_SUBCATS'|translate:$cat->nrSubcategories})
            {if $cat->nrSubcategories}
                <a style="outline: none;" href="#"
                   onclick='toggleBullet({$cat->id}); jQuery("#auction_cats_{$cat->id}").slideToggle("slow");  return false;'><img id="bullet_{$cat->id}" src="{$ROOT_HOST}/components/com_bids/images/category_bullet.jpg"
                         title="Hide subcategories" alt="Hide subcategories" style=""/>
                </a>
            {/if}
		</div>
	</div>
	<div id="auction_cats_{$cat->id}" class="auction_treecatsub" style="display:none;">
		{if $cat->nrSubcategories}
			{include file="elements/categories/t_subcategories.tpl" subcategories=$cat->subcategories}
		{/if}
	</div>
{/foreach}

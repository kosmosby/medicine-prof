{include file="../configs/config_template.tpl"}
{include file='snippets/t_javascript_language.tpl'}
{set_css}
{include file='elements/category/t_category_js.tpl'}

{include file='elements/category/t_category_mode.tpl'}

{$letterFilter}

<table id="auction_categories" cellspacing="0" cellpadding="0">
{foreach name=categories from=$categories item=category}
    {if $smarty.foreach.categories.iteration is odd}
    <tr>
    {/if}
        <td width="50%" class="auction_catcell" valign="top">
            <div class="auction_maincat">
                <a href="{$category->link}" title="{'COM_BIDS_SUBCATEGORIES'|translate}"><strong>{$category->title}</strong></a>

                <a href="{$ROOT_HOST}/index.php?option=com_bids&amp;task=rss&amp;cat={$category->id}&format=raw" target="_blank"><img src="{$ROOT_HOST}/components/com_bids/images/f_rss.jpg" width="10" border="0" alt="rss" /></a>

                {if $category->nrAuctions}
                    <a style="font-size:12px !important;" href="{$category->view}"><img src="{$ROOT_HOST}/components/com_bids/images/product.gif" width="10" border="0" alt="{'COM_BIDS_VIEW_LISTINGS'|translate}" title="{'COM_BIDS_AUCTIONS'|translate}" /></a>
                {/if}
                <a href ="index.php?option=com_bids&amp;task=newauction&amp;category_selected={$category->id}">
                    <img src="{$ROOT_HOST}/components/com_bids/images/new_listing.png" alt="Add" style="width: 12px;" />
                </a>
            </div>
            <div class="auction_subcat" style="background:#F5F5F5; border:1px solid #FFFFFF; " >

                <span style="font-size:12px;">
                    {'COM_BIDS_NO_OF_AUCTIONS'|translate:$category->nrAuctions} | {'COM_BIDS_NO_OF_SUBCATS'|translate:$category->nrSubcategories}
                </span>
            <br/>

            {assign var=was_more value=0}
            {foreach name=subcategories from=$category->subcategories item=subcategory}
                {if !$was_more && ($smarty.foreach.subcategories.iteration+1 > 3)}
                    {assign var=was_more value=1}
                    <div id="auction_hidden_subcats_{$smarty.foreach.categories.iteration}" style="display:none;">
                {/if}
                <a href="{$subcategory->link}">{$subcategory->title}</a>
                    ({'COM_BIDS_NO_OF_AUCTIONS'|translate:$subcategory->nrAuctions} | {'COM_BIDS_NO_OF_SUBCATS'|translate:$subcategory->nrSubcategories})

                <a href="{$ROOT_HOST}/index.php?option=com_bids&amp;task=rss&amp;cat={$subcategory->id}&format=raw" target="_blank"><img src="{$ROOT_HOST}/components/com_bids/images/f_rss.jpg" width="10" border="0" alt="rss" /></a>

                {if $subcategory->nrSubcategories}
                    <a style="font-size:9px !important;" href="{$subcategory->view}"><img src="{$ROOT_HOST}/components/com_bids/images/product.gif" width="10" border="0" alt="view listings" /></a>
                {/if}
                <br />
            {/foreach}
            {if $was_more}
                </div>
                <a href="javascript:void(0)" onclick="showMore({$smarty.foreach.categories.iteration},this)" class="auction_more_link">++{'COM_BIDS_MORE'|translate}</a>
            {/if}
            </div>
        </td>
    {if $smarty.foreach.categories.iteration is even}
    </tr>
    {/if}
{/foreach}
</table>

{include file="../configs/config_template.tpl"}
{set_css}
{include file='snippets/t_javascript_language.tpl'}
{assign var=newauction_url value="`$ROOT_HOST`index.php?option=com_bids&amp;task=newauction&amp;Itemid=`$Itemid`&amp;"}
{if $id}
    {assign var=newauction_url value="`$newauction_url`id=`$id`&amp;"}
{/if}
{assign var=newauction_url value="`$newauction_url`category_selected="}

<div class="auction_header">{'COM_BIDS_PAGE_CATS'|translate}</div>

<table id="auct_categories_select" border="0" width="100%">
    {foreach from=$categories key=key item=category}
        <tr class="auction_choosecat_row{cycle values="1,2"}">
        	<td>
                <div style="padding-left: {$category->depth*15}px;" {if $category->depth==0}class="auction_choosecat_firstcat"{/if}>
                    {if $category->depth>0}>{/if}
                    {if ( $category->nrSubcategories==0 && $bidCfg->bid_opt_leaf_posting_only==1 ) || !$bidCfg->bid_opt_leaf_posting_only}
    	    			<a href="{$newauction_url}{$category->id}">{$category->title}</a>
        			{else}
	        			{$category->title}
        			{/if}
                </div>
        	</td>
        </tr>
    {/foreach}
</table>
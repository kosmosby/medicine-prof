{include file="../configs/config_template.tpl"}
{include file='snippets/t_javascript_language.tpl'}
{import_js_file url="`$ROOT_HOST`/components/com_bids/js/auctions.js"}
{set_css}

<form action="{$ROOT_HOST}index.php" method="get" name="auctionForm">
    <input type="hidden" name="option" value="com_bids" />
    <input type="hidden" name="task" value="listauctions" />
    <input type="hidden" name="Itemid" value="{$Itemid}" />
    <input type="hidden" name="reset" value="" />
    {$lists.inputsHiddenFilters}{* Must be included in the main form - contains filter info *}

    {include file='snippets/t_search_header.tpl'}


    {include file='snippets/t_header_filter.tpl'}

    {* Include filter selectboxes *}
    {include file='elements/auction_list/t_list_header.tpl'}
    {include file='elements/auction_list/t_list_sort.tpl'}

    <table align="center" cellpadding="0" cellspacing="0" width="100%" class="auction_list_container">
        {foreach from=$auction_rows item=current_row}
            {assign var=class_featured value=""}
            {if $current_row->featured=='featured'}
                {assign var=class_featured value="listing-"|cat:$current_row->featured}
            {/if}
            {if $smarty.session.t_display_style=='grid'}
              {include file='elements/auction_list/t_listauctions_gridcell.tpl'}
            {elseif $smarty.session.t_display_style=='list_detail'}
              {include file='elements/auction_list/t_listauctions_listdetailcell.tpl'}
            {else}
              {include file='elements/auction_list/t_listauctions_listcell.tpl'}
            {/if}
        {foreachelse}
            <tr>
                <td>
                    {'COM_BIDS_NO_AUCTIONS'|translate}
                </td>
            </tr>
        {/foreach}
    </table>
    {include file='snippets/t_listfooter.tpl'}
</form>

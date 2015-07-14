{include file="../configs/config_template.tpl"}
{set_css}
{import_js_file url="`$ROOT_HOST`/components/com_bids/js/auctions.js"}
{include file='snippets/t_javascript_language.tpl'}

<form action="{$ROOT_HOST}index.php" method="post" name="auctionForm">
    <input type="hidden" name="option" value="com_bids" />
    <input type="hidden" name="task" value="{$task}" />
    <input type="hidden" name="Itemid" value="{$Itemid}" />
    <input type="hidden" name="filter_order_Dir" value="" />
    {* Include filter selectboxes *}
    {include file='snippets/t_header_filter.tpl'}
    <div class="auction_header">{'COM_BIDS_MY_WATCHLIST'|translate}</div>

    {import_js_file url="`$ROOT_HOST`/components/com_bids/js/ratings.js"}

    <table width="60%" cellpadding="0" cellspacing="0" border="0" id="auction_list_container" >
        <tr>

            <td width="20%">{$lists.filter_cats}</td>
            <td width="30%"><nobr>&nbsp;{'COM_BIDS_ORDER_BY'|translate}&nbsp;{$lists.orders}</nobr></td>
            <td width="20%">&nbsp;&nbsp;{$lists.filter_order_asc}</td>
        </tr>
    </table>
    <table align="center" cellpadding="0" cellspacing="0" width="100%" id="auction_list_container">
        <tr>
            <th style="text-align: center;" colspan="2">{'COM_BIDS_TITLE'|translate}</th>
            <th style="text-align: center;">{'COM_BIDS_BID_AUCTIONEER'|translate}</th>
            <th style="text-align: center;">{'COM_BIDS_HIGHEST_BID'|translate}</th>
            <th style="text-align: center;">{'COM_BIDS_TIME_LEFT'|translate}</th>
            <th style="text-align: center;">{'COM_BIDS_ACTIONS'|translate}</th>
        </tr>
        {foreach from=$auction_rows item=current_row}
            {include file='elements/mywatchlist/t_mywatchlist_cell.tpl'}
        {foreachelse}
            <tr>
                <td colspan="6">
                    {'COM_BIDS_NOTHING_WATCHED'|translate}
                </td>
            </tr>
        {/foreach}
    </table>
    {include file='snippets/t_listfooter.tpl'}
</form>

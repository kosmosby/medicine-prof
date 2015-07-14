{include file="../configs/config_template.tpl"}
{set_css}
{include file='snippets/t_javascript_language.tpl'}
{import_js_file url="`$ROOT_HOST`/components/com_bids/js/auctions.js"}

{include file='elements/myauctions/t_myauctions_mode.tpl'}

<form action="{$ROOT_HOST}index.php" method="get" name="auctionForm">
    <input type="hidden" name="option" value="com_bids">
    <input type="hidden" name="task" value="{$task}">
    <input type="hidden" name="Itemid" value="{$Itemid}">

    {* Include filter selectboxes *}
    {include file='snippets/t_header_filter.tpl'}

    <div style="width:50px; float: right;">
        <a href="{$links->getNewAuctionRoute()}" style="text-decoration: none;">
            <img src="{$ROOT_HOST}/components/com_bids/images/new_auction.jpg" width="32"
                 title="{'COM_BIDS_BUT_NEW'|translate}" alt="{'COM_BIDS_BUT_BULK_IMPORT'|translate}"/>
        </a>&nbsp;&nbsp;
    </div>

    {if $bidCfg->bid_opt_allow_import}
        <div style="width:90px; float:left;font-size:11px;" align="center">
            <a  href="{$ROOT_HOST}/index.php?option=com_bids&task=bulkimport" >
                <img src="{$ROOT_HOST}components/com_bids/images/auction_bulk_import.jpg" width="32" title="{'COM_BIDS_BUT_BULK_IMPORT'|translate}" alt="{'COM_BIDS_BUT_BULK_IMPORT'|translate}"/>
            </a><br />
            {'COM_BIDS_BUT_BULK_IMPORT'|translate}
        </div>
    {/if}
    <div style="float: left; padding-top: 4px;">
        {$lists.filter_cats}&nbsp;&nbsp;
    </div>
    <div style="float: left; padding-top: 4px;">
        {$lists.archive}&nbsp;
    </div>
    <div style="float: left;">
        <span class="auction_search_field" id="search_myauctions_button">
            <input type="submit" value="{'COM_BIDS_FILTER'|translate}" class="auction_button"/>
        </span>
    </div>
    <div style="clear: both;"></div>


    <table cellpadding="0" cellspacing="0" width="100%" id="auction_list_container">
        <tr>
            <th colspan="2" style="text-align: center">{'COM_BIDS_TITLE'|translate}</th>
            <th style="text-align: center">{'COM_BIDS_SORT_HITS'|translate}</th>
            <th style="text-align: center">{'COM_BIDS_NO_BIDS_PLACED'|translate}</th>
            <th style="text-align: center">{'COM_BIDS_PRICE'|translate}</th>
            <th style="text-align: center">{'COM_BIDS_TIME_LEFT'|translate}</th>
            <th style="text-align: center">{'COM_BIDS_ACTIONS'|translate}</th>
        </tr>
        {foreach from=$auction_rows item=current_row}
            {include file='elements/myauctions/t_myauctions_cell.tpl'}
        {foreachelse}
            <tr>
                <td colspan="6">
                    {'COM_BIDS_NO_AUCTIONS_IN_SELECTION'|translate}
                </td>
            </tr>
        {/foreach}
    </table>
    {include file='snippets/t_listfooter.tpl'}
</form>

{include file="../configs/config_template.tpl"}
{set_css}
{include file='snippets/t_javascript_language.tpl'}
{import_js_file url="`$ROOT_HOST`/components/com_bids/js/ratings.js"}
{import_js_file url="`$ROOT_HOST`/components/com_bids/js/auctions.js"}

{include file='elements/mybids/t_mybids_mode.tpl'}

<form action="{$ROOT_HOST}index.php" method="get" name="auctionForm">
    <input type="hidden" name="option" value="com_bids" />
    <input type="hidden" name="task" value="{$task}" />
    <input type="hidden" name="Itemid" value="{$Itemid}" />

    {* Include filter selectboxes *}
    {include file='snippets/t_header_filter.tpl'}

    <table width="60%" cellpadding="0" cellspacing="0" border="0" >
        <tr>
            <td width="30%">{$lists.filter_bidtype}</td>
        </tr>
    </table>

    <table align="center" cellpadding="0" cellspacing="0" width="100%" id="auction_list_container">
        <tr>
            <th colspan="2" style="text-align: center;">{'COM_BIDS_TITLE'|translate}</th>
            <th style="text-align: right;">{'COM_BIDS_MYBID'|translate}</th>
        {if $bidCfg->bid_opt_allow_proxy}
            <th style="text-align: right;">{'COM_BIDS_MY_PROXY'|translate}</th>
        {/if}
            <th style="text-align: right;">{'COM_BIDS_HIGHEST_BID'|translate}</th>
            <th style="text-align: center;">{'COM_BIDS_DATE'|translate}</th>
            <th>{'COM_BIDS_TIME_LEFT'|translate}</th>
        </tr>

        {foreach from=$auction_rows item=current_row}
            {include file='elements/mybids/t_mybids_cell.tpl'}
        {foreachelse}
            <tr>
                <td colspan="6">
                    {'COM_BIDS_NO_BIDS_PLACED'|translate}
                </td>
            </tr>
        {/foreach}
    </table>

    {include file='snippets/t_listfooter.tpl'}
</form>

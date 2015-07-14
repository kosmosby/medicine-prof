{include file="../configs/config_template.tpl"}
{set_css}
{init_behavior type='tooltip'}
{include file='snippets/t_javascript_language.tpl'}
{include file='elements/mywonbids/t_mywonbids_js.tpl'}

{include file='elements/mybids/t_mybids_mode.tpl'}

<form action="index.php" method="post" name="auctionForm">
    <input type="hidden" name="option" value="com_bids">
    <input type="hidden" name="task" value="{$task}">
    <input type="hidden" name="Itemid" value="{$Itemid}">

    {* Include filter selectboxes *}
    {include file='snippets/t_header_filter.tpl'}

</form>
    <table align="center" cellpadding="0" cellspacing="0" width="100%" id="auction_list_container">
        <tr>
            <th style="text-align: center;" colspan="2">{'COM_BIDS_TITLE'|translate}</th>
            <th style="text-align: center;">{'COM_BIDS_BID_AUCTIONEER'|translate}</th>
            <th style="text-align: center;">{'COM_BIDS_DATE'|translate}</th>
            <th style="text-align: center;">{'COM_BIDS_TOTAL_PRICE'|translate}</th>
        </tr>

        {foreach from=$auction_rows item=current_row}
            {include file='elements/mywonbids/t_mywonbids_cell.tpl'}
        {foreachelse}
            <tr>
                <td colspan="6">
                    {'COM_BIDS_NO_WON_BIDS'|translate}
                </td>
            </tr>
        {/foreach}
    </table>

    {include file='snippets/t_listfooter.tpl'}

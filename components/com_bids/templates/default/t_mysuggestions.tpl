{include file="../configs/config_template.tpl"}
{set_css}
{include file='snippets/t_javascript_language.tpl'}

{include file='elements/mybids/t_mybids_mode.tpl'}

<form action="{$ROOT_HOST}index.php" method="post" name="auctionForm">
    <input type="hidden" name="option" value="com_bids">
    <input type="hidden" name="task" value="{$task}">
    <input type="hidden" name="Itemid" value="{$Itemid}">
    {* Include filter selectboxes *}
    {include file='snippets/t_header_filter.tpl'}


    <table align="center" cellpadding="0" cellspacing="0" width="100%" id="auction_list_container">
        <tr>
            <th colspan="2">{'COM_BIDS_TITLE'|translate}</th>
            <th style="text-align: right;">{'COM_BIDS_MY_SUGGESTION'|translate}</th>
            <th style="text-align: center;">{'COM_BIDS_DATE'|translate}</th>
            <th style="text-align: center;">{'COM_BIDS_TIME_LEFT'|translate}</th>
            <th style="text-align: center;">{'COM_BIDS_STATUS'|translate}</th>
        </tr>
        {foreach from=$auction_rows item=current_row}
            {include file='elements/mysuggestions/t_mysuggestions_cell.tpl'}
        {foreachelse}
            <tr>
                <td colspan="6">
                    {'COM_BIDS_NO_SUGGESTIONS_PLACED'|translate}
                </td>
            </tr>
        {/foreach}
    </table>

    {include file='snippets/t_listfooter.tpl'}
</form>

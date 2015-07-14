{include file="../configs/config_template.tpl"}
{set_css}

{include file='snippets/t_javascript_language.tpl'}
{import_js_file url="`$ROOT_HOST`/components/com_bids/js/auctions.js"}
{import_js_file url="`$ROOT_HOST`/components/com_bids/js/spinner.js"}
{import_css_file url="`$ROOT_HOST`/components/com_bids/js/spinner.css"}

{include file='elements/myauctions/t_myauctions_mode.tpl'}

<form name="form_suggestPrice" method="POST" action="{$ROOT_HOST}index.php">
<input type="hidden" name="option" value="{$option}" />
<input type="hidden" name="task" value="suggest" />
<input type="hidden" id="auction_id" name="id" value="" />
<input type="hidden" id="s_id" name="s_id" value="" />
<input type="hidden" id="form_bid_suggest" name="bid_suggest" value="" />
<!-- -> [+] 1.6.7 --> 
<input type="hidden" id="quantity" name="quantity" value="" />
<!-- <- [+] 1.6.7 --> 

</form>


<form action="{$ROOT_HOST}index.php" method="get" name="auctionForm">
    <input type="hidden" name="option" value="com_bids">
    <input type="hidden" name="task" value="{$task}">
    <input type="hidden" name="Itemid" value="{$Itemid}">
    {* Include filter selectboxes *}
    {include file='snippets/t_header_filter.tpl'}

    <table align="center" cellpadding="0" cellspacing="0" width="100%" id="auction_list_container">
        <tr>
            <th>{'COM_BIDS_TITLE'|translate}</th>
            <th>{'COM_BIDS_SUGGEST_BY'|translate}</th>
            <th>{'COM_BIDS_SUGGESTION'|translate}</th>
            <th>{'COM_BIDS_DATE'|translate}</th>
            <th>{'COM_BIDS_TIME_LEFT'|translate}</th>
            <th>{'COM_BIDS_STATUS'|translate} / {'COM_BIDS_ACTIONS'|translate}</th>
        </tr>

        {foreach from=$auction_rows item=current_row}
            {include file='elements/suggestions/t_suggestions_cell.tpl'}
        {foreachelse}
            <tr>
                <td colspan="6">
                    {'COM_BIDS_NO_SUGGESTIONS_RECEIVED'|translate}
                </td>
            </tr>
        {/foreach}
    </table>

    {include file='snippets/t_listfooter.tpl'}
</form>

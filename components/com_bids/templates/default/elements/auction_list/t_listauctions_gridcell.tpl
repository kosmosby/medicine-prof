{assign var=cellclass value="auction_grid_row"}

{if $current_row->rownr == 1}
<tr>
{/if}
    <td width="20%" class="{$cellclass} {$class_featured}">
        <div class="cell_container">
            {positions position="cell-header" item=$current_row page="auctions"}
            <div class="auction_info_top">
                <div class="auction_title">
                    {assign var="dots" value="1"}
                    {if $current_row->title|substr:0:40 == $current_row->title}
                        {assign var="dots" value="0"}
                    {/if}
                    <a href="{$current_row->links.auctiondetails}" class="hasTip" title=" ::{$current_row->title}">{$current_row->title|substr:0:40}{if $dots}...{/if}</a>
                </div>
                <div style="height: 15px;">&nbsp;</div>
                <div class="auction_thumb_list" align="center">
                    <a href="{$current_row->links.auctiondetails}">{$current_row->thumbnail}</a>
                </div>
            </div>
            <div class="auction_price">
                <table style="width: 100%;">
                    <tr>
                        <td style="width: 40%; vertical-align: bottom; padding-bottom: 4px;
                        "><span>{'COM_BIDS_CURRENT_BID'|translate}</span></td>
                        <td style="width: 60%; vertical-align: bottom; text-align: right;">
                            {if $current_row->highest_bid gt 0}
                                {print_price price=$current_row->highest_bid}
                                {elseif $current_row->auction_type!=$smarty.const.AUCTION_TYPE_BIN_ONLY}
                                {print_price price=$current_row->initial_price}
                            {/if}
                            <span>{$current_row->currency}</span>
                        </td>
                    </tr>
                </table>

            </div>
            {if $current_row->BIN_price>0}
                <table style="width: 100%;">
                    <tr>
                        <td style="width: 40%; vertical-align: bottom; padding-bottom: 3px;">
                            <span class="auction_price_bold">
                                {'COM_BIDS_BIN_TEXT'|translate}
                            </span>
                        </td>
                        <td style="width: 60%; vertical-align: bottom; text-align: right;">
                            <span class="auction_price_bold">
                                {print_price price=$current_row->BIN_price cssprice="bids_price_bin"}
                                {$current_row->currency}
                            </span>
                        </td>
                    </tr>
                </table>
            {/if}
            <div class="auction_grid_info_bottom">
                {if $current_row->close_offer}
                    <span class='canceled_on'>
                    {if $current_row->end_date gt $current_row->closed_date}
                        {'COM_BIDS_CANCELED_ON'|translate}
                    {else}
                        {'COM_BIDS_CLOSED_ON_DATE'|translate}
                    {/if}:
                    </span>
                    {printdate date=$current_row->closed_date use_hour=1}
                {elseif  $current_row->expired}
                   <span class='expired'>{'COM_BIDS_EXPIRED'|translate}</span>
                {elseif $bidCfg->bid_opt_enable_countdown}
                    <strong>{$current_row->countdownHtml}</strong>
                {/if}
            </div>
            {positions position="cell-footer" item=$current_row page="auctions"}
        </div>
    </td>

{if ($current_row->rownr is div by 4)||($current_row->rownr==$auction_rows|@count)}
</tr>
{/if}

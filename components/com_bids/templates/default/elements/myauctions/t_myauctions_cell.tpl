{cycle values='1,2' assign=class}
<tr>
    <td colspan="7">
        {positions position="cell-header" item=$current_row page="auctions"}
    </td>
</tr>
<tr class="auction_myauction_row{$class}">
    <td valign="top">
        {positions position="cell-left" item=$current_row page="auctions"}
        <div class="auction_thumbnail_myauction">
            {$current_row->thumbnail}
        </div>
    </td>
    <td>
        <div class="auction_title_small">
            <a href="{$current_row->links.auctiondetails}">{$current_row->title}</a>
        </div>
    </td>
    <td  style="text-align: center">
        {$current_row->hits}
    </td>
    <td style="text-align: center">
        {$current_row->nr_bidders}
    </td>
    <td style="text-align: right; min-width: 110px;">
        {positions position="cell-middle" item=$current_row page="auctions"}
        {if $current_row->highest_bid>0}
            {print_price price=$current_row->highest_bid}
        {elseif $current_row->initial_price>0}
            {print_price price=$current_row->initial_price}
        {elseif $current_row->BIN_price>0}
            {print_price price=$current_row->BIN_price}
        {/if}
        &nbsp;{$current_row->currency}
    </td>
    <td style="text-align: center; padding: 3px;">
        {if $current_row->countdownHtml}
                {$current_row->countdownHtml}
            {else}
                {$current_row->auction_status}
         {/if}
    </td>
    <td style="text-align: center;width:65px;">
        {assign var=auction value=$current_row}
        {include file='elements/auction_details/t_edit_cancel_buttons.tpl'}
        {positions position="cell-right" item=$current_row page="auctions"}
    </td>
</tr>
<tr>
    <td colspan="6">
        {positions position="cell-footer" item=$current_row page="auctions"}
    </td>
</tr>
{cycle values='1,2' assign=class}
    <tr>
        <td colspan="6">
            {positions position="cell-header" item=$current_row page="auctions"}
        </td>
    </tr>
    <tr id="auction_mybids_row{$class}">

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
        <td style="text-align: right;">
            {print_price price=$current_row->mybid}&nbsp;{$current_row->currency}
        </td>
        {if $bidCfg->bid_opt_allow_proxy}
        <td style="text-align: right;">
            {if $current_row->my_proxy_bid}
                {$current_row->my_proxy_bid}
            &nbsp;{$current_row->currency}
            {else}
                &nbsp;-&nbsp;
            {/if}
        </td>
        {/if}
        <td style="text-align: right;">
            {positions position="cell-middle" item=$current_row page="auctions"}
            {if $current_row->highest_bid}
                {print_price price=$current_row->highest_bid}
            {elseif $current_row->initial_price}
                {print_price price=$current_row->initial_price}
            {else}
                {print_price price=$current_row->BIN_price}
            {/if}
            &nbsp;{$current_row->currency}
        </td>
        <td style="text-align: center">
            {printdate date=$current_row->mybid_date}
        </td>
        <td>
            {if $bidCfg->bid_opt_enable_countdown && !$current_row->expired && !$current_row->close_offer}
                {$current_row->countdownHtml}
        	{/if}
			{if $current_row->close_offer}
			    <span class='canceled_on'>
			    {if !$current_row->won_bid }
			        {'COM_BIDS_CANCELED'|translate}
			    {else}
			        {'COM_BIDS_CLOSED'|translate}
			    {/if}
			    </span>
            {elseif  $current_row->expired}
	           <font class='expired'>{'COM_BIDS_EXPIRED'|translate}</font><br/>
	        {/if}
            {positions position="cell-right" item=$current_row page="auctions"}
        </td>
	</tr>
    <tr>
        <td colspan="6">
            {positions position="cell-footer" item=$current_row page="auctions"}
        </td>
    </tr>

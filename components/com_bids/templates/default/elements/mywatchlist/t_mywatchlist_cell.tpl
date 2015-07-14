{if $current_row->rownr is odd}
	{assign var=class value="1"}
{else}
	{assign var=class value="2"}
{/if}
    <tr>
        <td colspan="3">
            {positions position="cell-header" item=$current_row page="auctions"}
        </td>
    </tr>
    <tr class="auction_mywatchlist_row{$class}">

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
        <td style="text-align: center;">
            <a style="text-decoration: none;" href="{$current_row->links.otherauctions}"
                title="{'COM_BIDS_MORE_OFFERS_USER'|translate}">{$current_row->username}</a>
        </td>
        <td style="text-align: right; padding-right: 5px;">
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
        <td style="text-align: center;">
            {if $bidCfg->bid_opt_enable_countdown && !$current_row->expired && !$current_row->close_offer}
                {$current_row->countdownHtml}
        	{/if}
			{if $current_row->close_offer}
			    <span class='canceled_on'>
			    {if $current_row->end_date gt $current_row->closed_date}
			        {'COM_BIDS_CANCELED'|translate}
			    {else}
			        {'COM_BIDS_CLOSED'|translate}
			    {/if}
			    </span>
            {elseif  $current_row->expired}
	           <font class='expired'>{'COM_BIDS_EXPIRED'|translate}</font><br/>
	        {/if}
        
        </td>
        <td style="text-align: center;">
			<span id='add_to_watchlist'><a href='{$current_row->links.del_from_watchlist}'>
				<img src="{$ROOT_HOST}/components/com_bids/images/f_watchlist_0.jpg" title="{'COM_BIDS_REMOVE_FROM_WATCHLIST'|translate}" alt="{'COM_BIDS_REMOVE_FROM_WATCHLIST'|translate}"/>
			</a></span>
            {positions position="cell-right" item=$current_row page="auctions"}
        </td>
	</tr>
    <tr>
        <td colspan="3">
            {positions position="cell-footer" item=$current_row page="auctions"}
        </td>
    </tr>
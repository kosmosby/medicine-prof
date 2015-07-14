        <tr class="auction_suggestion_row{cycle values='1,2'}">
            <td valign="top">
                <div class="auction_thumbnail_myauction">
                    {$current_row->thumbnail}
                </div>
            </td>
            <td align="center">
        		<a style="text-decoration: none;" href="{$current_row->links.auctiondetails}">{$current_row->title}</a>
        	</td>
        	<td align="right">
	        	<strong>{$current_row->quantity} x &nbsp;{print_price auction=$current_row price=$current_row->suggested_price}</strong>
        	</td>
        	<td align="center">
	        	{$current_row->bid_date}
        	</td>
        	<td align="center">
				{if  $current_row->expired}
				<div id="auction_info_bottom">
					   <font class='expired'>{'COM_BIDS_EXPIRED'|translate}</font>
				</div>
				{elseif $bidCfg->bid_opt_enable_countdown}
				<div id="auction_info_bottom">
					{$current_row->countdownHtml}
				</div>
				{/if}
        	</td>
        	<td align="center">
				{if $current_row->accept==0}{'COM_BIDS_SUGGEST_STATUS_REJECTED'|translate}{elseif $current_row->accept==1}{'COM_BIDS_SUGGEST_STATUS_PENDING'|translate}
				{else}{'COM_BIDS_SUGGEST_STATUS_PENDING'|translate}{/if}
        	</td>
        	<td align="center">
				{if $current_row->close_offer}
					<div id="auction_info_bottom">
					<span class='canceled_on'>
					{if $current_row->end_date gt $current_row->closed_date}
						{'COM_BIDS_CANCELED_ON'|translate}
					{else}
						{'COM_BIDS_CLOSED_ON_DATE'|translate}
					{/if}:
					</span>
                    {printdate date=$current_row->closed_date use_hour=1}
					</div>
				{/if}
        	</td>
        </tr>

{assign var=other_items value=$lists.other_items}

<table width="100%" cellpadding="0" cellspacing="0">
<tr>
    {foreach from=$other_items item=item name=other_items}
    <td class="auction_grid_row">
        <div id="cell_container">
            <div class="auction_info_top">
            <div id="auction_thumb_list">
                <a href="{$links->getAuctionDetailRoute($item)}">{$item->thumbnail}</a>
            </div>
            <div id="auction_title">
        		<a href="{$links->getAuctionDetailRoute($item)}">{$item->title}</a>
        	</div>
            </div>
            <div class="auction_price">
                <span>{'COM_BIDS_BID'|translate}:</span>&nbsp;<b>
                {if $item->highest_bid gt 0}
    				{print_price auction=$item price=$item->highest_bid}
                {else}
    				{print_price auction=$item price=$item->initial_price}
                {/if}
                </b>
            </div>
        	{if $item->BIN_price>0}
                <div id="auction_bin">
                    <span>{'COM_BIDS_BIN_TEXT'|translate}:</span>&nbsp;
        			<span class="auction_price_bold">
    					{print_price auction=$item price=$item->BIN_price}
                    </span>
                </div>
        	{/if}
            <div id="auction_info_bottom">
        		{if $item->close_offer}
        			<span class='canceled_on'>
        			{if $item->end_date>$item->closed_date}
        				{'COM_BIDS_CANCELED_ON'|translate}
        			{else}
        				{'COM_BIDS_CLOSED_ON_DATE'|translate}
        			{/if}:
        			</span>
        			{printdate date=$item->closed_date use_hour=1}
        		{elseif  $item->expired}
        		   <span class='expired'>{'COM_BIDS_EXPIRED'|translate}</span>
        		{elseif $bidCfg->bid_opt_enable_countdown}
        			<strong>{$item->countdownHtml}</strong>
        		{/if}
            </div>
        </div>

    </td>
    {foreachelse}
        <td>{'COM_BIDS_NO_OTHER_AUCTIONS'|translate}</td>
    {/foreach}
</tr>
</table>

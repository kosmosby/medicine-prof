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
    <tr id="auction_mywonbids_row{$class}">
        <td valign="top" style="width: 50px;">
            {positions position="cell-left" item=$current_row page="auctions"}
            <div class="auction_thumbnail_myauction">
            {$current_row->thumbnail}
            </div>
        </td>
        <td style="width: 200px;">
            <div class="auction_title_small">
                <a href="{$current_row->links.auctiondetails}">{$current_row->title}</a>
            </div>
        </td>
        <td style="text-align: center;" width="60">
            <a style="text-decoration: none;" href="{$current_row->links.otherauctions}"
                title="{'COM_BIDS_MORE_OFFERS_USER'|translate}">{$current_row->username}</a>
        </td>
        <td style="text-align: center;" width="100">
            {positions position="cell-middle" item=$current_row page="auctions"}
			{if $current_row->close_offer}
                <span class='canceled_on'>
                    {'COM_BIDS_CLOSED_ON_DATE'|translate}:
                </span><br />
                {printdate date=$current_row->closed_date use_hour=0}
            {else}
                <span>
                    {'COM_BIDS_STILL_RUNNING'|translate}
                </span>
            {/if}
        </td>
        <td style="text-align: center;" width="200">
			{if $current_row->sellerHasToRate}
                <a href="{$current_row->links.rate_auction}">
                    <img src="{$ROOT_HOST}/components/com_bids/images/rate_auction.jpg" class="hasTip" title="{'COM_BIDS_RATE'|translate}" alt="{'COM_BIDS_RATE'|translate}"/>
                </a>
            {/if}

            {assign var=auctionId value=$current_row->id}
            {$lists.bidderPaypalButton.$auctionId}
            {positions position="cell-right" item=$current_row page="auctions"}
        </td>
	</tr>
    <tr>
        <td colspan="3">
            {positions position="cell-footer" item=$current_row page="auctions"}
        </td>
    </tr>

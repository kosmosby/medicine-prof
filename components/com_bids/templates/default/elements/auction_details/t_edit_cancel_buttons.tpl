{if $auction->isMyAuction}
		{if $auction->close_offer}
            <span class="auction_action_button">
                <a href="{$auction->links.republish}">
                    <span class="hasTip actionsTooltip" title=" ::{'COM_BIDS_IMG_REPUB_OFFER'|translate}"
                          style="background-image:url('{$ROOT_HOST}/components/com_bids/images/republish_auction.jpg'); background-repeat: no-repeat;
                                  "></span>
                </a>
            </span>
		{else}
            <span class="auction_action_button">
                <a href="{$auction->links.edit}">
                    <span class="hasTip actionsTooltip" title=" ::{'COM_BIDS_IMG_EDIT_OFFER'|translate}"
                          style="background-image:url('{$ROOT_HOST}/components/com_bids/images/edit_auction.jpg');
                                  background-repeat: no-repeat;"></span>
                </a>
            </span>
		{/if}
		{if !$auction->close_offer}
            <span class="auction_action_button">
                <a onclick="return confirm('{'COM_BIDS_CONFIRM_CANCEL_AUCTION'|translate}');" href="{$auction->links.cancel}" >
                    <span class="hasTip actionsTooltip" title=" ::{'COM_BIDS_IMG_CANCEL_OFFER'|translate}"
                          style="background-image:url('{$ROOT_HOST}/components/com_bids/images/cancel_auction.jpg');
                                  background-repeat: no-repeat;"></span>
                </a>
            </span>
		{/if}
{/if}

{* define the cell CSS class *}
{assign var=cellclass value="auction_row"}
    <tr>
        <td colspan="2">
            {positions position="cell-header" item=$current_row page="auctions"}
        </td>
    </tr>
    <tr class="{$cellclass} {$class_featured}">
		<td class="auction_thumb_list" valign="top">
            <a href="{$current_row->links.auctiondetails}">{$current_row->thumbnail}</a>
        </td>
		<td valign="top" class="auction_cell" >
			<table width="100%">
			<tr>
    			<td colspan="3" valign="top">
    				<div class="auction_title">
    					<a href="{$current_row->links.auctiondetails}">{$current_row->title}</a>
    				</div>
    			</td>
			</tr>
			<tr>
    			<td class="auction_left" valign="top">
    				<div class="auction_description">
    					{$current_row->shortdescription}
                        {positions position="cell-left" item=$current_row page="auctions"}
    				</div>
    			</td>
                <td class="auction_middle" style="text-align: right; padding-right: 5px;">
                        {positions position="cell-middle" item=$current_row page="auctions"}
                        <div class="auction_price">
                            {if $current_row->highest_bid gt 0}
								{print_price auction=$current_row price=$current_row->highest_bid}
                            {elseif $current_row->auction_type!=$smarty.const.AUCTION_TYPE_BIN_ONLY}
								{print_price auction=$current_row price=$current_row->initial_price}
                            {/if}
                        </div>
    					{if $current_row->BIN_price>0}
                            <div class="auction_price">
            					<span class="auction_price_bold">
                                    {'COM_BIDS_BIN_TEXT'|translate}&nbsp;
									{print_price auction=$current_row price=$current_row->BIN_price
									cssprice="bids_price_bin"}
                                </span>
                            </div>
    					{/if}
                    
                </td>
    			<td class="auction_right"  valign="top">
    				<div class="auction_info_bottom">
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
    						   <font class='expired'>{'COM_BIDS_EXPIRED'|translate}</font>
    						{elseif $bidCfg->bid_opt_enable_countdown}
    							 {$current_row->countdownHtml}
    						{/if}
                            {positions position="cell-right" item=$current_row page="auctions"}
			         </div>
    			</td>
			</tr>
			</table>
		</td>
	</tr>
    <tr>
        <td colspan="2">
            {positions position="cell-footer" item=$current_row page="auctions"}
        </td>
    </tr>
    <tr>
        <td colspan="2"><div class="hr"></div></td>
    </tr>

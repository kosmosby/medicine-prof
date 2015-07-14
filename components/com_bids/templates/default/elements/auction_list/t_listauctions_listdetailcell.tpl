{* define the cell CSS class *}
{assign var=cellclass value="auction_details_row"}
{assign var=class_featured value=""}


{if $current_row->featured && 	$current_row->featured!='none'}
	{assign var=class_featured value="listing-"|cat:$current_row->featured}
{/if}
    <tr class="{$cellclass} {$class_featured}">
{* Thumbnail part *}
		<td class="auction_thumb_list" valign="top">
            <a href="{$current_row->links.auctiondetails}">{$current_row->thumbnail}</a>
        </td>
{* /Thumbnail part *}
		<td valign="top" class="auction_cell" >
            {positions position="cell-header" item=$current_row page="auctions"}
			<table width="100%">
{* Title spans over the rest of the cell*}
			<tr>
    			<td colspan="2" valign="top">
    				<div class="auction_title">
    					<a href="{$current_row->links.auctiondetails}">{$current_row->title}</a>
    				</div>
    			</td>
                <td style="text-align: right;">

                    <span class="auction_number">{'COM_BIDS_AUCTION_NUMBER'|translate}&nbsp;
                        {$current_row->auction_nr}</span>

                </td>
			</tr>
{* /Title spans over the rest of the cell*}
			<tr>
    			<td class="auction_left" valign="top">
                    {positions position="cell-left" item=$current_row page="auctions"}
                    <div class="auction_description">
                    {$current_row->shortdescription}
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
                                        {'COM_BIDS_BIN_TEXT'|translate}&nbsp;{print_price auction=$current_row
                                    price=$current_row->BIN_price
                                        cssprice="bids_price_bin"}
                                    </span>
                                </div>
        					{/if}

    						{if $current_row->mybid}
                                <div class="auction_price">
                                    <div class="auction_price_bold">{'COM_BIDS_MYBID'|translate} {print_price
                                    auction=$current_row price=$current_row->mybid}</div>
                                </div>
    						{/if}


						<div class="auction_price">{'COM_BIDS_BIDS'|translate}: {$current_row->nr_bidders}</div>

                </td>
    			<td class="auction_right" valign="top">
    				<div class="auction_info_bottom">
                        {positions position="cell-right" item=$current_row page="auctions"}
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
							 <span class="auction_timer" id="time{$current_row->rownr}">{$current_row->countdownHtml}</span>
						{/if}

                        <div class="auction_date">
                            <span>{'COM_BIDS_START_DATE'|translate} </span>&nbsp;{$current_row->start_date_text}
                        </div>


                        <div class="auction_date">
                            <span>{'COM_BIDS_END_DATE'|translate}</span>&nbsp;{$current_row->end_date_text}
                        </div>

			         </div>
    			</td>
			</tr>

			<tr>
				<td colspan="3">
                    <div class="auction_category">

                        <span>

                            {if $current_row->categoryname}
                                <a href="{$current_row->links.filter_cat}">{$current_row->categoryname}</a>,
                                {else}
                                &nbsp;-&nbsp;,
                            {/if}


                                &nbsp;&nbsp;<a href="{$current_row->links.otherauctions}"
                                        alt="{'COM_BIDS_BIN_TEXT'|translate}">{$current_row->username}</a>


                                &nbsp;
                                <a href="{$current_row->links.auctioneer_profile}" alt="{$smarty.const._DETAILS_TITLE}">
                                    <span class="rating_user" rating="{$current_row->rating_overall}"></span>
                                </a>

                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;

                                <span class="auction_bid_toolbar">
                                    <span class="auction_bid_toolicons">
                                    {if $current_row->add_to_watchlist}
                                        <span class='add_to_watchlist'>
                                            <a href='{$current_row->links.add_to_watchlist}'>
                                                <img src="{$ROOT_HOST}/components/com_bids/images/f_watchlist_1.jpg"
                                                     title="{'COM_BIDS_ADD_TO_WATCHLIST'|translate}"
                                                     alt="{'COM_BIDS_REMOVE_FROM_WATCHLIST'|translate}"/>
                                            </a>
                                        </span>
                                        {elseif $current_row->del_from_watchlist}
                                        <span class="add_to_watchlist">
                                            <a href='{$current_row->links.del_from_watchlist}'>
                                                <img src="{$ROOT_HOST}/components/com_bids/images/f_watchlist_0.jpg"
                                                     title="{'COM_BIDS_REMOVE_FROM_WATCHLIST'|translate}"
                                                     alt="{'COM_BIDS_NEWMESSAGES'|translate}"/>
                                            </a>
                                        </span>
                                    {/if}
                                        <span id='new_message'>
                                            <a href='{$current_row->links.messages}'>
                                                {if $current_row->nrNewMessages}
                                                    <img src="{$ROOT_HOST}/components/com_bids/images/f_message_1.png"
                                                         title="{'COM_BIDS_NEWMESSAGES'|translate}"
                                                         alt="{'COM_BIDS_NEW_MESSAGES'|translate}"/>
                                                    {else}
                                                    <img src="{$ROOT_HOST}/components/com_bids/images/f_message_0.png"
                                                         title="{'COM_BIDS_NO_NEW_MESSAGES'|translate}"
                                                         alt="{'COM_BIDS_TAB_OFFER_BIDNEW'|translate}"/>
                                                {/if}
                                            </a>
                                        </span>
                                        {if $current_row->isMyAuction!=1 && !$current_row->close_offer}
                                            <a href='{$current_row->links.bids}'><img
                                                    src="{$ROOT_HOST}/components/com_bids/images/f_bid.gif"
                                                    title="{'COM_BIDS_TAB_OFFER_BIDNEW'|translate}"
                                                    alt="{'COM_BIDS_AUCTION_NUMBER'|translate}"/></a>
                                        {/if}
                                </span>

                        </span>
                        </span>
                    </div>

                    <div style="clear: both; line-height: 5px;">&nbsp;</div>
				</td>
			</tr>
			</table>
            {positions position="cell-footer" item=$current_row page="auctions"}
		</td>
	</tr>
    <tr>
        <td colspan="2"><div class="hr"></div></td>
    </tr>

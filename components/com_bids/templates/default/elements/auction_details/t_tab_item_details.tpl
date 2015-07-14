<table width="100%">
    <tr>
        <td class="auction_details_left" style="width:10px;" rowspan="3">
            <div style=" text-align: center; vertical-align: top;">
                {$auction->gallery}
            </div>
        </td>
    {* AUCTION BIDDING *}
        <td  valign="top" class="auction_details_middle" id="bidsDetailsMiddleCollumnContainer">
            <div id="bidsDetailsMiddleCollumn">
                <div style="padding-left: 5px;">
                    {positions position="header" item=$auction page="auctions"}
                    <table width="100%">
                        <tr>
                            <td>
                            {if $auction->auction_type != $smarty.const.AUCTION_TYPE_BIN_ONLY}
                            {* PRINT PRICING INFORMATION (not available for BIN Only)*}
                                <div>
                                    <strong>{'COM_BIDS_BID_PRICE'|translate}:</strong>
                                    {if $auction->highestBid->bid_price==='private'}
                                        {'COM_BIDS_PRIVATE'|translate}
                                    {else}
                                        {print_price auction=$auction price=$auction->highestBid->bid_price}
                                        (<a href="javascript:launchpca( '{$auction->highestBid->bid_price}','{$auction->currency}','USD');">{'COM_BIDS_CONVERT'|translate}</a>)<br/>
                                        {if $auction->highestBid->bid_price|intval}
                                            {'COM_BIDS_BY_USER'|translate}
                                            <strong>{$auction->highestBid->username}</strong> {'COM_BIDS_ON'|translate} {printdate date=$auction->highestBid->modified usehour=1}
                                        {/if}
                                    {/if}
                                </div>
                            {/if}

                            {if $auction->myBid->bid_price > 0}
                            {*MY BID Info*}
                                <div class="auction_grey_text">
                                    <strong>{'COM_BIDS_MYBID'|translate}:</strong>
                                    {print_price auction=$auction price=$auction->myBid->bid_price}
                                    (<a href="javascript:launchpca('{$auction->myBid->bid_price|string_format:"%.2f"}','{$auction->currency}','USD');">{'COM_BIDS_CONVERT'|translate}</a>)
                                </div>
                            {/if}

                            {if $auction->my_proxy_bid>0}
                                <div class="auction_grey_text">
                                    <strong>{'COM_BIDS_MY_PROXY'|translate}:</strong>
                                    {print_price auction=$auction price=$auction->my_proxy_bid}
                                </div>
                            {/if}

                            {include file="elements/auction_details/t_bidbox.tpl"}

                            </td>
                        </tr>
                    </table>

                    <table width="100%">
                        <tr>
                            <td>
                            {positions position="detail-left" item=$auction page="auctions"}
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </td>
    {* SELLER INFO, RATING, AUCTION INFO *}
        <td valign="top" class="auction_details_right" style="padding-right: 0; width: 200px;">
            <table class="auction_caption_box" style="vertical-align: top;">
                <tr>
                <th>{'COM_BIDS_SELLER_INFO'|translate}</th>
                </tr>
                <tr>
                <td>
                    <a href="{$auction->links.auctioneer_profile}"><strong>{$auctioneer->username}</strong></a>
                    ( {$lists.ratingsDetails} )
                    &nbsp;
                    {if $auctioneer_details->verified}<img src="{$ROOT_HOST}/components/com_bids/images/verified_1.gif"  id='auction_star' height="16" border="0" title="{'COM_BIDS_USER_VERIFIED'|translate}" class="hasTip"/>{/if}
                    &nbsp;
                    {if $auctioneer_details->powerseller}<img src="{$ROOT_HOST}/components/com_bids/images/powerseller_1.png"  id='auction_star' height="16" border="0" title="{'COM_BIDS_USER_POWERSELLER'|translate}" class="hasTip"/>{/if}
                    &nbsp;
                    <a href="index.php?option=com_bids&task=rss&user={$auction->userid}&format=raw" target="_blank">
                       <img src="{$ROOT_HOST}/components/com_bids/images/f_rss.jpg" width="10" border="0">
                    </a>
                </td>
                </tr>
                <tr>
                <td>{'COM_BIDS_REGISTER_SINCE'|translate}: <strong>{printdate date=$auctioneer->registerDate use_hour=0}</strong> </td>
                </tr>
                <tr>
                <td>
                    {'COM_BIDS_USER_LOCATION'|translate}: <strong>{$auctioneer_details->city}, {$auctioneer_details->country}</strong>
                </td>
                </tr>
                <tr>
                <td><a href="{$auction->links.otherauctions}">{'COM_BIDS_AUCTIONS_BY_USER'|translate:$auctioneer_details->username}</a> </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td rowspan="2" style="vertical-align: bottom; padding-right: 5px; padding-top: 10px;">
            {if $auction->shipmentPrices|count || $auction->shipment_info}
                <div>
                    <table class="auction_caption_box">
                        <tr>
                            <th>{'COM_BIDS_SHIPPING'|translate}</th>
                        </tr>
                        <tr>
                            <td>
                            {$auction->shipment_info}
                            </td>
                        </tr>
                        <tr>
                            <td>
                            {if $auction->shipmentPrices|count}
                                {'COM_BIDS_SHIPPING_COSTS'|translate}:
                                {if $bidCfg->bid_opt_multiple_shipping}<br/>{/if}
                                {foreach from=$auction->shipmentPrices item=zitem key=zkey}
                                    {if $zitem->name} {$zitem->name}
                                        - {/if} {print_price auction=$auction price=$zitem->price}
                                    <br/>
                                {/foreach}
                            {/if}
                            </td>
                        </tr>
                    </table>
                </div>
            {/if}
        </td>
        <td style="vertical-align: middle;">
            {if $auction->payment_info!=''}
                <table class="auction_caption_box">
                    <tr>
                        <th>{'COM_BIDS_PAYMENT'|translate}</th>
                    </tr>
                    <tr>
                        <td>
                            {$auction->payment_info}
                        </td>
                    </tr>
                </table>
            {/if}
        </td>
    </tr>
    <tr>
        <td style="vertical-align: bottom;">
            <table class="auction_caption_box" style="">
                <tr>
                    <th colspan="2">{'COM_BIDS_AUTION_TYPE_INFO'|translate}</th>
                </tr>

            {if $bidCfg->bid_opt_enable_countdown && !$auction->expired && !$auction->close_offer}
                <tr>
                    <td colspan="2">
                        {'COM_BIDS_TIME_LEFT'|translate}: <span
                            style="font-size: 14px;"><strong>{$auction->countdownHtml}</strong></span>
                    </td>
                </tr>
            {/if}
            {if $auction->auction_type == $smarty.const.AUCTION_TYPE_PRIVATE}
                <tr>
                    <td colspan="2" class="auction_info">{'COM_BIDS_PRIVATE'|translate}
                        &nbsp;{infobullet text='COM_BIDS_HELP_PRIVATE'}</td>
                </tr>
                {else}
                <tr>
                    <td colspan="2" class="auction_info">{'COM_BIDS_PUBLIC'|translate}
                        &nbsp;{infobullet text='COM_BIDS_HELP_PUBLIC'}</td>
                </tr>
            {/if}

            {if $auction->automatic == 1}
                <tr>
                    <td colspan="2" class="auction_info">{'COM_BIDS_AUTOMATIC'|translate}
                        &nbsp;{infobullet text='COM_BIDS_HELP_AUTOMATIC'}</td>
                </tr>
                {else}
                <tr>
                    <td colspan="2" class="auction_info">{'COM_BIDS_MANUAL'|translate}
                        &nbsp;{infobullet text='COM_BIDS_HELP_MANUAL'}</td>
                </tr>
            {/if}

            {if !$auction->expired && !$auction->close_offer && $auction->extended_counter>0}
                <tr>
                    <td colspan="2">
                        <div style="background:#FF0000;padding:5px; text-align:center;"> {$auction->extended_counter} {'COM_BIDS_TIME_EXTENDED'|translate}</div>
                    </td>
                </tr>
            {/if}
                <tr>
                    <td colspan="2">
                    {'COM_BIDS_START_DATE'|translate}: <strong>{$auction->start_date_text}</strong>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                    {'COM_BIDS_END_DATE'|translate}: <strong>{$auction->end_date_text}</strong>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <strong>{$auction->hits}</strong> {'COM_BIDS_OFFER_HITS'|translate}
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                    {positions position="detail-right" item=$auction page="auctions"}
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<table width="100%">
    <tr>
        <td>
        {positions position="middle" item=$auction page="auctions"}
        </td>
    </tr>
</table>
{positions position="footer" item=$auction page="auctions"}
{* Default Custom Position *}

<div style="display:none">
    <div id="auction_rateit">
        <span class="auction_section_title">{'COM_BIDS_RATE'|translate}: <em>{$auction->title}</em></span>
        <div style="padding:15px;">
            {if !$auction->isMyAuction}
                {'COM_BIDS_HELP_RATE_AUCTIONEER'|translate}
            {else}
                {'COM_BIDS_HELP_RATE_BIDDER'|translate}
            {/if}
        </div>
        <form action="{$ROOT_HOST}index.php" method="post" name="auctionRateForm" onsubmit="return FormValidate(this);">
            <input type="hidden" name="option" value="com_bids" />
            <input type="hidden" name="task" value="rate" />
            <input type="hidden" name="Itemid" value="{$Itemid}" />
            <input type="hidden" name="id" value="{$auction->id}" />
            <input type="hidden" name="auction_id" value="{$auction->id}" />
            <input type="hidden" name="rate" class="auctionRateFormRate" value="0" />
            <table width="50%">
                <tr>
                    <td>
						<div style="margin:5px;">{'COM_BIDS_USER_RATED'|translate}{$lists.userRated}</div>
                        {section name=foo loop=10}
                            <img src="components/com_bids/images/f_rateit_0.png" class="rate_star"
                                rate="{$smarty.section.foo.iteration}" id="rate_star{$smarty.section.foo.iteration}"
                                onclick="dorate({$smarty.section.foo.iteration});"
                                onmouseover="showrate(this,{$smarty.section.foo.iteration});"
                                onmouseout="showrate(this,{$smarty.section.foo.iteration});"/>
                        {/section}
                        <div style="margin:5px;">{'COM_BIDS_COMMENT'|translate}</div>
                        <div style="margin:10px;"><textarea name="comment" cols="40" rows="3" class="inputbox" ></textarea></div>
                        <div><input type="submit" value="{'COM_BIDS_RATE'|translate}" class="auction_button" /></div>
                    </td>
                </tr>
            </table>
        </form>
    </div>
</div>

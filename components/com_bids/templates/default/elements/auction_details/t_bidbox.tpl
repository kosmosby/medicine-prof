{if !$is_logged_in}
    <table class="auction_bid_box auction_warning">
        <tr>
            <td><strong>{'COM_BIDS_LOGIN_TO_BID'|translate}</strong></td>
        </tr>
    </table>
{elseif !$has_profile}
    <table class="auction_bid_box auction_warning">
        <tr>
            <td><strong>{'COM_BIDS_ERR_MORE_USER_DETAILS'|translate}</strong></td>
        </tr>
    </table>
{elseif $auction->isMyAuction}
    <table class="auction_bid_box">
        <tr>
            <td>
                {capture assign="minAcceptedBid"}{print_price price=$auction->minAcceptedPrice nocss="1"}{/capture}
                {'COM_BIDS_MIN_BID_ALERT'|translate:$minAcceptedBid:$auction->currency}<br />
                {if $auction->reserve_price}
                    {'COM_BIDS_RESERVE_ALERT_PRICE'|translate:$auction->reserve_price:$auction->currency}<br />
                {/if}
                {if $bid_list|@count gt 0}
                    <div>
                        <a href="javascript:showMessageBox('auction_bids_history')"><strong>{'COM_BIDS_BIDS_HISTORY'|translate}</strong></a>
                    </div>
                    <div style="display:none">
                        <div id="auction_bids_history">
                            {include file="elements/auction_details/t_bid_list.tpl"}
                        </div>
                    </div>
                {/if}
                {if $auction->auction_type == $smarty.const.AUCTION_TYPE_PUBLIC && $auction->BIN_price>0}
                    {'COM_BIDS_BIN'|translate}: {print_price price=$auction->BIN_price} {$auction->currency}
                {/if}
                 {if $auction->auction_type == $smarty.const.AUCTION_TYPE_BIN_ONLY && $smarty.const.bin_opt_price_suggestion && $auction->params.price_suggest }
                 <div>
                    <a href="javascript:showMessageBox('suggest_list');" >
                        {'COM_BIDS_SHOW_SUGGESTIONS'|translate} ({$suggestions|@count})
                    </a>
                 </div>
                 {/if}

                 {if $auction->winner_list|@count>0}
                 <div>
                     <a href="#bids_winner_list" class="modal" rel="{literal}{handler:'clone'}{/literal}">{'COM_BIDS_WINNER_LIST'|translate} ({$auction->winner_list|@count})</a>
                     <div style="display: none;">
                         <div id="bids_winner_list">
                             <strong>{'COM_BIDS_WINNER_LIST'|translate}</strong>
                              <table style="width: 100%;" class="auction_winner">
                                 <tr>
                                     <th>{'COM_BIDS_BY'|translate}</th>
                                     <th>
                                     {'COM_BIDS_TYPE'|translate}
                                     <br />
                                     {'COM_BIDS_PRICE'|translate}
                                     </th>
                                     <th>{'COM_BIDS_PLACED_ON'|translate}</th>
                                 </tr>
                                 {foreach from=$auction->winner_list item=item key=key}
                                     <tr>
                                         <td>
                                             <strong>{$item->username}</strong>
                                         </td>
                                         <td>
                                             {assign var="button_text" value="OOM_BIDS_BIDTYPE_`$item->bid_type`"}
                                             {$button_text|translate}
                                             <br />
                                             {if $item->quantity}{$item->quantity} x {/if}{print_price auction=$auction price=$item->bid_price}
                                         </td>
                                         <td>{printdate date=$item->modified usehour=1}</td>
                                     </tr>
                                 {/foreach}
                              </table>
                         </div>
                     </div>
                 </div>
                 {/if}
            </td>
        </tr>
    </table>
    {if $bidCfg->bin_opt_price_suggestion && $auction->params.price_suggest}
    <div style="display:none;">
        <div id="suggest_list">
        {include file="elements/auction_details/t_suggest_list.tpl"}
        </div>
    </div>
    <div style="display:none;" id="suggest_box">
    {include file="elements/suggestions/t_suggest_box.tpl"}
    </div>
    {/if}
{elseif $auction->close_offer || $auction->expired}
    <table class="auction_bid_box auction_warning">
        <tr>
            <td align="center">
                {'COM_BIDS_AUCTION'|translate}
                {if $auction->expired}
                    {'COM_BIDS_EXPIRED'|translate} ({$auction->end_date_text})
                {else}
                    {'COM_BIDS_CLOSED'|translate} ({printdate date=$auction->closed_date usehour=1})
                {/if}
            </td>
        </tr>
        {if $auction->iAmWinner}
        <tr>
            <td align="center">
                <img src="{$ROOT_HOST}/components/com_bids/images/you_are_winner.jpg" class="hasTip" title="{'COM_BIDS_ALT_YOU_ARE_WINNER'|translate}" alt="{'COM_BIDS_ALT_YOU_ARE_WINNER'|translate}"/>
                <br /><strong style="font-size:130%;">{'COM_BIDS_ALT_YOU_ARE_WINNER'|translate}</strong>
                <br />{'COM_BIDS_WINNING_BID'|translate}: {print_price price=$auction->winBid} {$auction->currency}
            </td>
        </tr>
        {elseif $auction->auction_type==AUCTION_TYPE_PUBLIC}
            {'COM_BIDS_WINNING_BID'|translate}: {print_price price=$auction->winBid} {$auction->currency}
        {/if}
    </table>
{elseif !$userProfile->isBidder}
    <table class="auction_bid_box auction_warning">
        <tr>
            <td><strong>{'COM_BIDS_YOU_ARE_NOT_BIDDER'|translate}</strong></td>
        </tr>
    </table>
{elseif $auction->auction_type==$smarty.const.AUCTION_TYPE_BIN_ONLY}

    {if $bidCfg->bid_opt_quantity_enabled}
        <div>
            <strong>{'COM_BIDS_BIN_QUANTITY'|translate}:</strong>&nbsp;{$auction->quantity}&nbsp;{'COM_BIDS_QUANTITY_ITEMS'|translate}
            &nbsp;{infobullet text='COM_BIDS_BIN_QUANTITY_HELP'}
        </div>
    {/if}

    <table class="auction_bid_box" width="100%">
        <tr>
            <td>
                <div>
                    {if $auction->nr_bidders!==null}
                        <strong>{'COM_BIDS_NO_BIDDERS'|translate}:</strong>&nbsp;{$auction->nr_bidders}&nbsp;
                    {/if}
                    <a href="javascript:showMessageBox('auction_bids_history')"><strong>{'COM_BIDS_BIDS_HISTORY'|translate}</strong></a>

                    <div style="display:none">
                        <div id="auction_bids_history">
                        {include file="elements/auction_details/t_bid_list.tpl"}
                        </div>
                    </div>
                </div>
                <form action="{$ROOT_HOST}index.php" method="post" name="auctionForm_bin" onsubmit="return MakeBinBid(this,{$auction->BIN_price},'{$auction->currency}');">
                    <input type="hidden" name="option" value="com_bids" />
                    <input type="hidden" name="task" value="bin" />
                    <input type="hidden" name="id" value="{$auction->id}" />
                    <input type="hidden" name="Itemid" value="{$Itemid}" />
                    <input type="hidden" name="{$validate}" value="1" />
                    <input type="hidden" id="system_bin_quantity" name="bin_quantity" value="1" />
                    <input type="hidden" name="quantity" value="1" />
                    {if $auction->quantity>1}
                        <div style="float:left; position: relative;" class="auction_search_field">
                            <div id="spinnerbinBox"></div>
                            <div style="float: left; padding-top: 7px;">&nbsp;{'COM_BIDS_QUANTITY_ITEMS'|translate}</div>
                            <div id="quantity_info">&nbsp;<input type="button" id="bin_button" class="auction_button_BINq"
                                    value="{'COM_BIDS_BUY_IT_NOW'|translate} {print_price price=$auction->BIN_price
                                    nocss="1"}{$auction->currency}" name="bin_button"
                                    onclick="preCheckBin();" />
                            </div>
                        </div>
                    {/if}
                    <div style="clear: both;">&nbsp;</div>
                </form>
            </td>
        </tr>
        {if $bidCfg->bin_opt_price_suggestion && $auction->params.price_suggest }
        <tr>
            <td>
                <a href="javascript:showMessageBox('suggest_list');" >
                    {'COM_BIDS_SHOW_SUGGESTIONS'|translate} ({$suggestions|@count})
                </a>
                <br />
                {if !($auction->iAmWinner && $auction->close_offer) }
                    <a href="javascript:showMessageInline('suggest_box');" >
                        {'COM_BIDS_ADD_SUGGESTIONS'|translate}
                    </a>
                {/if}

            </td>
        </tr>
        {/if}
        {if $auction->iAmWinner && $auction->quantity>1}
        <tr>
            <td>
                <div style="font-size: 12px; color: #ff7700; text-align: center; font-weight: bold;">
                    {'COM_BIDS_YOU_HAVE_PURCHASED_X_ITEMS'|translate:$lists.myPurchasedItems}
                </div>
            </td>
        </tr>
        {/if}
        {if $auction->winner_list|@count>0}
        <tr>
            <td>
                <a href="#bids_winner_list" class="modal" rel="{literal}{handler:'clone'}{/literal}">{'COM_BIDS_WINNER_LIST'|translate} ({$auction->winner_list|@count})</a>
                <div style="display: none;">
                    <div id="bids_winner_list">
                        <strong>{'COM_BIDS_WINNER_LIST'|translate}</strong>
                         <table style="width: 100%;">
                            <tr>
                                <th>{'COM_BIDS_BY'|translate}</th>
                                <th>
                                    {'COM_BIDS_TYPE'|translate}
                                    <br />
                                    {'COM_BIDS_PRICE'|translate}
                                </th>
                                <th>{'COM_BIDS_PLACED_ON'|translate}</th>
                            </tr>
                            {foreach from=$auction->winner_list item=item key=key}
                            <tr>
                                <td>
                                    <strong>{$item->username}</strong>
                                </td>
                                <td>
                                    {assign var="button_text" value="OOM_BIDS_BIDTYPE_`$item->bid_type`"}
                                    {$button_text|translate}
                                    <br />
                                    {if $item->quantity}{$item->quantity} x {/if}{print_price auction=$auction price=$item->bid_price}
                                </td>
                                <td>{printdate date=$item->modified usehour=1}</td>
                            </tr>
                            {/foreach}
                         </table>
                    </div>
                </div>
            </td>
         </tr>
        {/if}
    </table>
    {if $bidCfg->bin_opt_price_suggestion && $auction->params.price_suggest}
    <div style="display:none;">
        <div id="suggest_list">
        {include file="elements/auction_details/t_suggest_list.tpl"}
        </div>
    </div>
    <div style="display:none;" id="suggest_box">
    {include file="elements/suggestions/t_suggest_box.tpl"}
    </div>
    {/if}
{else}
    <form action="{$ROOT_HOST}index.php" method="post" name="auctionForm2" onsubmit="return FormValidate(this);">
        <table class="auction_bid_box">
                <input type="hidden" name="option" value="com_bids" />
                <input type="hidden" name="task" value="sendbid" />
                <input type="hidden" name="id" value="{$auction->id}" />
                <input type="hidden" name="initial_price" value="{$auction->initial_price}" />
                <input type="hidden" name="bin_price" value="{if $bidCfg->bid_opt_global_enable_bin || $bidCfg->bid_opt_enable_bin_only}{$auction->BIN_price}{/if}" />
                <input type="hidden" name="mylastbid" value="{$auction->myBid->bid_price}" />
                <input type="hidden" name="min_increase" value="{$auction->minIncrement}" />
                <input type="hidden" name="Itemid" value="{$Itemid}" />
                <input type="hidden" name="maxbid" value="{$auction->highestBid->bid_price}" />
                <input type="hidden" name="{$validate}" value="1" />
                <input type="hidden" name="proxy" value="0" />
                <tr>
                    <td>{'COM_BIDS_EXACTLY'|translate}:</td>
                    <td><input name="amount" class="inputbox" type="text" value="" size="3" alt="bid" {$disable_bids}>&nbsp;{$auction->currency}&nbsp;</td>
                    <td>
                        <span class="auction_search_field">
                            <input type="submit" name="send" value="{'COM_BIDS_BUT_SEND_BID'|translate}" class="auction_button_BINq" />
                        </span>

                    </td>
                </tr>
                {if $bidCfg->bid_opt_allow_proxy}
                    <tr>
                        <td colspan="3">
                            <input type="checkbox" name="proxy" id="bid_as_proxy" value="1" />
                            <label for="bid_as_proxy">{'COM_BIDS_BID_AS_PROXY'|translate}</label>
                        </td>
                    </tr>
                {/if}
                <tr>
                    <td colspan="3">
                        <div class="auction_grey_text">
                            {capture assign="minAcceptedBid"}{print_price price=$auction->minAcceptedPrice nocss="1"}{/capture}
                            {'COM_BIDS_MIN_BID_ALERT'|translate:$minAcceptedBid:$auction->currency}
                        </div>
                    </td>
                </tr>
                {if $bidCfg->bid_opt_global_enable_reserve_price && $auction->reserve_price > 0 }
                    <tr>
                        <td colspan="3">
                            <div class="auction_grey_text">
                                {if $auction->reserve_price > $auction->minAcceptedPrice}
                                    {if $auction->params.show_reserve}
                                        {'COM_BIDS_RESERVE_ALERT_PRICE'|translate:$auction->reserve_price:$auction->currency}
                                    {else}
                                        {'COM_BIDS_RESERVE_ALERT'|translate}
                                    {/if}
                                {/if}
                            </div>
                        </td>
                    </tr>
                {/if}
        </table>
    </form>
    <div>
        {if $auction->nr_bidders!==null}
            <strong>{'COM_BIDS_NO_BIDDERS'|translate}:</strong>&nbsp;{$auction->nr_bidders}&nbsp;
        {/if}
        {if $auction->auction_type!=$smarty.const.AUCTION_TYPE_PRIVATE}
            <a href="javascript:showMessageBox('auction_bids_history')"><strong>{'COM_BIDS_BIDS_HISTORY'|translate}</strong></a>

            <div style="display:none">
                <div id="auction_bids_history">
                {include file="elements/auction_details/t_bid_list.tpl"}
                </div>
            </div>
        {/if}
    </div>

    <div>&nbsp;</div>

    {if ($bidCfg->bid_opt_global_enable_bin || $bidCfg->bid_opt_enable_bin_only) && $auction->BIN_price>0}
        <form action="{$ROOT_HOST}index.php" method="post" name="auctionForm_bin"
              onsubmit="return MakeBinBid(this,{$auction->BIN_price},'{$auction->currency}');">
            <table style="width: 100%;">
                <tr>
                    <td colspan="3" align="center">

                        <input type="hidden" name="option" value="com_bids"/>
                        <input type="hidden" name="task" value="bin"/>
                        <input type="hidden" name="id" value="{$auction->id}"/>
                        <input type="hidden" name="Itemid" value="{$Itemid}"/>
                        <input type="hidden" name="{$validate}" value="1"/>
                        <input type="hidden" id="system_bin_quantity" name="bin_quantity" value="1"/>
                        <input type="hidden" name="quantity" value="1"/>
                        <div class="auction_search_field">
                            <input type="button" id="bin_button" class="auction_button_BINq"
                                   value="{'COM_BIDS_BUY_IT_NOW'|translate} - {print_price auction=$auction price=$auction->BIN_price nocss=1}"
                                   name="bin_button" {$disable_bids}
                                   onclick="preCheckBin();"/>
                        </div>


                    </td>
                </tr>
            </table>
        </form>
    {/if}
{/if}

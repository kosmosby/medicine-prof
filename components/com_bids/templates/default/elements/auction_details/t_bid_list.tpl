<div class="auction_section_title">{'COM_BIDS_BIDS_LIST_AUCTION'|translate}: <em>{$auction->title}</em></div>
{if !$cfg->bid_opt_manual_accept_before_end && $auction->isMyAuction}
    {'COM_BIDS_MSG_NO_ACCEPT_BEFORE_AUCTION_END'|translate}
{/if}
<table width="100%" border="0" class="auction_bids_list">
    <tbody>
        <tr>
            <th class="auction_bids_list" width="5%">{'COM_BIDS_NO_SHORT'|translate}</th>
            <th class="auction_bids_list" width="20%">{'COM_BIDS_DATE'|translate}</th>
            <th class="auction_bids_list" width="*%">{'COM_BIDS_USERNAME'|translate}</th>
            <th class="auction_bids_list" width="20%">{'COM_BIDS_BIDED_AMOUNT'|translate}</th>
            {if ($cfg->bid_opt_manual_accept_before_end || $auction->close_offer) && ( $auction->isMyAuction || $auction->iAmBidder ) && !$auction->automatic }
            <th class="auction_bids_list" width="10%">&nbsp;</th>
            {/if}
            {if $auction->my_proxy_bid}
              <th class="auction_bids_list" width="20%">{'COM_BIDS_MY_PROXY'|translate}</th>
            {/if}
        </tr>
        {foreach from=$bid_history item=bid name=bids}
            {assign var="tr_class" value=""}
            {if $bid->userid==$userid}
                {assign var="tr_class" value="auction_bids_mybid1"}
                <a name='mybid' id='mybid'></a>
            {/if}
            {if $bid->accept}
                {assign var="tr_class" value="auction_winner"}
            {/if}
            <tr class="{cycle values=auction_bids_list1,auction_bids_list2} {$tr_class}">
                <td>{$smarty.foreach.bids.iteration}</td>
                <td>{$bid->modified}</td>
                <td>
                    <a href="index.php?option=com_bids&task=userdetails&id={$bid->userid}&Itemid={$Itemid}" class="hasTip" title="{'COM_BIDS_VIEW_USER_PROFILE'|translate}">{$bid->username}</a>
                    {if $auction->isMyAuction && $bidCfg->bid_opt_allow_messages}
                        <a href="javascript:void(0);" id="sendm" onclick="showSendMessages('{$bid->username}',0,{$bid->userid});">({'COM_BIDS_BUT_SEND_MESSAGE'|translate})</a>
                    {/if}
                </td>
                <td>
                    {print_price auction=$auction price=$bid->bid_price}
                    {if $auction->nr_items >1}
                        {math equation='x*y' x=$bid->bid_price y=$bid->quantity assign=price}
                        x {$bid->quantity} = {print_price price=$price}&nbsp;{$auction->currency}{/if}
                    {if  $bidCfg->bid_opt_global_enable_reserve_price && $auction->reserve_price>0 && ( $auction->params.show_reserve || $auction->isMyAuction) }
                       {if $auction->reserve_price+0 > $bid->bid_price+0 }
                            <img style="margin:0px;" src="{$ROOT_HOST}components/com_bids/images/reserve_price_not_ok.png" border="0" alt="{'COM_BIDS_RESERVE_NOT_MET'|translate}" title="{'COM_BIDS_RESERVE_NOT_MET'|translate}"/>
                       {else}
                           <img style="margin:0px;" src="{$ROOT_HOST}components/com_bids/images/reserve_price_ok.png" border="0"  alt="{'COM_BIDS_RESERVE_MET'|translate}" title="{'COM_BIDS_RESERVE_MET'|translate}"/>
                       {/if}
                    {/if}
                </td>
                {if ($cfg->bid_opt_manual_accept_before_end || $auction->close_offer) && ( $auction->isMyAuction || $auction->iAmBidder ) && !$auction->automatic}
                    <td>
                        {if $auction->isMyAuction && !($bid->bid_type=='bin' && $bid->accept==1 ) && !$auction->accepted && $auction->auction_type!=$smarty.const.AUCTION_TYPE_PRIVATE }
                            <a href="index.php?option=com_bids&task=accept&bid={$bid->id}&Itemid={$Itemid}" onclick="return confirm('{'COM_BIDS_CONFIRM_ACCEPT_BID'|translate}');">
                                <img src="{$ROOT_HOST}components/com_bids/images/auctionicon16.gif" border="0" />{'COM_BIDS_ACCEPT'|translate}
                            </a>
                        {elseif $bid->bid_type=='bin' && $bid->accept==1}
                            {'COM_BIDS_ACCEPTED'|translate}
                        {/if}
                    </td>
                {/if}
              {if $auction->my_proxy_bid}
                {if $bid->userid==$userid}
                    <td class="auction_my_proxy">{print_price price=$auction->my_proxy_bid}&nbsp;{$auction->currency}</td>
                {else}
                    <td>&nbsp;</td>
                {/if}
              {/if}
            </tr>
        {foreachelse}
            <h2>
            {if $auction->auction_type == $smarty.const.AUCTION_TYPE_PRIVATE}
                {'COM_BIDS_YOU_HAVE_NO_BIDS'|translate}
            {else}
                {'COM_BIDS_NO_USER_BIDS'|translate}
            {/if}
            </h2>
        {/foreach}

    </tbody>
</table>

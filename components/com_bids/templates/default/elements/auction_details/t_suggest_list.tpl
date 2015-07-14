{if $auction->isMyAuction || $auction->iAmWinner || !$auction->close_offer || !$auction->expired}
   {* ensure it is not my auction and the auction still runs*}

    <table width="100%">
        <tr>
            <th class="auction_bids_list" width="5%" align="center" >#</th>
            <th class="auction_bids_list" width="20%" align="center" >{'COM_BIDS_USERNAME'|translate}</th>
            <th class="auction_bids_list" align="center" >{'COM_BIDS_SUGGEST_PRICE'|translate}</th>
            <th class="auction_bids_list" width="25%" align="center" >{'COM_BIDS_DATE'|translate}</th>
            <th class="auction_bids_list" width="20%" align="center" > {'COM_BIDS_STATUS'|translate} </th>
        </tr>
        {foreach from=$suggestions item=suggestion key=k}

            {assign var="tr_class" value="auction_bids_list" }
            {if $auction->userid==$userid}
                {assign var="tr_class" value="auction_bids_mybid" }
            {/if}
            {if $suggestion->status=="1"}
                {assign var="tr_class" value="auction_winner"}
            {/if}
            <tr class="{$tr_class}{cycle values='1,2'}">
                <td align="center">{$k+1}</td>
                <td align="center">
                    {if $suggestion->parent_id}
                        {'COM_BIDS_REPLY'|translate}
                    {/if}
                    <a href="{$ROOT_HOST}/index.php?option=com_bids&task=userdetails&id={$suggestion->userid}&Itemid={$Itemid}"  title="{'COM_BIDS_VIEW_USER_PROFILE'|translate}" class="hasTip">
                        {$suggestion->username}
                    </a>
                </td>
                <td align="center">{$suggestion->quantity} x {print_price price=$suggestion->bid_price}{$auction->currency}</td>
                <td align="center">{printdate date=$suggestion->modified}</td>
                <td align="center">
                    {if !$auction->automatic && ( $suggestion->repliedto|default:$auction->userid == $userProfile->id ) &&  $suggestion->status =="2" && !$auction->close_offer }
                        <a href="{$ROOT_HOST}/index.php?option=com_bids&task=acceptsuggestion&id={$suggestion->id}&Itemid={$Itemid}" onclick="return confirm('{'COM_BIDS_CONFIRM_ACCEPT_SUGGEST'|translate}');">
                            {'COM_BIDS_ACCEPT'|translate}
                        </a>
                        /
                        <a href="{$ROOT_HOST}/index.php?option=com_bids&task=rejectsuggestion&id={$suggestion->id}&Itemid={$Itemid}" onclick="return confirm('{'COM_BIDS_CONFIRM_REJECT_SUGGEST'|translate}');">
                            {'COM_BIDS_REJECT'|translate}
                        </a>
                    {else}
                        {if $suggestion->status==0}
                            {'COM_BIDS_SUGGEST_STATUS_REJECTED'|translate}
                        {elseif $suggestion->status==1}
                            {'COM_BIDS_SUGGEST_STATUS_ACCEPTED'|translate}
                        {else}
                            {'COM_BIDS_SUGGEST_STATUS_PENDING'|translate}
                        {/if}
                    {/if}
                </td>
            </tr>
        {foreachelse}
            <tr>
                <td colspan="5">&nbsp;</td>
            </tr>
            <tr>
                <td colspan="5">{'COM_BIDS_NO_SUGGESTION'|translate}</td>
            </tr>
        {/foreach}
    </table>
{/if}
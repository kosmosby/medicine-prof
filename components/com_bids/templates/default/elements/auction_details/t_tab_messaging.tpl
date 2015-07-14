<table width="100%" cellpadding="0" cellspacing="0">
{if $auction->isMyAuction && !$message_list|count}
    <tr>
        <td colspan="2"><strong>{'COM_BIDS_NO_MESSAGES'|translate}</strong><a id="mess"></a></td>
    </tr>
{/if}

{if !$auction->isMyAuction}
    <tr>
        <td colspan="2">
            <div class="auction_search_field">
                <strong>{'COM_BIDS_ASK_SELLER'|translate}</strong>&nbsp;&nbsp;&nbsp;
                <input type="button" class="auction_button_BINq" value="{'COM_BIDS_ASK_NOW'|translate}" onclick="showSendMessages('{$auctioneer->username}')"/>
            </div>
            <div>&nbsp;</div>
        </td>
    </tr>
{/if}


{foreach item=message from=$message_list}
<tr>
    <td colspan="2">
        <hr class="auction_hr"/>
    </td>
</tr>
<tr class="auction_message_row {if $message->userid1==$auctioneer->id}auction_message_answer{/if}">
    <td nowrap="" width="100" style="text-align: right; padding-right: 20px; vertical-align: top;">
        <strong>
            {if $message->fromuser}
                <a href="index.php?option=com_bids&task=userdetails&id={$message->userid1}">
                    {if $message->userid1!=$userProfile->id}
                        {$message->fromuser}
                    {else}
                        {'COM_BIDS_ME'|translate}
                    {/if}
                </a>
            {else}
                {'COM_BIDS_FROM_GUEST'|translate}
            {/if}
        </strong>
    </td>
    <td width="*%">
        <div class="auction_msg_text">
            {$message->message|stripslashes}
            {if $auction->isMyAuction && $message->userid1!=$auctioneer->id}
                <a href="javascript:void(0);" id="auction_message_reply" onclick="showSendMessages('{$message->fromuser}',{$message->id});">{'COM_BIDS_REPLY'|translate}</a>
            {/if}
        </div>
    </td>
</tr>
{/foreach}


</table>

<div style="display:none" id="bid_message_form">
    <div id="auction_message_form_div">
        <form action="{$ROOT_HOST}index.php" method="POST" name="messageForm">
            <input type="hidden" name="option" value="com_bids" />
            <input type="hidden" name="task" value="savemessage" />
            <input type="hidden" name="id" value="{$auction->id}" />
            <input type="hidden" name="Itemid" value="{$Itemid}" />
            <input type="hidden" name="idmsg" id="idmsg" value="" />
            <input type="hidden" name="bidder_id" id="bidder_id" value="" />
            <table>
                <tr>
                    <td>
                        {'COM_BIDS_MESSAGE'|translate}:&nbsp;<span id="auction_message_to"></span><br />
                        <textarea class="inputbox" name="message" id="auction_message" rows="15" cols="50"></textarea>
                    </td>
                </tr>
                {if !$is_logged_in && $bidCfg->bid_opt_enable_captcha}
                <tr>
                    <td>
                        <table>
                        <tr>
                            <td>
                                {$cs}
                            </td>
                            <td>
                                {'COM_BIDS_CAPTCHA'|translate}
                            </td>
                        </tr>
                        </table>
                    </td>
                </tr>
                {/if}
                <tr>
                    <td>
                        <input type="submit" name="send" value="{'COM_BIDS_BUT_SEND_MESSAGE'|translate}" class="auction_button" />
                    </td>
                </tr>
            </table>
        </form>
    </div>
</div>

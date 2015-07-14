{include file="../configs/config_template.tpl"}
{include file='snippets/t_javascript_language.tpl'}
{include file='elements/auction_details/t_detail_header.tpl'}
{include file='elements/auction_details/t_auctiondetails_js.tpl'}

{if $payment_items_header}
    <div class="bids_headerinfo">{$payment_items_header}</div>
{/if}
<table width="100%"  class="auction_details_container">
    <tr>
        <td style="width: 50%; text-align: left;">
            <img src="components/com_bids/images/rfolder.gif" alt="" style="vertical-align: middle;" />
            {if $auction->categoryname}
                <a href="{$auction->links.filter_cat}">{$auction->categoryname}</a>
            {else}
                &nbsp;-&nbsp;
            {/if}
        </td>
        <td style="width: 50%; text-align: right;">
            <div style="line-height: 15px;">
                <a href="{$ROOT_HOST}index.php?option=com_bids&task=listauctions" style="text-decoration: none;">
                    <img style="vertical-align: bottom" height="16"
                         src="{$ROOT_HOST}components/com_bids/images/back.png"
                         border="0"/>
                </a>
                <a href="{$ROOT_HOST}index.php?option=com_bids&task=listauctions">
                    <span style="padding-bottom: 5px">{'COM_BIDS_BACK_TO_LIST'|translate}</span>
                </a>
            </div>
        </td>
    </tr>
    <tr>
        <td style="width: 70%; text-align: left; vertical-align: top; padding-top: 4px;">
            <div class="auction_title">{$auction->title}</div>
        </td>
        <td style="width: 30%; text-align: right;">
            <div class="cleardiv">&nbsp;</div>
            {if !$auction->close_by_admin}
                <div class="auction_actions">
                    {if $lists.auctionFbLikeButton}
                        <span class="auction_action_button" style="width:90px;">
                            {$lists.auctionFbLikeButton}
                        </span>
                    {/if}
                    {include file='elements/auction_details/t_auction_actions.tpl'}
                    {include file='elements/auction_details/t_edit_cancel_buttons.tpl'}
                    {if $auction->iAmWinner && $lists.bidderPaypalButton}
                        <span class="auction_action_button paypal">
                            {$lists.bidderPaypalButton}
                        </span>
                    {/if}
                </div>
            {/if}
            <div class="cleardiv">&nbsp;</div>
        </td>
    </tr>

    <tr>
        <td style="width: 70%; text-align: left;">
            <div class="auction_detail_itemident">
                {'COM_BIDS_STATUS'|translate}: <strong>{$auction->auction_status}</strong>
            </div>
        </td>
        <td style="width: 30%; text-align: right;">
            <div class="auction_detail_itemident" style="float: right;">
                {'COM_BIDS_AUCTION_REF'|translate}: {$auction->auction_nr}<br/>
            </div>
        </td>
    </tr>

    <tr>
        <td colspan="2" style="height:8px;"></td>
    </tr>

    <tr>
        <td colspan="2">
            {include file='elements/auction_details/t_tab_item_details.tpl'}
        </td>
    </tr>
    {* -------------------  TABBING PART ---------------------*}
    <tr>
        <td colspan="2">
            {startpane id="content-pane2" usecookies=0}
                {starttab paneid="tab1" text='COM_BIDS_TAB_AUCTION_DESCRIPTION'}
                    <div>
                        {$auction->description}
                    </div>
                    <br />
                    <div>
                        {if $lists.tagLinks}
                            <strong>
                                {'COM_BIDS_TAGS'|translate}:
                            </strong>
                            {$lists.tagLinks}
                        {/if}
                    </div>
                {endtab}
                {if $bidCfg->bid_opt_allow_messages && ($is_logged_in || $bidCfg->bid_opt_allow_guest_messaging)}
                    {starttab paneid="tab2" text='COM_BIDS_TAB_AUCTION_MESSAGES'}
                        {include file='elements/auction_details/t_tab_messaging.tpl'}
                    {endtab}
                {/if}
                {starttab paneid="tab3" text='COM_BIDS_TAB_OTHER_ITEMS_SELLER'}
                    {include file='elements/auction_details/t_tab_other_items.tpl'}
                {endtab}
            {endpane}
        </td>
    </tr>
</table>


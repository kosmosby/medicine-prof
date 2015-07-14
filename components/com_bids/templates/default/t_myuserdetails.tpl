{include file="../configs/config_template.tpl"}
{set_css}

{include file='snippets/t_javascript_language.tpl'}

{include file='elements/myuserdetails/t_myuserdetails_js.tpl'}



<div class="auction_profile_page">

    {startpane id="account_information"}

        {starttab paneid="acc_info" text='COM_BIDS_ACCOUNT_INFO'|translate}
            <div style="float: left; width: 50%;">
                <fieldset class="auction_profile_box">
                    <legend>{'COM_BIDS_USER_PROFILE'|translate}&nbsp;&nbsp;&nbsp;{$lists.linkEditProfile}</legend>
                    {include file='elements/myuserdetails/t_display_userprofile.tpl'}
                </fieldset>
            </div>
            {if $has_profile}
                <div style="float: left; width: 50%;">
                    <fieldset class="auction_profile_box">
                        <legend>{'COM_BIDS_RECENT_RATINGS'|translate}</legend>
                        {include file='elements/myuserdetails/t_myratingstable.tpl' ratings=$lists.ratings}
                    </fieldset>
                </div>
                <div style="height: 80px; float: left">&nbsp;</div>
                <div style="float:right">
                    {$lists.fbLikeButton}
                </div>
                <div style="clear: both;">&nbsp;</div>

                <fieldset class="auction_profile_box">
                    <legend>{"COM_BIDS_PAYMENT_BALANCE"|translate}</legend>
                    <div class="auction_credits">
                        <strong>{"COM_BIDS_YOUR_CURRENT_BALANCE_IS"|translate}:</strong>
                        <span class="bids_price">{$lists.balance->balance}</span>&nbsp;{$lists.balance->currency}
                    </div>
                    <div>
                        <a href="{$lists.links.upload_funds}">{"COM_BIDS_ADD_FUNDS_TO_YOUR_BALANCE"|translate}</a><br/>
                        <a href="{$lists.links.payment_history}">{"COM_BIDS_SEE_MY_PAYMENTS_HISTORY"|translate}</a>
                    </div>
                </fieldset>
            {/if}
            <div style="clear: both;">&nbsp;</div>
        {if $bidCfg->terms_and_conditions}
            <div>
                <form action="index.php" name="">
                    <input type="hidden" name="option" value="com_bids" />
                    <input type="hidden" name="task" value="agreeTC" />
                    <input type="checkbox" name="agreetc" value="1" {if $user->agree_tc}checked="checked"{/if} id="agreetc">
                    <label for="agreetc">
                        {'COM_BIDS_AGREE_TC'|translate}
                        <a href="{$lists.linktc}" class="modal" rel="{literal}{handler:'iframe'}{/literal}">{'COM_BIDS_T_AND_C'|translate}</a>
                    </label>
                    <input type="submit" value="{'COM_BIDS_AGREE_BUTTON'|translate}">
                </form>
            </div>
        {/if}
        {starttab paneid="messages" text='COM_BIDS_MESSAGES_RECEIVED'|translate}
            {include file='elements/myuserdetails/t_message_inbox.tpl'}
        {if $bidCfg->bid_opt_allow_user_settings}
        {starttab paneid="settings" text='COM_BIDS_DEFAULT_SETTINGS'|translate}
            {include file='elements/myuserdetails/t_edit_defaultsettings.tpl'}
        {/if}
    {endpane}

</div>


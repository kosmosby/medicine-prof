{include file="../configs/config_template.tpl"}
{set_css}
{init_behavior type='modal'}
{include file='snippets/t_javascript_language.tpl'}
{include file='elements/myuserdetails/t_myuserdetails_js.tpl'}

<div class="auction_profile_page">

    {startpane id="account_information"}

        {starttab paneid="acc_info" text='COM_BIDS_ACCOUNT_INFO'|translate}

            {positions position="header" item=$user page="user_profile"}

            <div style="float: left; width: 50%;">
                <fieldset class="auction_profile_box">
                    <legend>{'COM_BIDS_USER_PROFILE'|translate}</legend>
                    {include file='elements/myuserdetails/t_display_userprofile.tpl'}
                </fieldset>
            </div>
            <div style="float: left; width: 50%;">
                <fieldset class="auction_profile_box">
                    <legend>{'COM_BIDS_RECENT_RATINGS'|translate}</legend>
                    {include file='elements/myuserdetails/t_myratingstable.tpl' ratings=$lists.ratings}
                    <div class="div_spacer"></div>
                    {capture name="userratingslink"}index.php?option=com_bids&task=userratings&userid={$user->userid}&Itemid={$Itemid}{/capture}
                    <a href="{jroute url=$smarty.capture.userratingslink}">{'COM_BIDS_VIEW_ALL_RATINGS'|translate}</a>
                </fieldset>
            </div>
            <div style="height: 80px; float: left">&nbsp;</div>
            <div style="float:right">
                {$lists.fbLikeButton}
            </div>
            <div style="clear: both;">&nbsp;</div>

            {positions position="bottom" item=$user page="user_profile"}

    {endpane}

</div>

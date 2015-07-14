
{include file='snippets/t_javascript_language.tpl'}
{include file="../configs/config_template.tpl"}
{import_js_file url="`$ROOT_HOST`components/com_bids/js/ratings.js"}
{set_css}
<h2><strong>{$page_title}</strong></h2>
<form action="{$ROOT_HOST}/index.php?option=com_bids&task={$task}&Itemid={$Itemid}" method="get" name="bidsForm">
<input type="hidden" name="option" value="com_bids">
<input type="hidden" name="task" value="{$task}">
<input type="hidden" name="Itemid" value="{$Itemid}">

<div>
{foreach from=$users item=user}
    <div>
        {positions position="header" item=$user page="user_profile"}
    </div>
	<div class="myrating_user">
		<a href="{$user->link}" style="font-size: 20px;">
			{$user->username}
        </a>
        &nbsp;
        <a href="index.php?option=com_bids&task=rss&user={$user->id}&format=raw" target="_blank">
           <img src="{$ROOT_HOST}components/com_bids/images/f_rss.jpg" width="10" border="0">
        </a>
        &nbsp;
        {if $user->verified}
            <img src="{$ROOT_HOST}components/com_bids/images/user/verified_1.gif"  id='auction_star' height="16" border="0" title="{'COM_BIDS_USER_VERIFIED'|translate}" class="hasTip"/>
        {/if}
        {if $user->powerseller}
            <img src="{$ROOT_HOST}components/com_bids/images/user/powerseller1.png"  id='auction_star' height="16" border="0" title="{'COM_BIDS_USER_POWERSELLER'|translate}" class="hasTip"/>
        {/if}
        {if $user->isSeller}
            <img src="{$ROOT_HOST}components/com_bids/images/user/f_can_sell1.gif"  id='auction_star' height="16" border="0" title="{'COM_BIDS_USER_SELLER'|translate}" class="hasTip"/>
        {/if}
        {if $user->isBidder}
            <img src="{$ROOT_HOST}components/com_bids/images/user/f_can_buy1.gif"  id='auction_star' height="16" border="0" title="{'COM_BIDS_USER_BUYER'|translate}" class="hasTip"/>
        {/if}
        <br />
        {'COM_BIDS_NO_AUCTIONS'|translate}: {$user->nr_auctions}<br />
        {'COM_BIDS_NO_BIDS_PLACED'|translate}: {$user->nr_bids}
        <br />
        <div style="float: right; position: relative; margin-top: -50px;">
            {'COM_BIDS_RATING'|translate}:<span class="rating_user" rating="{$user->rating_overall}"></span>
        </div>
        <div>
            {positions position="details" item=$user page="user_profile"}
        </div>
        
	</div>

{foreachelse}
    {'COM_BIDS_SEARCH_NO_USERS'|translate}
{/foreach}
</div>
{if $users|count}
<table align="center" width="54%">
    <tr>
        <td>
        {"COM_BIDS_PN_DISPLAY_NR"|translate} {$pagination->getLimitBox()}
        </td>
        <td class="sectiontablefooter" style="font-weight: bold;">
        {$pagination->getPagesCounter()}
        </td>
        <td align="center" style="font-weight: bold;">
        {$pagination->getPagesLinks()}<br />
        </td>
    </tr>
</table>
{/if}
</form>

{include file="../configs/config_template.tpl"}
{* Include Validation script *}
{include file='snippets/t_javascript_language.tpl'}
{assign var=required_image value="`$ROOT_HOST`components/com_bids/images/requiredfield.gif"}
{assign var=required_image value="<img src='`$required_image`' alt='' border=0 title='required'>"}
{* set the custom Auction CSS & Template CSS - after tabbing so we replace css in tabbed output *}
{set_css}
{init_behavior type='formvalidation'}
{init_behavior type='tooltip'}

{import_js_file url="`$ROOT_HOST`/components/com_bids/js/multifile.js"}

{import_js_file url="`$ROOT_HOST`/components/com_bids/js/date.js"}
{import_js_file url="`$ROOT_HOST`/components/com_bids/js/auction_edit.js"}
{import_js_file url="`$ROOT_HOST`/components/com_bids/js/auctions.js"}

{if $payment_items_header}
    <div class="bids_headerinfo">{$payment_items_header}</div>
{/if}

<form action="{$ROOT_HOST}index.php" method="post" name="auctionForm" id="auctionForm" enctype="multipart/form-data" onsubmit="return validateForm(this);">
    {if $auction->oldid}
	    <input type="hidden" name="oldid" value="{$auction->oldid}" />
    {/if}
    <input type="hidden" name="id" value="{$auction->id}" />
    <input type="hidden" name="option" value="{$option}" />
    <input type="hidden" name="task" value="saveauction" />
    <input type="hidden" name="has_custom_fields_with_cat" value="{$custom_fields_with_cat}" />
<div style="float: left;">
    <div class="auction_header">
        {$lists.editFormTitle}
    </div>
    <div class="auction_info_legend">{'COM_BIDS_REQUIRED_FIELDS_INFO'|translate} {$required_image}</div>
</div>

<div style="float: right;">
	<input type="submit" name="save" value="{'COM_BIDS_BUT_SAVE'|translate}" class="auction_button save" />
    <input type="button" class="auction_button cancel" name="cancel" value="{'COM_BIDS_CANCEL'|translate}" class="auction_button" onclick="if ( confirm('{'COM_BIDS_CONFIRM_CANCEL_AUCTION'|translate}')) {literal}{document.auctionForm.task.value='canceledit';document.auctionForm.submit();}{/literal} " />
	{if $bidCfg->bid_opt_allow_user_settings}
        <span class="auction_info_small">
		  <input type="checkbox" name="save_user_settings" value="1" /> {'COM_BIDS_SAVE_DEFAULT'|translate} {infobullet text='COM_BIDS_SAVE_USER_FIELDS_HELP'}
        </span>
	{/if}
</div>
<div style="clear: both;"></div>
<div class="div_spacer"></div>

{if $auction->auction_type==$smarty.const.AUCTION_TYPE_BIN_ONLY}
    {assign var=hide_prices value="style='display:none;'"}
    {else}
    {assign var=hide_suggest value="style='display:none;'"}
{/if}
{if $auction->BIN_price<=0}
    {assign var=hide_bin value="style='display:none;'"}
{/if}

{include file='snippets/t_title_band.tpl' title="COM_BIDS_TAB_OFFER_DETAILS" }
{include file='elements/editauction/t_auction_detail.tpl'}
<div class="div_spacer"></div>

{include file='snippets/t_title_band.tpl' title="COM_BIDS_TAB_DESCRIPTION"}
{include file='elements/editauction/t_pictures_upload.tpl'}
<div class="div_spacer"></div>

<div style="text-align: right;">
	<input type="submit" name="save" value="{'COM_BIDS_BUT_SAVE'|translate}" class="auction_button save" />
    <input type="button" class="auction_button cancel" name="cancel" value="{'COM_BIDS_CANCEL'|translate}"
           class="auction_button"
           onclick="if ( confirm('{'COM_BIDS_CONFIRM_CANCEL_AUCTION'|translate}')) {literal}{document.auctionForm.task.value='canceledit';document.auctionForm.submit();}{/literal} "/>
	{if $bidCfg->bid_opt_allow_user_settings}
        <span class="auction_info_small">
		  <input type="checkbox" name="save_user_settings" value="1" /> {'COM_BIDS_SAVE_DEFAULT'|translate} {infobullet text='COM_BIDS_SAVE_USER_FIELDS_HELP'}
        </span>
	{/if}
</div>

<br clear="all" />
</form>

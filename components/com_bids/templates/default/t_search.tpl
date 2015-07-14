{include file="../configs/config_template.tpl"}
{* Include Validation script *}
{include file='snippets/t_javascript_language.tpl'}
{* set the custom Auction CSS & Template CSS - after tabbing so we replace css in tabbed output*}
{set_css}

{include file='elements/search/t_search_mode.tpl'}

<div class="auction_header">{'COM_BIDS_SEARCH_AUCTIONS_TITLE'|translate}</div>
<form action="{$ROOT_HOST}index.php" method="get" name="auctionForm" >
    <input type="hidden" name="task" value="showSearchResults" />
    <input type="hidden" name="option" value="com_bids" />
    <input type="hidden" name="Itemid" value="{$Itemid}" />
    {$lists.inputReset}

    
    <div class="auction_search_outer">
        <div class="auction_search_inner">

            <table style="width: 100%;">

                <tr>
                    <td style="width:150px;">
                        <label class="auction_lables">{"COM_BIDS_SEARCH_IPT_TITLE"|translate}:</label>
                    </td>
                    <td colspan="3">
                        <input type="text" name="keyword" class="inputbox big" size="20"/>
                    </td>
                    <td style="">
                        <div class="auction_search_field">
                            <input type="submit" name="{'COM_BIDS_BUT_SEARCH'|translate}" value="{'COM_BIDS_BUT_SEARCH'|translate}" class="auction_button" />
                        </div>
                    </td>
                </tr>

                <tr>
                    <td>&nbsp;</td>
                    <td>
                        <div class="auction_search_sub">
                            <input type="checkbox" class="inputbox" name="indesc" value="1" checked="" /> &nbsp;{'COM_BIDS_SEARCH_DESC_TEXT'|translate}
                        </div>
                    </td>
                    <td>
                        <div class="auction_search_sub">
                            <input type="checkbox" class="inputbox" name="inarch" value="1" /> &nbsp;{'COM_BIDS_SEARCH_ARCH'|translate}
                        </div>
                    <td>
                        <div class="auction_search_sub">
                            <input type="checkbox" class="inputbox" name="tagnames" value="1" /> &nbsp;{'COM_BIDS_SEARCH_TAGS'|translate}
                        </div>
                    </td>
                    <td>&nbsp;</td>
                </tr>

                <tr>
                    <td colspan="6">
                        <div class="div_spacer"></div>
                    </td>
                </tr>

            {if $lists.cats}
                <tr>
                    <td>
                        <div class="auction_search_field auction_search_field2">
                            <label class="auction_lables">{"COM_BIDS_CATEGORY"|translate}:</label>
                        </div>
                    </td>
                    <td colspan="5">
                        <div class="auction_search_field auction_search_field2">
                            {$lists.cats}
                        </div>
                    </td>
                </tr>
            {/if}

                <tr>
                    <td colspan="6">
                        <div class="div_spacer"></div>
                    </td>
                </tr>

                <tr>
                    <td>
                        <div class="auction_search_field  auction_search_field2">
                            <label class="auction_lables">{"COM_BIDS_SEARCH_TIME_RANGE"|translate}:</label>
                        </div>
                    </td>
                    <td colspan="5">
                        <div class="auction_search_field  auction_search_field2">
                            {$lists.after_calendar} &ndash; {$lists.before_calendar}
                        </div>
                    </td>
                </tr>

                <tr>
                    <td colspan="6">
                        <div class="div_spacer"></div>
                    </td>
                </tr>

                <tr>
                    <td>
                        <div class="auction_search_field auction_search_field2">
                            <label class="auction_lables">{"COM_BIDS_SEARCH_PRICE_RANGE"|translate}:</label>
                        </div>
                    </td>
                    <td colspan="6">
                        <div class="auction_search_field auction_search_field2">
                            {$lists.startprice} &ndash; {$lists.endprice}&nbsp;&nbsp;&nbsp;{$lists.currency}
                        </div>
                    </td>
                </tr>

                <tr>
                    <td colspan="6">
                        <div class="div_spacer"></div>
                    </td>
                </tr>

                <tr>
                    <td>
                        <div class=" auction_search_field2">
                            <label class="auction_lables">{"COM_BIDS_SERCH_JUST_SELLERS"|translate}:</label>
                            {$lists.active_users}
                        </div>
                    </td>
                    <td>
                        <div class="auction_search_field2">
                        {if $lists.city}
                            <label class="auction_lables">{"COM_BIDS_CITY"|translate}:</label>
                            {$lists.city}
                        {/if}
                        </div>
                    </td>
                    <td>
                        <div class="auction_search_field2">
                        {if $lists.country}
                            <label class="auction_lables">{"COM_BIDS_COUNTRY"|translate}:</label>
                            {$lists.country}
                        {/if}
                        </div>
                    </td>
                </tr>
            </table>


            <div class="div_spacer"></div>

            {$custom_fields_html}

        </div>
    </div>

</form>

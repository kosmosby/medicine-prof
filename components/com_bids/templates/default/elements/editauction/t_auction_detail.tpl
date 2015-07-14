<div>
    <div class="auction_edit_field_container">
        <div class="auction_edit_field_label">{'COM_BIDS_CATEGORY'|translate}:</div>
        <div class="auction_edit_field_input">{$lists.cats}</div>
        <div style="clear: both;"></div>
    </div>
    <div class="auction_edit_field_container">
        <div class="auction_edit_field_label">{'COM_BIDS_TITLE'|translate}:</div>
        <div class="auction_edit_field_input">{$lists.title}{$required_image}</div>
        <div style="clear: both;"></div>
    </div>
    <div class="auction_edit_field_container">
        <div class="auction_edit_field_label">{'COM_BIDS_PUBLISHED'|translate}:</div>
        <div class="auction_edit_field_input">{$lists.published}</div>
        <div style="clear: both;"></div>
    </div>

    <div class="auction_edit_subsection">
        <div>{'COM_BIDS_AUCTION_TYPE_SETTINGS'|translate}</div>
    </div>
    <div class="auction_edit_field_container">
        <div class="auction_edit_field_label">{'COM_BIDS_TYPE_OF_AUCTION'|translate}:</div>
        <div class="auction_edit_field_input">{$lists.auctiontype} {$required_image}</div>
        <div style="clear: both;"></div>
    </div>
    <div class="auction_edit_field_container">
        <div class="auction_edit_field_label">{'COM_BIDS_AUTOMATIC_AUCTION'|translate}:</div>
        <div class="auction_edit_field_input">{$lists.automatic}</div>
        <div style="clear: both;"></div>
    </div>
    {if ($bidCfg->bid_opt_global_enable_bin || $bidCfg->bid_opt_enable_bin_only) }
    <div class="auction_edit_field_container">
        <div class="auction_edit_field_label">{'COM_BIDS_BIN_OPTION'|translate}:</div>
        <div class="auction_edit_field_input">{$lists.binType}</div>
        <div style="clear: both;"></div>
    </div>
    {/if}

    <div class="auction_edit_subsection">
        <div>{'COM_BIDS_DATE_TIME_SETTINGS'|translate}</div>
    </div>
    <div class="auction_edit_field_container">
        <div class="auction_edit_field_label">{'COM_BIDS_CURRENT_LOCAL_TIME'|translate}:</div>
        <div class="auction_edit_field_input">{$lists.currentLocalTime_field}</div>
        <div style="clear: both;"></div>
    </div>
    <div class="auction_edit_field_container">
        <div class="auction_edit_field_label">{'COM_BIDS_START_DATE'|translate}:</div>
        <div class="auction_edit_field_input">{$lists.startDate_field}</div>
        <div style="clear: both;"></div>
    </div>
    <div class="auction_edit_field_container">
        <div class="auction_edit_field_label">{'COM_BIDS_END_DATE'|translate}:</div>
        <div class="auction_edit_field_input">{$lists.endDate_field}</div>
        <div style="clear: both;"></div>
    </div>

    <div class="auction_edit_subsection">
        <div>{'COM_BIDS_PRICE_SETTINGS'|translate}</div>
    </div>
    <div class="auction_edit_field_container">
        <div class="auction_edit_field_label">{'COM_BIDS_TAB_CURRENY'|translate}:</div>
        <div class="auction_edit_field_input">{$lists.currency}{$required_image}</div>
        <div style="clear: both;"></div>
    </div>
    <div class="auction_edit_field_container" id="initial_price_row" {$hide_prices}>
        <div class="auction_edit_field_label">{'COM_BIDS_INITIAL_PRICE'|translate}:</div>
        <div class="auction_edit_field_input">{$lists.initialPrice}{$required_image}</div>
        <div style="clear: both;"></div>
    </div>
    <div class="auction_edit_field_container" id="reserve_maxprice_row" {$hide_prices}>
        <div class="auction_edit_field_label">{'COM_BIDS_PARAM_MAX_PRICE_TEXT'|translate}:</div>
        <div class="auction_edit_field_input">{$lists.showMaxPrice}</div>
        <div style="clear: both;"></div>
    </div>
    <div class="auction_edit_field_container" id="reserve_nrbidder_row" {$hide_prices}>
        <div class="auction_edit_field_label">{'COM_BIDS_PARAM_COUNTS_TEXT'|translate}:</div>
        <div class="auction_edit_field_input">{$lists.showNumberBids}</div>
        <div style="clear: both;"></div>
    </div>
{if $bidCfg->bid_opt_global_enable_reserve_price}
    <div class="auction_edit_field_container" id="reserve_price_row" {$hide_prices}>
        <div class="auction_edit_field_label">{'COM_BIDS_RESERVE_PRICE'|translate}:</div>
        <div class="auction_edit_field_input">{$lists.reservePrice}</div>
        <div style="clear: both;"></div>
    </div>
    <div class="auction_edit_field_container" id="reserve_price_row2" {$hide_prices}>
        <div class="auction_edit_field_label">{'COM_BIDS_PARAM_RESERVE_PRICE_TEXT'|translate}:</div>
        <div class="auction_edit_field_input">{$lists.showReservePrice}</div>
        <div style="clear: both;"></div>
    </div>
{/if}
{if $bidCfg->bid_opt_min_increase_select}
    <div class="auction_edit_field_container" id="min_increase_select_row" {$hide_prices}>
        <div class="auction_edit_field_label">{'COM_BIDS_OPT_MIN_INCREASE_TITLE'|translate}:</div>
        <div class="auction_edit_field_input">{$lists.minIncrease}</div>
        <div style="clear: both;"></div>
    </div>
{/if}
{* BIN SETTINGS *}
{if ($bidCfg->bid_opt_global_enable_bin || $bidCfg->bid_opt_enable_bin_only) }

    <div class="auction_edit_field_container" id="BIN_price_row" {$hide_bin}>
        <div class="auction_edit_field_label">{'COM_BIDS_BIN_PRICE'|translate}:</div>
        <div class="auction_edit_field_input">{$lists.binPrice}</div>
        <div style="clear: both;"></div>
    </div>
    <div class="auction_edit_field_container" id="BIN_price_row2" {$hide_bin}>
        <div class="auction_edit_field_label">{'COM_BIDS_PARAM_ACCEPT_BIN_TEXT'|translate}:</div>
        <div class="auction_edit_field_input">{$lists.autoAcceptBIN}</div>
        <div style="clear: both;"></div>
    </div>
    {if $bidCfg->bid_opt_quantity_enabled}
        <div class="auction_edit_field_container" id="bin_only_extra" {$hide_suggest}>
            <div class="auction_edit_field_label">{'COM_BIDS_BIN_QUANTITY'|translate}:</div>
            <div class="auction_edit_field_input">{$lists.quantity}</div>
            <div style="clear: both;"></div>
        </div>
    {/if}
    {if $bidCfg->bin_opt_price_suggestion}
        <div class="auction_edit_field_container" id="bid_price_suggest" {$hide_suggest}>
            <div class="auction_edit_field_label">{'COM_BIDS_PRICE_SUGGEST'|translate}:</div>
            <div class="auction_edit_field_input">{$lists.enableSuggestions}</div>
            <div style="clear: both;"></div>
        </div>
        <div class="auction_edit_field_container" id="bid_price_suggest_min" {$hide_suggest}>
            <div class="auction_edit_field_label">{'COM_BIDS_PRICE_SUGGEST_MIN'|translate}:</div>
            <div class="auction_edit_field_input">{$lists.minNumberSuggestions}</div>
            <div style="clear: both;"></div>
        </div>
    {/if}
{/if}
{* END BIN *}

    <div class="auction_edit_subsection">
        <div>{'COM_BIDS_SHIPPING_SETTINGS'|translate}</div>
    </div>
    <div class="auction_edit_field_container">
        <div class="auction_edit_field_label">{'COM_BIDS_SHIPMENT_PRICE'|translate}:</div>
        <div class="auction_edit_field_input">{$lists.shippingPrice}</div>
        <div style="clear: both;"></div>
    </div>
    <div class="auction_edit_field_container">
        <div class="auction_edit_field_label">{'COM_BIDS_SHIPMENT'|translate}:</div>
        <div class="auction_edit_field_input">{$lists.shipmentInfo}</div>
        <div style="clear: both;"></div>
    </div>

    <div class="auction_edit_subsection">
        <div>{'COM_BIDS_PAYMENT_SETTINGS'|translate}</div>
    </div>
    <div class="auction_edit_field_container">
        <div class="auction_edit_field_label">{'COM_BIDS_ADDITIONAL_PAYMENT_INFO'|translate}:</div>
        <div class="auction_edit_field_input">{$lists.paymentInfo}</div>
        <div style="clear: both;"></div>
    </div>
</div>

{if $custom_fields_html}
    <div class="auction_edit_subsection">
        <div>{'COM_BIDS_OTHER_SETTINGS'|translate}</div>
    </div>
    <div>
        {$custom_fields_html}
    </div>
{/if}

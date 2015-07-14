<div align="right" style="text-align:right;">
    <ul id="auction_tabmenu">
        <li>
            <a class="{if ('all' == $filter_type || '' == $filter_type)}active{else}inactive{/if}"
               href="{$ROOT_HOST}index.php?option=com_bids&filter_type=all">
            {"COM_BIDS_ALL_ITEMS"|translate}</a>
        </li>
        <li>
            <a class="{if ('auctions_only' == $filter_type)}active{else}inactive{/if}"
               href="{$ROOT_HOST}index.php?option=com_bids&filter_type=auctions_only">
            {"COM_BIDS_AUCTIONS_ONLY"|translate}</a>
        </li>
        <li>
            <a class="{if ('bin_only' == $filter_type)}active{else}inactive{/if}"
               href="{$ROOT_HOST}index.php?option=com_bids&filter_type=bin_only">
            {"COM_BIDS_WITH_BIN"|translate}</a>
        </li>
    </ul>
</div>

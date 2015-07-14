<div align="right" style="text-align:right;">
    <ul id="auction_tabmenu">
        <li>
            <a class="{if ('search' == $task)}active{else}inactive{/if}"
               href="{$ROOT_HOST}index.php?option=com_bids&task=search">
            {"COM_BIDS_VIEW_SEARCH_AUCTIONS"|translate}</a>
        </li>
        <li>
            <a class="{if ('searchusers' == $task)}active{else}inactive{/if}"
               href="{$ROOT_HOST}index.php?option=com_bids&task=searchusers">
            {"COM_BIDS_VIEW_SEARCH_PROFILES"|translate}</a>
        </li>
    </ul>
</div>
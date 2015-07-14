<div align="right" style="text-align:right;">
    <ul id="auction_tabmenu">
        <li>
            <a class="{if ('myauctions' == $task)}active{else}inactive{/if}"
               href="{$ROOT_HOST}index.php?option=com_bids&task=myauctions">
            {"COM_BIDS_VIEW_MY_AUCTIONS"|translate}</a>
        </li>
        <li>
            <a class="{if ('suggestions' == $task)}active{else}inactive{/if}"
               href="{$ROOT_HOST}index.php?option=com_bids&task=suggestions">
            {"COM_BIDS_VIEW_RECEIVED_SUGGESTIONS"|translate}</a>
        </li>
    </ul>
</div>
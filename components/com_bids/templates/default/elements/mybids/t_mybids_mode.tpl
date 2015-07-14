<div align="right" style="text-align:right;">
    <ul id="auction_tabmenu">
        <li>
            <a class="{if ('mysuggestions' == $task)}active{else}inactive{/if}"
               href="{$ROOT_HOST}index.php?option=com_bids&task=mysuggestions">
            {"COM_BIDS_VIEW_MY_SUGGESTIONS"|translate}</a>
        </li>
        <li>
            <a class="{if ('mybids' == $task)}active{else}inactive{/if}"
               href="{$ROOT_HOST}index.php?option=com_bids&task=mybids">
            {"COM_BIDS_VIEW_MY_BIDS"|translate}</a>
        </li>
        <li>
            <a class="{if ('mywonbids' == $task)}active{else}inactive{/if}"
               href="{$ROOT_HOST}index.php?option=com_bids&task=mywonbids">
            {"COM_BIDS_VIEW_MY_WON_BIDS"|translate}</a>
        </li>
    </ul>
</div>
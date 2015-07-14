<div align="right" style="text-align:right;">
    <ul id="auction_tabmenu">
        <li>
            <a class="{if ('listcats' == $task)}active{else}inactive{/if}"
               href="{$ROOT_HOST}index.php?option=com_bids&task=listcats">
            {"COM_BIDS_VIEW_CATEGORIES_DEFAULT"|translate}</a>
        </li>
        <li>
            <a class="{if ('tree' == $task)}active{else}inactive{/if}"
               href="{$ROOT_HOST}index.php?option=com_bids&task=tree">
            {"COM_BIDS_VIEW_CATEGORIES_TREE"|translate}</a>
        </li>
    </ul>
</div>
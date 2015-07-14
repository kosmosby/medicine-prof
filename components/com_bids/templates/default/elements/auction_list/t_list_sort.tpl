<table  id="auction_order_header" cellpadding="0" cellspacing="0" border="0">
    <tr>
        <td align="left" valign="center">
            <a href="{$ROOT_HOST}index.php?option=com_bids&task=listauctions&liststyle=grid">
                <img class="auction_liststyle" src="{$ROOT_HOST}/components/com_bids/images/grid_list{if #display_style#!='grid'}_off{/if}.gif" border="0" title="{'COM_BIDS_DISPLAY_GRID'|translate}"/>
            </a>
            <a href="{$ROOT_HOST}index.php?option=com_bids&task=listauctions&liststyle=list_detail">
                <img class="auction_liststyle" src="{$ROOT_HOST}/components/com_bids/images/list_detail{if #display_style#!='list_detail'}_off{/if}.gif" border="0" title="{'COM_BIDS_DISPLAY_DETAILED'|translate}"/>
            </a>
            <a href="{$ROOT_HOST}index.php?option=com_bids&task=listauctions&liststyle=list">
                <img class="auction_liststyle" src="{$ROOT_HOST}/components/com_bids/images/list_simple{if #display_style#!='list'}_off{/if}.gif" border="0" title="{'COM_BIDS_DISPLAY_SIMPLE'|translate}"/>
            </a>
            &nbsp;&nbsp;&nbsp;{'COM_BIDS_ORDER_BY'|translate}&nbsp;{$lists.orders}&nbsp;{$lists.filter_order_asc}
        </td>
    </tr>
    <tr>
        <td style="height: 1px;">&nbsp;</td>
    </tr>
</table>

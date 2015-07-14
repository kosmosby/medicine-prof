<table width="100%">
    <tr>
        <td>
            <div id="intro_search">
                    <span id="search_left">&nbsp;</span>
                    <div style="width: 100%; display: table-row;">
                        <span style="white-space: nowrap; display: table-cell; padding-left: 5px; font-size: 14px;
                        font-weight: bold;
                        ">{'COM_BIDS_BUT_SEARCH'|translate}&nbsp;</span>
                        <span style="width:100%; display: table-cell;">{$lists.inputKeyword}</span>
                        {if $lists.filter_cats|@count}
                            <span style="white-space: nowrap; display: table-cell; font-size: 14px; font-weight: bold;;
                            ">&nbsp;
                                {'COM_BIDS_IN'|translate}&nbsp;</span>
                            <span style="display: table-cell;">{$lists.filter_cats}</span>
                        {/if}
                        <span style="display: table-cell; padding-right: 8px; padding-left: 10px;">
                            <span class="auction_search_field" id="search_header_button">
                                <input type="submit"
                                       value="{'COM_BIDS_FILTER'|translate}" class="auction_button"/>
                            </span>
                        </span>
                    </div>
                    <span id="search_right">&nbsp;</span>
            </div>
        </td>
    </tr>
</table>

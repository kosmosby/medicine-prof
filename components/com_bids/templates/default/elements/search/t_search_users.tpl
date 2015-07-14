{set_css}

{include file='elements/search/t_search_mode.tpl'}

<div class="auction_header">{'COM_BIDS_SEARCH_USERS_TITLE'|translate}</div>
<form action="{$ROOT_HOST}index.php" method="post" name="auctionForm" >
<input type="hidden" name="task" value="showUsers"/>
<input type="hidden" name="option" value="com_bids"/>
<input type="hidden" name="search_type" value="{$search_type}"/>
<input type="hidden" name="Itemid" value="{$Itemid}"/>

<table width="100%" class="auctions_search">
	<tr class="auction_search_keyword">
		<td colspan="2">
            <div>
    			<label class="auction_lables">{"COM_BIDS_FILTER_KEYWORD"|translate}:</label>
    			<input type="text" name="keyword" class="inputbox" size="20"/>
                <input type="submit" name="search" value="Search" class="auction_button" />
            </div>
		</td>
	</tr>
    <tr>
        <td colspan="2">
            {$lists.custom_fields}
        </td>
    </tr>
</table>
</form>

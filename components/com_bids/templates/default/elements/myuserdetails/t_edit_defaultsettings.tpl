 <form action="{$ROOT_HOST}index.php" method="post" name="auctionForm" onsubmit="return formvalidate()">
 <input type="hidden" name="Itemid" value="{$Itemid}" />
 <input type="hidden" name="option" value="{$option}" />
 <input type="hidden" name="task" value="saveDefaultAuctionSettings" />

<table width="100%" id="bidsDefaultUserSettings">
	<tr>
		<td style="width: 200px;"><strong>{'COM_BIDS_SETTING_CURRENCY'|translate}</strong></td>
		<td>
			{$lists.user_settings.currency}
		</td>
        <td rowspan="100">
            <input name="save" value="{'COM_BIDS_BUT_SAVE'|translate}" class="auction_button save" type="submit"/>
        </td>
	</tr>
	<tr>
		<td><strong>{'COM_BIDS_TYPE_OF_AUCTION'|translate}</strong></td>
		<td>
			{$lists.user_settings.auction_type}
		</td>
	</tr>
    {if $bidCfg->bid_opt_enable_hour}
	<tr>
		<td><strong>{'COM_BIDS_SETTING_END_TIME'|translate}</strong></td>
		<td>
			H: {$lists.user_settings.end_hour}
			m: {$lists.user_settings.end_minute}
		</td>
	</tr>
    {/if}
	<tr>
		<td><strong>{'COM_BIDS_PAYMENT_INFO'|translate}</strong></td>
		<td>
            {$lists.user_settings.payment_info}
		</td>
	</tr>
	<tr>
		<td><strong>{'COM_BIDS_SHIPMENT'|translate}</strong></td>
		<td>
			{$lists.user_settings.shipment_info}
		</td>
	</tr>
	<tr>
		<td><strong>{'COM_BIDS_SHIPMENT_PRICE'|translate}</strong></td>
		<td>
			{$lists.user_settings.shipment_price}
		</td>
	</tr>
	{if $bidCfg->bid_opt_global_enable_reserve_price}
	<tr>
        <td><strong>{'COM_BIDS_PARAM_RESERVE_PRICE_TEXT'|translate}</strong></td>
        <td>
            {$lists.user_settings.show_reserve}
            {infobullet text='COM_BIDS_PARAM_RESERVE_PRICE_HELP'}
        </td>
	</tr>
	{/if}
    {if ($bidCfg->bid_opt_global_enable_bin || $bidCfg->bid_opt_enable_bin_only)}
    <tr>
        <td><strong>{'COM_BIDS_PARAM_ACCEPT_BIN_TEXT'|translate}</strong></td>
        <td>
            {$lists.user_settings.auto_accept_bin}
            {infobullet text='COM_BIDS_PARAM_ACCEPT_BIN_HELP'}
        </td>
    </tr>
    {/if}
    <tr>
        <td><strong>{'COM_BIDS_PARAM_COUNTS_TEXT'|translate}</strong></td>
        <td>
            {$lists.user_settings.bid_counts}
            {infobullet text='COM_BIDS_PARAM_COUNTS_HELP'}
        </td>
    </tr>
    <tr>
        <td><strong>{'COM_BIDS_PARAM_MAX_PRICE_TEXT'|translate}</strong></td>
        <td>
            {$lists.user_settings.max_price}
            {infobullet text='COM_BIDS_PARAM_MAX_PRICE_HELP'}
        </td>
    </tr>
</table>
</form>


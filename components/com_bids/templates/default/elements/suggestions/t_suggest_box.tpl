   {if !$auction->isMyAuction}
   <form name="form_suggestPrice" method="POST" action="{$ROOT_HOST}index.php">
   	<input type="hidden" name="option" value="{$option}" />
   	<input type="hidden" name="task" value="suggest" />
    <input type="hidden" name="id" value="{$auction->id}" />
    <input type="hidden" id="system_quantity" name="quantity" value="1" />
		{'COM_BIDS_BIN_QUANTITY_HELP'|translate}<br />
	    <table class="auction_bid_box">
			<tr>
				<td>
                    <div style="float: left;">
                        <span id="spinnerBox"></span> {$lists.quantity}
                    </div>
                    <div style="float: left;">&nbsp;X&nbsp;</div>
                    <div style="float: left;">
                            <input type="text" id="bid_suggest" name="bid_suggest" onkeyup="refreshSuggestPrice();" size="4" value="0" /> {$auction->currency}
                    </div>

                    <div style="float: left;">
                        &nbsp;= &nbsp;<span id="bid_suggest_total">0</span> {$auction->currency} {'COM_BIDS_BIN_SUGGEST_TOTAL_PRICE'|translate}
                    </div>

                    <div style="clear: both;"></div>
                    <div>
                        <input type="button" class="auction_button" value="{'COM_BIDS_BUT_SUGGEST'|translate}" name="price_suggest" {$disable_bids} onclick="suggestPrice(this);" />
                    </div>
				</td>
			</tr>
		</table>
   </form>
   {/if}

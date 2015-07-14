        {if $current_row->rownr is odd}
			{assign var=class value="1"}
		{else}
			{assign var=class value="2"}
		{/if}
        <tr class="auction_suggestion_row{$class} nobottom">
        	<td colspan="6">
            	<a style="text-decoration: none;" href="{$current_row->links.auctiondetails}">{$current_row->title}</a>
        	</td>
        </tr>
        <tr class="auction_suggestion_row{$class}">
        	<td>
	        	&nbsp;
        	</td>
        	<td>
        		<a style="text-decoration: none;" href="{$current_row->links.otherauctions}"
                    title="{'COM_BIDS_EXPIRED'|translate}">{$current_row->username}</a>
        	</td>
        	<td>
	        	{$current_row->quantity} x &nbsp;{print_price auction=$current_row price=$current_row->suggested_price}
        	</td>
        	<td>
        		{$current_row->bid_date}
        	</td>
        	<td>
				{if  $current_row->expired}
				<div id="auction_info_bottom">
                    <span class='expired'>{'COM_BIDS_EXPIRED'|translate}</span>
				</div>
				{elseif $bidCfg->bid_opt_enable_countdown}
				<div id="auction_info_bottom">
				    {$current_row->countdownHtml}
				</div>
				{/if}
        	</td>
        	<td>
				{if $current_row->accept==0}
					{'COM_BIDS_SUGGEST_STATUS_REJECTED'|translate}
				{elseif $current_row->accept==1}	
					{'COM_BIDS_SUGGEST_STATUS_ACCEPTED'|translate}
				{else}
					{'COM_BIDS_SUGGEST_STATUS_PENDING'|translate}
				{/if}	
		  		{if  !$current_row->automatic}
			  		{if $current_row->accept=="2"}
                        <div>
                            {$current_row->quantity} x <input type="text"
                                                              id="bid_suggest_{$current_row->parent_message}"
                                                              name="bid_suggest" size="5"/> {$current_row->currency}
                            <input type="button" class="auction_button" value="{'COM_BIDS_REPLY'|translate}"
                                   name="price_suggest"
                                   onclick="document.getElementById('form_bid_suggest').value = document.getElementById('bid_suggest_{$current_row->parent_message}').value;  document.getElementById('s_id').value = {$current_row->parent_message};document.getElementById('auction_id').value = {$current_row->id}; document.getElementById('quantity').value = {$current_row->quantity};  suggestThisPrice();"/>
                            <br/>
                            <a style="text-decoration: none;" href="{$ROOT_HOST}/index
                            .php?option=com_bids&task=acceptsuggestion&id={$current_row->parent_message}" onclick="return confirm('{'COM_BIDS_CONFIRM_ACCEPT_SUGGEST'|translate}');">
                                <input type="button" class="auction_button" value="{'COM_BIDS_ACCEPT'|translate}" /></a>&nbsp;

                            <a style="text-decoration: none;" href="{$ROOT_HOST}/index
                            .php?option=com_bids&task=rejectsuggestion&id={$current_row->parent_message}" onclick="return confirm('{'COM_BIDS_CONFIRM_REJECT_SUGGEST'|translate}');">
                              <input type="button" class="auction_button" value="{'COM_BIDS_REJECT'|translate}" /></a>


                        </div>
                    {/if}
				{else}
					-
				{/if}
        	</td>
        </tr>

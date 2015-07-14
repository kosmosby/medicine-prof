{include file="../configs/config_template.tpl"}
{set_css}
<div class="auction_header">{'COM_BIDS_REPORT_AUCTION'|translate}</div>
<form action="{$ROOT_HOST}index.php" name="auctionForm" method="POST">
<input type="hidden" name="option" value="com_bids" />
<input type="hidden" name="Itemid" value="{$Itemid}" />
<input type="hidden" name="id" value="{$auction_id}" />
<input type="hidden" name="task" value="sendReportAuction" />
 <table>
	<tr><td><span class="auction_info_text">{'COM_BIDS_REPORT_OFFER_MSG'|translate}</span></td></tr>
	<tr id="auctionedit_section2"><td>{$auction_title}</td></tr>
    <tr><td>&nbsp;</td></tr>
	<tr><td><textarea name="message" rows="10" cols="50" class="inputbox"></textarea></td></tr>
	<tr>
		<td>
			<a href='javascript:history.go(-1);'><input type="button" class="auction_button" value="{'COM_BIDS_CANCEL'|translate}" /></a>
			<input type="submit" name="send" value="{'COM_BIDS_BIDS_SEND_MESSAGE'|translate}" class="auction_button" />
		</td>
	</tr>
 </table>
</form>

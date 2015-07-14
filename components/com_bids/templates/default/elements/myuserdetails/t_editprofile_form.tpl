{set_css}
<div class="auction_header">{'COM_BIDS_EDIT_USER_DETAILS'|translate}</div>
<div id="submit_message_status"></div>
<form action="{$formAction}" method="post" name="auctionForm" id="WV-form" class="form-validate" onSubmit="return myValidate(this);" enctype="multipart/form-data" >
 <input type="hidden" name="Itemid" value="{$Itemid}" />
 <input type="hidden" name="return" value="{$return}" />
 {$lists.token}

<div class="auction_profile_page" id="auctionedit_pane">
    <fieldset class="auction_profile_box">
        <legend>{'COM_BIDS_EDIT_USER_DETAILS'|translate}</legend>
        <table width="100%" border="0" cellpadding="0" cellspacing="0">
        <tr>
           <td width="150"><strong>{'COM_BIDS_NAME'|translate}</strong></td>
           <td><input class="inputbox required" type="text" name="jform[name]" value="{$user->name}" size="40" /></td>
        </tr>
        <tr>
           <td width="150"><strong>{'COM_BIDS_SURNAME'|translate}</strong></td>
           <td><input class="inputbox required" type="text" name="surname" value="{$user->surname}" size="40" /></td>
        </tr>
    {if !$is_logged_in}
        <tr>
           <td width="150"><strong>{'COM_BIDS_USERNAME'|translate}</strong></td>
           <td><input class="inputbox required" type="text" name="jform[username]" value="{$user->username}" size="40" /></td>
        </tr>
        <tr>
           <td width="150"><strong>{'COM_BIDS_EMAIL'|translate}</strong></td>
           <td><input class="inputbox required" type="text" name="jform[email1]" value="{$user->email}" size="40" /></td>
        </tr>
        <tr>
           <td width="150">{'COM_BIDS_EMAIL'|translate}</td>
           <td><input class="inputbox required" type="text" name="jform[email2]" value="{$user->email}" size="40" /></td>
        </tr>
        <tr>
           <td width="150"><strong>{'COM_BIDS_PASSWORD'|translate}</strong></td>
           <td><input class="inputbox required" type="password" name="jform[password1]" value="" size="40" /></td>
        </tr>
        <tr>
           <td width="150"><strong>{'COM_BIDS_PASSWORD_CONFIRM'|translate}</strong></td>
           <td><input class="inputbox required" type="password" name="jform[password2]" value="" size="40" /></td>
        </tr>
        <tr>
          	<td colspan="2"><hr /></td>
        </tr>
    {/if}
          
        <tr>
           <td width="150"><strong>{'COM_BIDS_ADDRESS'|translate}</strong></td>
           <td><input class="inputbox required" type="text" name="address" value="{$user->address}" size="40" /></td>
        </tr>
        <tr>
           <td width="150"><strong>{'COM_BIDS_CITY'|translate}</strong></td>
           <td><input class="inputbox required" type="text" name="city" value="{$user->city}" size="40" /></td>
        </tr>
        <tr>
           <td width="150"><strong>{'COM_BIDS_COUNTRY'|translate}</strong></td>
           <td>{$lists.country}</td>
        </tr>
        <tr>
           <td width="150"><strong>{'COM_BIDS_PHONE'|translate}</strong></td>
           <td><input class="inputbox" type="text" name="phone" value="{$user->phone}" size="40" /></td>
        </tr>
        
   {if $bidCfg->bid_opt_allowpaypal}
        <tr>
           <td width="150"><strong>{'COM_BIDS_USER_PAYPALEMAIL'|translate}</strong></td>
           <td><input class="inputbox" type="text" name="paypalemail" value="{$user->paypalemail}" size="40" /></td>
        </tr>
   {/if}

         <tr>
             <td colspan="2">{$custom_fields_html}</td>
        </tr>
        <tr>
            <td colspan="2">&nbsp;</td>
        </tr>
        <tr>
            <td colspan="2">
                <input name="save" value="{'COM_BIDS_BUT_SAVE'|translate}" class="auction_button save" type="submit" />
                <input type="button" class="auction_button cancel" name="cancel" value="{'COM_BIDS_CANCEL'|translate}"
                       class="auction_button"
                       onclick="history.go(-1)"/>
            </td>
        </tr>
        </table>
    </fieldset>
</div>
</form>

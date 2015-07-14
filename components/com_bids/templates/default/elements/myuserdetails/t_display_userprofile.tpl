 <table width="100%" border="0" cellpadding="0" cellspacing="0" >
  <tr>
   <td width="85"><strong>{'COM_BIDS_NAME'|translate}:</strong></td>
   <td>{$user->name}</td>
  </tr>
  <tr>
   <td width="85"><strong>{'COM_BIDS_SURNAME'|translate}:</strong></td>
   <td>{$user->surname}</td>
  </tr>

  <tr>
   <td width="85"><strong>{'COM_BIDS_ADDRESS'|translate}:</strong></td>
   <td>{$user->address}</td>
  </tr>

  <tr>
   <td width="85"><strong>{'COM_BIDS_CITY'|translate}:</strong></td>
   <td>{$user->city}</td>
  </tr>
  <tr>
   <td width="85"><strong>{'COM_BIDS_COUNTRY'|translate}:</strong></td>
   <td>{$user->country}</td>
  </tr>  

  <tr>
   <td width="85"><strong>{'COM_BIDS_PHONE'|translate}:</strong></td>
   <td>{$user->phone}</td>
  </tr>
    <tr>
        <td colspan="2">
            {positions position="header" item=$user page="user_profile"}
        </td>
    </tr>


  {if $bidCfg->bid_opt_allowpaypal}
	  <tr>
       <td width="85"><strong>{'COM_BIDS_USER_PAYPALEMAIL'|translate}:</strong></td>
       <td>{$user->paypalemail}</td>
	  </tr>
  {/if}

{foreach from=$fields item=cf}
    <tr>
        <td width="85"><strong>{$cf->name|translate}:</strong></td>
        {assign var=cfDbName value=$cf->db_name}
        <td>{$user->$cfDbName}</td>
    </tr>
{/foreach}
</table>

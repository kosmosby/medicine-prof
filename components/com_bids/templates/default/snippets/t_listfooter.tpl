<table class="pagination" width="100%">
  <tr>
   <td  colspan="11" class="sectiontablefooter" style="font-weight: bold;">
   {$pagination->getPagesLinks()}
   </td>
  </tr>
  <tr>
   <td colspan="11" style="font-weight: bold;">
    {$pagination->getPagesCounter()}
   </td>
  </tr>
  <tr>
    <td colspan="11">
    {'COM_BIDS_PN_DISPLAY_NR'|translate}
    {$pagination->getLimitBox()}
    </td>
  </tr>
 </table>

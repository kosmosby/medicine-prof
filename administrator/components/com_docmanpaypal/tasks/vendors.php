<?php
JToolBarHelper::title("DOCman PayPal IPN " . $dm->getVersion() . ' ' ._DMP_EDITVENDORS,'docmanPayPalLogo');
JToolBarHelper::deleteList(_DMP_REALLYDELETEVENDORS,'deleteVendors');
JToolBarHelper::addNew('editVendors');
?><form action="index.php" method="post" name="adminForm" id="adminForm">
  <table cellpadding="4" cellspacing="0" border="0" width="100%" class="adminlist table table-bordered table-striped">

    <thead>

          <tr>

            <th width="1%" class="title"><input type="checkbox" name="toggle" value="" onclick="checkAll(11);" /></th>

            <th align="left">Name</th>

            <th align="center">PayPal Email</th>

            <th align="center"><?php echo _DMP_MYPERCENT; ?></th>

            <th align="center"><?php echo _DMP_EARNINGS; ?></th>

            <th align="center"><?php echo _DMP_UNPAID; ?></th>

            <th align="center"><?php echo _DMP_ACTIONS; ?></th>


          </tr>

    </thead>

          <tfoot><tr><td colspan="7">--</td></tr></tfoot>

          <tbody>

<?php
$vendors = $dm->getVendors();
$count = count($vendors);
foreach ($vendors as $v) {
?>
                <tr class="row<?php echo fmod($count,2) ? 1 : 0; ?>">
        		<td width="2%">
				<input type="checkbox" id="cb<?php echo $v->vendor_id; ?>" name="cid[]" value="<?php echo $v->vendor_id; ?>" onclick="isChecked(this.checked);" />
            	</td>
            	<td>
                    <a onclick="return listItemTask('cb<?php echo $v->vendor_id; ?>','editVendors')" href="#editVendors">
                        <?php echo $v->name; ?>                    </a>
                </td>
            	<td align="center"><?php echo $v->paypalemail; ?></td>
                
				<td align="center"><?php echo $v->mypercent; ?></td>

				<td align="center"><?php echo number_format($v->earnings,2); ?></td>

				<td align="center"><?php echo number_format($v->unpaid,2); ?></td>

				<td align="center"><a onclick="return listItemTask('cb<?php echo $v->vendor_id; ?>','viewOrders')" href="#viewOrders"><?php echo _DMP_VIEWORDERS; ?></a></td>
       			</tr>
<?php
}
?>
	</tbody>

  </table>







      <input type="hidden" name="option" value="com_docmanpaypal" />

      <input type="hidden" name="task" id="task" value="" />

      <input type="hidden" name="boxchecked" value="0" />

	  </form>
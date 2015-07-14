<?php
$row =& JTable::getInstance('vendors','Table');
@$cid = JRequest::getVar('cid');

if ($cid[0] > 0) {

	$row->load($cid[0]);

} else {

	$row->load(0);

}

$newOld = 'New';
if ($row->name != '') { $newOld = $row->name; }
JToolBarHelper::title(_DMP_EDITVENDORSADDEDIT . " - " . $newOld,'docmanPayPalLogo');
JToolBarHelper::save('saveVendor');
JToolBarHelper::cancel('vendors');
?><form action="index.php" method="post" name="adminForm" id="adminForm">

		

        <fieldset class="adminform">

    	<legend><?php echo _DMP_VENDOR; ?></legend>

    	<table class="admintable">

			<tr>

					<td class="key"><?php echo _DMP_NAME; ?>:</td>

					<td>

						<input class="inputbox" type="text" name="name" size="50" maxlength="100" value="<?php echo $row->name; ?>" />

					</td>

		  </tr>
                <tr>

					<td class="key"><?php echo _DMP_PAYPALEMAIL; ?>:</td>

					<td>

						<input name="paypalemail" type="text" class="inputbox" id="paypalemail" value="<?php echo $row->paypalemail; ?>" size="50" maxlength="100" />

					</td>

				</tr>
                <tr>

					<td class="key"><?php echo _DMP_MYPERCENT; ?>:</td>

					<td>

						<input name="mypercent" type="text" class="inputbox" id="mypercent" value="<?php echo $row->mypercent; ?>" size="50" maxlength="100" />

					</td>

				</tr>

					  <td colspan="2"><?php echo _DMP_MYPERCENTEXPLAIN; ?></td>

				</tr>
                
                	</table>

			</fieldset>
			<input type="hidden" name="vendor_id" value="<?php echo $row->vendor_id; ?>" />

			<input type="hidden" name="option" value="com_docmanpaypal" />


			<input type="hidden" name="task" value="saveVendor" />

			</form>
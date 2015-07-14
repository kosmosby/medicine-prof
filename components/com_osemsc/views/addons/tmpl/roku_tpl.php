<?php
/**
 * @version     5.0 +
 * @package        Open Source Membership Control - com_osemsc
 * @subpackage    Open Source Access Control - com_osemsc
 * @author        Open Source Excellence (R) {@link  http://www.opensource-excellence.com}
 * @author        Created on 15-Nov-2010
 * @license GNU/GPL http://www.gnu.org/copyleft/gpl.html
 *
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *  @Copyright Copyright (C) 2010- Open Source Excellence (R)
 */
defined('_JEXEC') or die("Direct Access Not Allowed");
$document = &JFactory::getDocument();
if (!empty($this->items)) {
	$status = ($this->items->status) ? "Active" : "Inactive";
	echo "<table width='100%' class='3rdpartycats'>";
	echo "<th width='40%' class='first'>My Device ID</th><th width='40%'>Registration Code</th><th width='20%'>Status</td>";
	echo "<tr class='items'>";
	echo "<td>" . $this->items->deviceID . "</td>";
	echo "<td>" . $this->items->regCode . "</td>";
	echo "<td>" . $status . "</td>";
	echo "</tr>";
	echo "</table>";
} else {
	$document->addScript(JURI::root() . '/components/com_osemsc/views/addons/js/js.roku.js');
?>
<div class='componentheading'>
<?php echo $this->page_title; ?>
</div>
<div id = 'deviceRegistration'>
	<div>
		<p id='warnmessage' name='warnmessage' class='gk_warning3'></p>
	</div>
	<div>
    	<p class='gk_tips3' id="tips" name="tips"></p>
	</div>
	<div id='deviceRegister'>
	<h2>Please enter your Registration Code as shown on the Screen: </h2>
		<div id='deviceRegisterBox'>
		Registration Code: <input type="text" class="text" name="regCode" id="regCode" value="" size="20" maxlength="20" />
		</div>
		<div id='deviceRegisterButton'>
		<input id ='deviceRegButton' name ='deviceRegButton' type='button' value ='Register'>
		</div>
	</div>
</div>
<br /><br />
<div class='clr'></div>
<?php
}
?>
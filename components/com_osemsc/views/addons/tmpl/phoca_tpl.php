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
if (!empty($this->items)) {
	echo "<table width='100%' id='osephocacats'>";
	echo "<th width='40%' class='first'>Membership Plan</th><th width='40%'>Accessbile Resources</th><th width='20%'>Download</td>";
	foreach ($this->items as $key => $values) {
		foreach ($values as $value) {
			echo "<tr class='items'>";
			if ($mscID != $key) {
				$mscID = $key;
				echo "<td>" . $value['mscTitle'] . "</td>";
			} else {
				echo "<td></td>";
			}
			echo "<td>" . $value['title'] . "</td>";
			echo "<td><span class='download'><a href='" . JRoute::_($this->link . $value['content_id']) . "'  class='button'>Download</a></span></td>";
			echo "</tr>";
		}
	}
	echo "</table>";
}
?>

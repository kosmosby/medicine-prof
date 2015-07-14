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
oseHTML::script("administrator/".OSEMSCFOLDER."/views/addons/js/js.addons.simple.js",'1.5');
?>
<div id="oseheader">
	<div class="container">
		<div class="logo-labels">
			<h1>
				<a href="http://www.opensource-excellence.com" target="_blank"><?php echo JText::_("Open Source Excellence"); ?>
				</a>
			</h1>
			<?php
			echo $this->preview_menus;
			?>
		</div>
		<?php
		$this->OSESoftHelper->showmenu();
		?>
		<div class="section">
			<div id="sectionheader">
				<?php echo $this->title; ?>
			</div>
			<div class="grid-title">
				<?php
				echo JText :: _('PLEASE_MANAGE_YOUR_ADDONS_HERE');
				?>
			</div>
			
			<div id="ose-addon-list">
				
				<table id ="core-addons" style ="width:100%;">
				<tr class='addon-item'>
				<th class='x-panel-header x-unselectable' width = "40%"><?php echo JText::_("CORE_OSE_ADDON_NAME"); ?></th>
				<th class='x-panel-header x-unselectable' width = "40%"><?php echo JText::_("PREFERENCE"); ?></th>
				<th class='x-panel-header x-unselectable' width = "20%"><?php echo JText::_("STATUS"); ?></th>
				</tr>
				<?php
				foreach ($this->coreaddons as $addon)
				{
				?>
				<tr class='addon-item'>
				<td class='title'>
				<?php echo $addon->name; ?></td>
				<td class='item'><?php
				echo $addon->options;
				?></td>
				<td class='item'>
				<?php
				$image = ($addon->status==1)?"accept.png":"remove.png";
				echo "<img src ='components/com_osemsc/assets/images/".$image."'>";
				 ?>
				</td>
				</tr>
				<?php
				}
				?>
				</table>
				
				<table id ="3rd-party-addons" width = "100%">
				<tr class='addon-item'>
				<th class='x-panel-header x-unselectable' width = "40%"><?php echo JText::_("THIRD_PARTY_ADDON_NAME"); ?></th>
				<th class='x-panel-header x-unselectable' width = "40%"><?php echo JText::_("PREFERENCE"); ?></th>
				<th class='x-panel-header x-unselectable' width = "20%"><?php echo JText::_("STATUS"); ?></th>
				</tr>
				<?php
				foreach ($this->addons as $addon)
				{
				?>
				<tr class='addon-item'>
				<td class='title'>
				<?php echo $addon->name; ?></td>
				<td class='item'><?php
				echo $addon->options;
				?></td>
				<td class='item'>
				<?php
				$image = ($addon->status==1)?"accept.png":"remove.png";
				echo "<img src ='components/com_osemsc/assets/images/".$image."'>";
				 ?>
				</td>
				</tr>
				<?php
				}
				?>
				</table>
			</div>
		</div>
	</div>
</div>
<?php
echo oseSoftHelper::renderOSETM();
?>
<?php
/*
 * Licensed to the Apache Software Foundation (ASF) under one
* or more contributor license agreements.  See the NOTICE file
* distributed with this work for additional information
* regarding copyright ownership.  The ASF licenses this file
* to you under the Apache License, Version 2.0 (the
		* "License") +  you may not use this file except in compliance
* with the License.  You may obtain a copy of the License at
*
*   http://www.apache.org/licenses/LICENSE-2.0
*
* Unless required by applicable law or agreed to in writing,
* software distributed under the License is distributed on an
* "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
* KIND, either express or implied.  See the License for the
* specific language governing permissions and limitations
* under the License.
*/
// No direct access
defined('_JEXEC') or die('Restricted access');

JHTML::_('behavior.tooltip');
?>

<form action="index.php" method="post" name="adminForm">
	<table class="adminlist">
		<thead>
			<tr>
				<th width="10"><?php echo JText::_( 'ID' ); ?></th>
				<th width="10"><input type="checkbox" name="toggle" value=""
					onclick="checkAll(
	    <?php echo count( $this->items ); ?>);" />
				</th>
				<th><?php echo JText::_('ROOM_NAME'); ?></th>
				<th><?php echo JText::_('ROOM_LINK'); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php
			$k = 0;
			$i = 0;
			foreach( $this->items as $row )
			{
				$checked = JHTML::_('grid.id', $i, $row->id );
				$link = JRoute::_('index.php?option=' . JRequest::getVar( 'option' ) . '&task=omroom_edit&cid[]='. $row->id . '&hidemainmenu=1' );

				$participantlink=JRoute::_( 'index.php?option=' . JRequest::getVar( 'option' ) . '&view=om&format=raw&room='. urlencode($row->room_id));
				?>
			<tr class="<?php echo "row$k"; ?>">
				<td><?php echo $row->id; ?></td>
				<td><?php echo $checked; ?></td>
				<td><a href="<?php echo $link; ?>"><?php echo $row->name; ?> </a></td>
				<td><a href="<?php echo $participantlink; ?>" target="_blank"><?php echo JText::_('SHOW_ROOM'); ?>
				</a></td>
			</tr>
			<?php
			$k = 1 - $k;
			$i++;
	    } ?>
		</tbody>
		<tfoot>
			<tr>
				<td colspan="6"><?php echo $this->pagination->getListFooter(); ?></td>
			</tr>
		</tfoot>
	</table>

	<input type="hidden" name="option"
		value="<?php echo JRequest::getVar( 'option' ); ?>" /> <input
		type="hidden" name="task" value="omrooms" /> <input type="hidden"
		name="boxchecked" value="0" />
</form>

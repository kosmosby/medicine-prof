<?php
/**
 * ------------------------------------------------------------------------
 * JUDownload for Joomla 2.5, 3.x
 * ------------------------------------------------------------------------
 *
 * @copyright      Copyright (C) 2010-2015 JoomUltra Co., Ltd. All Rights Reserved.
 * @license        GNU General Public License version 2 or later; see LICENSE.txt
 * @author         JoomUltra Co., Ltd
 * @website        http://www.joomultra.com
 * @----------------------------------------------------------------------@
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

if ($this->item->params->get('access-edit') || $this->item->params->get('access-edit-state') || $this->item->params->get('access-delete'))
{
	?>
	<div class="private-actions btn-group pull-right">
		<a class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" href="#"><span class="fa fa-cog"></span> <span class="caret"></span></a>
		<ul class="dropdown-menu">
			<?php
			if ($this->item->params->get('access-edit'))
			{
				?>
				<li>
					<?php
					// Edit link
					if(isset($this->item->edit_link))
					{
						echo '<a title="' . JText::_('COM_JUDOWNLOAD_EDIT_DOCUMENT') . '" href="' . $this->item->edit_link . '"><i class="fa fa-edit"></i> ' . JText::_('COM_JUDOWNLOAD_EDIT_DOCUMENT') . '</a>';
					}
					// Check in link
					else
					{
						$checkedOutUser = JFactory::getUser($this->item->checked_out);
						$checkedOutTime = JHtml::_('date', $this->item->checked_out_time);
						$tooltip      = JText::sprintf('COM_JUDOWNLOAD_CHECKED_OUT_BY', $checkedOutUser->name) . ' <br /> ' . $checkedOutTime;
						if(isset($this->item->checkin_link))
						{
							echo '<a href="' . $this->item->checkin_link . '"><i class="hasTooltip fa fa-lock" title="' . $tooltip . '"></i> ' . JText::_('COM_JUDOWNLOAD_EDIT_DOCUMENT') . '</a>';
						}
						// Can not check in, show icon without link
						else
						{
							echo '<a><i class="hasTooltip fa fa-lock" title="' . $tooltip . '"></i> ' . JText::_('COM_JUDOWNLOAD_EDIT_DOCUMENT') . '</a>';
						}
					}
					?>
				</li>
			<?php
			}
			?>

			<?php
			if ($this->item->params->get('access-edit-state'))
			{
				?>
				<li>
					<?php
					// Edit state link
					if(isset($this->item->editstate_link))
					{
						$publish_up   = JHtml::_('date', $this->item->publish_up);
						$publish_down = JHtml::_('date', $this->item->publish_down);

						$tooltip = '&lt;br /&gt;';
						if (strtotime($publish_up) > 0)
						{
							$tooltip .= JText::_('COM_JUDOWNLOAD_PUBLISH_UP') . ': ';
							$tooltip .= $publish_up;
							$tooltip .= '&lt;br /&gt;';
						}

						if (strtotime($publish_down) > 0)
						{
							$tooltip .= JText::_('COM_JUDOWNLOAD_PUBLISH_DOWN') . ': ';
							$tooltip .= $publish_down;
							$tooltip .= '&lt;br /&gt;';
						}

						if ($this->item->published == 1)
						{
							$time_now  = JFactory::getDate()->toSql();
							$null_date = JFactory::getDbo()->getNullDate();

							$text = JText::_('COM_JUDOWNLOAD_PUBLISHED');
							$icon = '<i class="hasTooltip fa fa-check" title="' . $text . $tooltip . '"></i>';
							if ($this->item->publish_up != $null_date && strtotime($publish_up) > strtotime($time_now))
							{
								// Pending
								$text = JText::_('COM_JUDOWNLOAD_PENDING');
								$icon = '<i class="hasTooltip fa fa-clock-o" title="' . $text . $tooltip . '"></i>';
							}
							elseif ($this->item->publish_down != $null_date && strtotime($publish_down) <= strtotime($time_now))
							{
								// Expired
								$text = JText::_('COM_JUDOWNLOAD_EXPIRED');
								$icon = '<i class="hasTooltip fa fa-exclamation-circle" title="' . $tooltip . '"></i>';
							}
						}
						else
						{
							$text = JText::_('COM_JUDOWNLOAD_UNPUBLISHED');
							$icon = '<i class="hasTooltip fa fa-close" title="' . $text . $tooltip . '"></i>';
						}

						echo '<a class="judl-document-state" id="judl-document-state-' . $this->item->id . '" title="' . JText::_('COM_JUDOWNLOAD_EDIT_STATE_DOCUMENT') . '" href="' . $this->item->editstate_link . '">' . $icon . ' ' . $text . '</a>';
					}
					// Check in link
					else
					{
						$checkedOutUser = JFactory::getUser($this->item->checked_out);
						$checkedOutTime = JHtml::_('date', $this->item->checked_out_time);
						$tooltip      = JText::sprintf('COM_JUDOWNLOAD_CHECKED_OUT_BY', $checkedOutUser->name) . ' <br /> ' . $checkedOutTime;
						if(isset($this->item->checkin_link))
						{
							echo '<a href="' . $this->item->checkin_link . '"><i class="hasTooltip fa fa-lock" title="' . $tooltip . '"></i> ' . JText::_('COM_JUDOWNLOAD_EDIT_STATE_DOCUMENT') . '</a>';
						}
						// Can not check in, show icon without link
						else
						{
							echo '<a><i class="hasTooltip fa fa-lock" title="' . $tooltip . '"></i> ' . JText::_('COM_JUDOWNLOAD_EDIT_STATE_DOCUMENT') . '</a>';
						}
					}
					?>
				</li>
			<?php
			}
			?>

			<?php
			if ($this->item->params->get('access-delete'))
			{
				?>
				<li>
					<?php
						echo '<a class="judl-delete-document" id="judl-delete-document-' . $this->item->id . '" title="' . JText::_('COM_JUDOWNLOAD_DELETE_DOCUMENT') . '" href="' . $this->item->delete_link . '"><i class="fa fa-trash-o"></i> ' . JText::_('COM_JUDOWNLOAD_DELETE_DOCUMENT') . '</a>';
					?>
				</li>
			<?php
			}
			?>
		</ul>
	</div>

	<?php
	if ($this->item->params->get('access-edit-state'))
	{
		?>
		<!-- Modal: Edit document state confirm -->
		<div id="judl-edit-state-alert-<?php echo $this->item->id; ?>"
		     class="modal hide fade" tabindex="-1" role="dialog"
		     aria-labelledby="judl-edit-state-alert-label-<?php echo $this->item->id; ?>" aria-hidden="true">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3 id="judl-edit-state-alert-label-<?php echo $this->item->id; ?>" class="modal-title">
					<?php echo JText::_('COM_JUDOWNLOAD_EDIT_STATE_DOCUMENT_ALERT'); ?>
				</h3>
			</div>
			<div class="modal-body">
				<?php
				if ($this->item->published)
				{
					echo JText::sprintf('COM_JUDOWNLOAD_UNPUBLISH_DOCUMENT_X_CONFIRM', $this->item->title);
				}
				else
				{
					echo JText::sprintf('COM_JUDOWNLOAD_PUBLISH_DOCUMENT_X_CONFIRM', $this->item->title);
				}
				?>
			</div>
			<div class="modal-footer">
				<button id="judl-edit-state-accept-<?php echo $this->item->id; ?>"
						class="btn btn-primary judl-edit-state-accept"><?php echo JText::_("COM_JUDOWNLOAD_CHANGE_STATE"); ?>
				</button>
				<button class="btn" data-dismiss="modal" aria-hidden="true">
					<?php echo JText::_("COM_JUDOWNLOAD_CANCEL"); ?>
				</button>
			</div>
		</div>
	<?php
	}
	?>

	<?php
	if ($this->item->params->get('access-delete'))
	{
		?>
		<!-- Modal: Delete document confirm -->
		<div id="judl-delete-alert-<?php echo $this->item->id; ?>"
		     class="modal hide fade" tabindex="-1" role="dialog"
		     aria-labelledby="judl-delete-alert-label-<?php echo $this->item->id; ?>" aria-hidden="true">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h3 id="judl-delete-alert-label-<?php echo $this->item->id; ?>" class="modal-title">
					<?php echo JText::_('COM_JUDOWNLOAD_DELETE_DOCUMENT_ALERT'); ?>
				</h3>
			</div>
			<div class="modal-body">
				<?php echo JText::sprintf('COM_JUDOWNLOAD_DELETE_DOCUMENT_X_CONFIRM', $this->item->title); ?>
			</div>
			<div class="modal-footer">
				<button id="judl-delete-accept-<?php echo $this->item->id; ?>"
				        class="btn btn-primary judl-delete-accept"><?php echo JText::_("COM_JUDOWNLOAD_DELETE"); ?>
				</button>
				<button class="btn" data-dismiss="modal" aria-hidden="true">
					<?php echo JText::_("COM_JUDOWNLOAD_CANCEL"); ?>
				</button>
			</div>
		</div>
	<?php
	}
	?>
<?php
} ?>
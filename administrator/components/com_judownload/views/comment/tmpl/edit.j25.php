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

JHtml::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_judownload/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidation');
?>
<script type="text/javascript">
	Joomla.submitbutton = function (task) {
		if (task == 'comment.cancel' || document.formvalidator.isValid(document.id('adminForm'))) {
			Joomla.submitform(task, document.getElementById('adminForm'));
		}
	};
</script>
<div class="jubootstrap">

	<div id="iframe-help"></div>

	<form action="<?php echo JRoute::_('index.php?option=com_judownload&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">
		<?php
		if (JFactory::getApplication()->input->getInt('approve', 0) == 1)
		{
			?>
		<div class="approval">
			<div class="approval-inner">
				<?php
				$totalPrevPendingComments = JUDownloadHelper::getTotalPendingComments('prev', $this->item->id);
				if ($totalPrevPendingComments)
				{
					?>
					<button class="judl-previous btn btn-info" onclick="Joomla.submitbutton('pendingcomment.saveAndPrev')">
						<i class="icon-arrow-left-2"></i>
						<?php echo JText::sprintf('COM_JUDOWNLOAD_SAVE_AND_PREV_N', $totalPrevPendingComments); ?>
					</button>
				<?php
				}
				?>

				<div class="judl-approval-options">
					<div class="judl-option-approve-item input-prepend input-append pull-left">
					<span class="add-on">
						<input type="radio" name="approval_option" value="ignore" checked id="ignore-comment" />
					</span>
						<label for="ignore-comment" class="btn">
							<i class="icon-question"></i>
							<?php echo JText::_('COM_JUDOWNLOAD_IGNORE'); ?>
						</label>
					</div>
					<div class="judl-option-approve-item input-prepend input-append pull-left">
					<span class="add-on">
						<input type="radio" name="approval_option" value="approve" id="approval-comment" />
					</span>
						<label for="approval-comment" class="btn btn-success">
							<i class="icon-checkmark-2"></i>
							<?php echo JText::_('COM_JUDOWNLOAD_APPROVE'); ?>
						</label>
					</div>
					<div class="judl-option-approve-item input-prepend input-append pull-left">
					<span class="add-on">
						<input type="radio" name="approval_option" value="delete" id="reject-comment" />
					</span>
						<label for="reject-comment" class="btn btn-danger">
							<i class="icon-cancel"></i>
							<?php echo JText::_('COM_JUDOWNLOAD_REJECT'); ?>
						</label>
					</div>
					<div class="clr"></div>
				</div>

				<?php
				$totalNextPendingComments = JUDownloadHelper::getTotalPendingComments('next', $this->item->id);
				if ($totalNextPendingComments)
				{
					?>
					<button class="judl-next btn btn-info" onclick="Joomla.submitbutton('pendingcomment.saveAndNext')">
						<?php echo JText::sprintf('COM_JUDOWNLOAD_SAVE_AND_NEXT_N', $totalNextPendingComments); ?>
						<i class="icon-arrow-right-2"></i>
					</button>
				<?php
				}
				?>
			</div>
		</div>

		<div class="clr"></div>
		<?php
		}
		?>
		<div class="width-60 fltlft">
			<fieldset class="adminform">
				<legend><?php echo JText::_('COM_JUDOWNLOAD_EDIT_COMMENT'); ?></legend>
				<ul class="adminformlist">
					<?php foreach ($this->form->getFieldset('details') AS $field): ?>
						<li>
							<?php echo $field->label; ?>
							<?php echo $field->input; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</fieldset>
		</div>

		<div class="width-40 fltrt">
			<?php echo JHtml::_('sliders.start', 'comment-sliders-' . $this->item->id, array('useCookie' => 1)); ?>
			<?php echo JHtml::_('sliders.panel', JText::_('COM_JUDOWNLOAD_FIELD_SET_PUBLISHING'), 'publishing'); ?>
			<fieldset class="adminform">
				<ul class="adminformlist">
					<?php foreach ($this->form->getFieldset('publishing') AS $field): ?>
						<li>
							<?php
							if ($field->fieldname == "approved_time" || $field->fieldname == "approved_by")
							{
								if ($this->item->approved_by)
								{
									echo $field->label;
									echo $field->input;
								}
							}
							elseif ($field->fieldname == "modified" || $field->fieldname == "modified_by")
							{
								if ($this->item->modified_by)
								{
									echo $field->label;
									echo $field->input;
								}
							}
							else
							{
								echo $field->label;
								echo $field->input;
							}
							?>
						</li>
					<?php endforeach; ?>
				</ul>
			</fieldset>
			<?php echo JHtml::_('sliders.end'); ?>
		</div>

		<div>
			<input type="hidden" name="task" value="" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>
</div>
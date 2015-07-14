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
JHtml::_('formbehavior.chosen', 'select');
JHtml::_('behavior.tabstate');

?>
<script type="text/javascript">
	Joomla.submitbutton = function (task) {
		if (task == 'comment.cancel' || document.formvalidator.isValid(document.id('adminForm'))) {
			Joomla.submitform(task, document.getElementById('adminForm'));
		}
	};
</script>

<div id="iframe-help"></div>

<form action="<?php echo JRoute::_('index.php?option=com_judownload&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">
	<?php
	if (JFactory::getApplication()->input->getInt('approve', 0) == 1)
	{
		?>
		<div class="approval span12">
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
	} ?>

	<?php echo JLayoutHelper::render('joomla.edit.title_alias', $this); ?>
	<div class="form-horizontal">
		<?php echo JHtml::_('bootstrap.startTabSet', 'comment', array('active' => 'details')); ?>
		<?php $fieldSets = $this->form->getFieldsets(); ?>
		<?php
		$this->form->removeField('title');
		$this->form->removeField('alias');
		?>
		<?php
		foreach ($fieldSets AS $fieldSet)
		{
			$fields = $this->form->getFieldSet($fieldSet->name);
			if ($fields)
			{
				$label = $fieldSet->label ? $fieldSet->label : JText::_('COM_JUDOWNLOAD_FIELD_SET_' . strtoupper($fieldSet->name));
				echo JHtml::_('bootstrap.addTab', 'comment', $fieldSet->name, $label);
				foreach ($fields AS $field)
				{
					if ($field->fieldname == 'modified' || $field->fieldname == 'modified_by')
					{
						if ($this->item->modified_by)
						{
							echo $field->getControlGroup();
						}
					}
					elseif ($field->fieldname == "rules")
					{
						echo $field->input;
					}
					else
					{
						echo $field->getControlGroup();
					}
				}
				echo JHtml::_('bootstrap.endTab');
			}
		}
        echo JHtml::_('bootstrap.endTabSet');
        ?>
	</div>

	<div>
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
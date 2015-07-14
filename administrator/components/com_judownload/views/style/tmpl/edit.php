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

$fields = $this->form->getFieldset('params');
?>
<script type="text/javascript">

	var taskSubmit = null;
	var style_id = <?php echo $this->item->id ? $this->item->id : 0 ; ?>;

	Joomla.submitbutton = function (task) {
		if (task == 'style.cancel') {
			Joomla.submitform(task, document.getElementById('adminForm'));
		} else if(document.formvalidator.isValid(document.id('adminForm'))){
			checkChangeHomeStyle(task);
			taskSubmit = task;
		}
	};

	function checkChangeHomeStyle(task){
		var objectPost = {};
		objectPost.id = style_id;
		objectPost.template_id = jQuery('#jform_template_id').val();
		objectPost.home = jQuery('#jform_home').val();

		if(jQuery('#jform_home').val() == 0){
			Joomla.submitform(task, document.getElementById('adminForm'));
		}
		jQuery.ajax({
			type: "POST",
			url : "index.php?option=com_judownload&task=style.checkChangeHomeStyle",
			data: objectPost
		}).done(function (data) {
			var data = jQuery.parseJSON(data);

			if (data) {
				if(data.status == 1){
					jQuery('#templateStyleMessage').html(data.message);
					jQuery('#confirmModal').modal();
				}else{
					Joomla.submitform(task, document.getElementById('adminForm'));
				}
			}
		});
	}

	jQuery(document).ready(function($){
		$('#acceptConfirm').on('click',function(e){
			e.preventDefault();
			var task = taskSubmit;
			Joomla.submitform(task, document.getElementById('adminForm'));
		});
	});
</script>

<div id="iframe-help"></div>

<form action="<?php echo JRoute::_('index.php?option=com_judownload&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">
	<div id="confirmModal" class="modal hide fade">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
			<h3><?php echo JText::_('COM_JUDOWNLOAD_CHANGE_DEFAULT_TEMPLATE_STYLE_WARNING'); ?></h3>
		</div>
		<div class="modal-body">
			<div id="warningTemplateStyle">
				<div class="alert alert-block">
					<div id="templateStyleMessage"></div>
				</div>
				<?php echo $this->form->getControlGroup('changeTemplateStyleAction'); ?>
			</div>
		</div>
		<div class="modal-footer">
			<button class="btn" data-dismiss="modal" aria-hidden="true"><?php echo JText::_('COM_JUDOWNLOAD_CLOSE'); ?></button>
			<button id="acceptConfirm" class="btn btn-primary"><?php echo JText::_('COM_JUDOWNLOAD_CONFIRM_AND_SAVE'); ?></button>
		</div>
	</div>
	<?php echo JLayoutHelper::render('joomla.edit.title_alias', $this); ?>
	<div class="form-horizontal">
		<?php echo JHtml::_('bootstrap.startTabSet', 'style', array('active' => 'details')); ?>
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
				echo JHtml::_('bootstrap.addTab', 'style', $fieldSet->name, $label);
				foreach ($fields AS $field)
				{
					if ($field->fieldname == 'modified' || $field->fieldname == 'modified_by')
					{
						if ($this->item->modified_by)
						{
							echo $field->getControlGroup();
						}
					}
					elseif ($field->fieldname == "parent_id")
					{
						if(!$this->item->id){
						?>
							<div class="alert alert-info">
								<?php echo JText::_('COM_JUDOWNLOAD_PLEASE_SELECT_TEMPLATE_THEN_SELECT_PARENT_TEMPLATE_STYLE'); ?>
							</div>
						<?php
						}
						echo $field->getControlGroup(); ?>
					<?php
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

		if ($this->item->xml)
		{
			if ($information = trim($this->item->xml->information))
			{
				echo JHtml::_('bootstrap.addTab', 'style', 'information', JText::_('COM_JUDOWNLOAD_INFORMATION'));
				?>
				<div class="information">
					<?php echo JText::_($information); ?>
				</div>
				<?php
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
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
		if (task == 'field.cancel' || (document.formvalidator.isValid(document.id('adminForm')) && (jQuery('#jform_predefined_values_type').val() != 2 || jQuery('#jform_php_predefined_values').data('validPhp')))) {
			Joomla.submitform(task, document.getElementById('adminForm'));
		}
		else
		{
			
			testPhpCode(true, task);
		}
	};
</script>

<div id="iframe-help"></div>

<form action="<?php echo JRoute::_('index.php?option=com_judownload&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">
	<div class="form-inline form-inline-header">
		<?php
		echo $this->form->getControlGroup('caption');
		echo $this->form->getControlGroup('alias');
		$this->form->removeField('caption');
		$this->form->removeField('alias');
		?>
	</div>

	<div class="form-horizontal">
		<div class="row-fluid">
			<div class="span7">
				<?php echo JHtml::_('bootstrap.startTabSet', 'field', array('active' => 'details')); ?>
				<?php $fieldSets = $this->form->getFieldsets(); ?>
				<?php
				$this->form->removeField('title');
				$this->form->removeField('alias');
				?>
				<?php
				foreach ($fieldSets AS $fieldSet)
				{
					if($fieldSet->name == 'preview' || $fieldSet->name == 'params'){
						continue;
					}

					$fields = $this->form->getFieldSet($fieldSet->name);
					if ($fields)
					{
						$label = $fieldSet->label ? $fieldSet->label : JText::_('COM_JUDOWNLOAD_FIELD_SET_' . strtoupper($fieldSet->name));
						echo JHtml::_('bootstrap.addTab', 'field', $fieldSet->name, $label);
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

				if ($this->item->xml)
				{
					if ($information = trim($this->item->xml->information))
					{
						echo JHtml::_('bootstrap.addTab', 'field', 'information', JText::_('COM_JUDOWNLOAD_INFORMATION'));
						?>
						<div class="information">
							<?php echo JText::_($information); ?>
						</div>
						<?php
						echo JHtml::_('bootstrap.endTab');
					}
				} ?>
				<?php echo JHtml::_('bootstrap.endTabSet');?>
			</div>
			<div class="span5">
				<?php echo JHtml::_('bootstrap.startAccordion', 'field-sliders-' . $this->item->id, array('active' => 'params')); ?>
				<?php echo JHtml::_('bootstrap.addSlide', 'field-sliders-' . $this->item->id, JText::_('COM_JUDOWNLOAD_FIELD_SET_PARAMS'), 'params', 'params'); ?>
				<fieldset class="adminform">
					<ul class="adminformlist nav">
						<?php
						foreach ($this->form->getFieldset('params') AS $key => $field): ?>
							<li>
								<?php echo $field->getControlGroup(); ?>
							</li>
						<?php endforeach; ?>
					</ul>
				</fieldset>
				<?php echo JHtml::_('bootstrap.endSlide'); ?>
				<?php echo JHtml::_('bootstrap.addSlide', 'field-sliders-' . $this->item->id, JText::_('COM_JUDOWNLOAD_FIELD_SET_PREVIEW'), 'preview', 'preview'); ?>
				<fieldset class="adminform">
					<ul class="adminformlist nav">
						<?php
						foreach ($this->form->getFieldset('preview') AS $key => $field): ?>
							<li>
								<?php echo $field->getControlGroup(); ?>
							</li>
						<?php endforeach; ?>
					</ul>
				</fieldset>
				<?php echo JHtml::_('bootstrap.endSlide'); ?>
				<?php echo JHtml::_('bootstrap.endAccordion'); ?>
			</div>
		</div>

	<div>
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
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

JFactory::getDocument()->addScript(JUri::root() . "components/com_judownload/assets/js/judl-tabs-state.js");

?>
<script type="text/javascript">
	Joomla.submitbutton = function (task) {
		if (task == 'criteriagroup.cancel' || document.formvalidator.isValid(document.id('adminForm'))) {
			<?php echo $this->form->getField('description')->save(); ?>
			Joomla.submitform(task, document.getElementById('adminForm'));
		}
	};
</script>

<div id="iframe-help"></div>

<form action="<?php echo JRoute::_('index.php?option=com_judownload&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">
	<?php echo JLayoutHelper::render('joomla.edit.title_alias', $this); ?>
	<div class="form-horizontal">
		<?php
		$this->form->removeField('name');
		?>
		<?php echo JHtml::_('bootstrap.startTabSet', 'criteria-group', array('active' => 'details')); ?>
		<?php $fieldSets = $this->form->getFieldsets(); ?>
		<?php
		foreach ($fieldSets AS $fieldSet)
		{
			$fields = $this->form->getFieldSet($fieldSet->name);
			if ($fields)
			{
				$label = $fieldSet->label ? $fieldSet->label : JText::_('COM_JUDOWNLOAD_FIELD_SET_' . strtoupper($fieldSet->name));
				echo JHtml::_('bootstrap.addTab', 'criteria-group', $fieldSet->name, $label);
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
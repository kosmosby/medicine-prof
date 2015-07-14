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

JHtml::_('behavior.tooltip');
JHtml::_('behavior.keepalive');
JHtml::_('behavior.formvalidation');
$app = JFactory::getApplication();
?>
<script type="text/javascript">
	Joomla.submitbutton = function (task) {
		if (task == 'globalconfig.cancel' || document.formvalidator.isValid(document.id('adminForm'))) {
			Joomla.submitform(task, document.getElementById('adminForm'));
		}
	};
</script>

<div class="jubootstrap">

	<?php echo JUDownloadHelper::getMenu(JFactory::getApplication()->input->get('view')); ?>

	<div id="iframe-help"></div>

	<form action="<?php echo JRoute::_('index.php?option=com_judownload&view=globalconfig&layout=edit'); ?>" enctype="multipart/form-data" method="post" name="adminForm" id="adminForm" class="form-validate judownload-config">
		<?php
		$fieldSets = $this->form->getFieldsets();
		if ($fieldSets)
		{
			if ($this->isJoomla3x)
			{
				echo JHtml::_('bootstrap.startTabSet', 'globalconfig', array('active' => 'config-general'));
			}
			else
			{
				echo JHtml::_('tabs.start', 'judownload', array('useCookie' => 1));
			}
			foreach ($fieldSets AS $name => $fieldSet)
			{
				$label = $fieldSet->label ? $fieldSet->label : JText::_('COM_JUDOWNLOAD_FIELD_SET_' . strtoupper($fieldSet->name));
				if ($this->isJoomla3x)
				{
					echo JHtml::_('bootstrap.addTab', 'globalconfig', $fieldSet->name, JText::_($label));
				}
				else
				{
					echo JHtml::_('tabs.panel', JText::_($label), '');
				}
				?>
				<ul class="adminformlist nav">
					<?php
					if(!JUDLPROVERSION)
					{
						foreach ($this->form->getFieldset($name) AS $field)
						{
							if($this->form->getFieldAttribute($field->fieldname, 'proversion', null, $field->group) == 'true')
							{
								$this->form->setFieldAttribute($field->fieldname, 'disabled', 'disabled', $field->group);
								$fieldLabel = $this->form->getFieldAttribute($field->fieldname, 'label', '', $field->group);
								$this->form->setFieldAttribute($field->fieldname, 'label', JText::_($fieldLabel) . ' <span class="pro-version">Pro version</span>', $field->group);
								$fieldClass = $this->form->getFieldAttribute($field->fieldname, 'class', '', $field->group);
								$this->form->setFieldAttribute($field->fieldname, 'class', $fieldClass . ' disabled', $field->group);
								$this->form->setFieldAttribute($field->fieldname, 'name', '', $field->group);
							}
						}
					}

					foreach ($this->form->getFieldset($name) AS $field)
					{
						if(!$app->input->getInt('showhidden', 0) && $this->form->getFieldAttribute($field->fieldname, 'hiddenfield', null, $field->group) == 'true')
						{
							$class = 'hidden';
						}
						elseif($this->form->getFieldAttribute($field->fieldname, 'hiddenfield', null, $field->group) == 'true')
						{
							$class = 'hiddenfield';
						}
						else
						{
							$class = '';
						}
						?>
						<li <?php echo $class ? 'class="' . $class . '"' : ''; ?>>
							<?php echo $field->label; ?>
							<?php echo $field->input; ?>
						</li>
					<?php
					} ?>
				</ul>
				<?php
				if ($this->isJoomla3x)
				{
					echo JHtml::_('bootstrap.endTab');
				}
				?>
			<?php
			}

			if ($this->isJoomla3x)
			{
				echo JHtml::_('bootstrap.endTabSet');
			}
			else
			{
				echo JHtml::_('tabs.end');
			}
		}
		?>

		<div>
			<input type="hidden" name="task" value="" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>
</div>
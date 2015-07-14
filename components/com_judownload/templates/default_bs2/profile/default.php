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

JHtml::_('behavior.formvalidation');
?>
<script type="text/javascript">
	Joomla.submitbutton = function (task) {
		if (task == 'criteria.cancel' || document.formvalidator.isValid(document.id('judownload-form'))) {
			<?php echo $this->form->getField('description')->save(); ?>
			Joomla.submitform(task, document.getElementById('judownload-form'));
		}
	}
</script>

<div id="judl-container" class="jubootstrap component judl-container view-profile">
	<form
		action="#"
		method="post" name="adminForm" id="judownload-form"
		class="form-validate form-horizontal" enctype="multipart/form-data">
		<fieldset>
			<legend><?php echo JText::_('COM_JUDOWNLOAD_EDIT_PROFILE'); ?></legend>
			<?php foreach ($this->form->getFieldset('details') AS $key => $field)
			{
				// Ignore notes field
				if ($field->id == 'jform_notes')
				{
					continue;
				}

				$canShow = false;
				?>
				<div class="control-group">
					<?php if ($field->id == 'jform_avatar')
					{
						if ($this->params->get('avatar_source', 'juavatar') == 'juavatar')
						{
							$canShow = true;
						}
					}
					else
					{
						if ($this->params->get('edit_account_details', 1) == 1)
						{
							$canShow = true;
						}
						else
						{
							$haystack = array('jform_email', 'jform_password', 'jform_password2');
							if (!in_array($field->id, $haystack))
							{
								$canShow = true;
							}
						}
					}

					if ($canShow)
					{
						echo '<div class="control-label">' . $field->label . '</div>';
						echo ' <div class="controls">' . $field->input . '</div>';
					}
					?>
				</div>
			<?php
			} ?>
			<div class="control-group">
				<div class="controls">
					<button type="button" onclick="Joomla.submitbutton('profile.save')"
					   class="btn btn-primary"> <?php echo JText::_('COM_JUDOWNLOAD_SUBMIT'); ?></button>
					<button type="button" onclick="Joomla.submitbutton('profile.cancel')"
					   class="btn"> <?php echo JText::_('COM_JUDOWNLOAD_CANCEL'); ?></button>
				</div>
			</div>
		</fieldset>

		<div>
			<input type="hidden" name="user_id" value="<?php echo $this->item->id ?>"/>
			<input type="hidden" name="task" value=""/>
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>
</div>
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
		if (task == 'field.cancel' || (document.formvalidator.isValid(document.id('adminForm')) && (jQuery('#jform_predefined_values_type').val() != 2 || jQuery('#jform_php_predefined_values').data('validPhp')))) {
			Joomla.submitform(task, document.getElementById('adminForm'));
		}
		else
		{
			
			testPhpCode(true, task);
		}
	};
</script>

<div class="jubootstrap">

	<div id="iframe-help"></div>

	<form action="<?php echo JRoute::_('index.php?option=com_judownload&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">
		<div class="width-60 fltlft">
			<fieldset class="adminform">
				<legend><?php echo JText::_('COM_JUDOWNLOAD_EDIT_FIELD'); ?></legend>
				<ul class="adminformlist">
					<?php
					foreach ($this->form->getFieldset('details') AS $key => $field):
						if ($field->name == "jform[access]")
						{
							?>
							<li>
								<?php echo $field->label; ?>
								<?php echo $field->input; ?>
							</li>
							<?php if ($this->canDo->get('core.admin')): ?>
							<li>
								<span class="faux-label"><?php echo JText::_('JGLOBAL_ACTION_PERMISSIONS_LABEL'); ?></span>

								<div class="button2-left">
									<div class="blank">
										<button type="button" onclick="document.location.href='#access-rules';">
											<?php echo JText::_('JGLOBAL_PERMISSIONS_ANCHOR'); ?></button>
									</div>
								</div>
							</li>
						<?php endif; ?>
						<?php
						}
						else
						{
							?>
							<li>
								<?php echo $field->label; ?>
								<?php echo $field->input; ?>
							</li>
						<?php
						} ?>
					<?php endforeach; ?>
				</ul>
			</fieldset>
		</div>

		<div class="width-40 fltrt">
			<?php echo JHtml::_('sliders.start', 'field-sliders-' . $this->item->id, array('useCookie' => 1)); ?>
			<?php echo JHtml::_('sliders.panel', JText::_('COM_JUDOWNLOAD_FIELD_SET_PUBLISHING'), 'publishing'); ?>
			<fieldset class="adminform">
				<ul class="adminformlist">
					<?php foreach ($this->form->getFieldset('publishing') AS $key => $field): ?>
						<li>
							<?php if ($field->fieldname == "modified" || $field->fieldname == "modified_by")
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
							}?>
						</li>
					<?php endforeach; ?>
				</ul>
			</fieldset>

			<?php $fields = $this->form->getFieldset('params'); ?>
			<?php if ($fields)
			{
				?>
				<?php echo JHtml::_('sliders.panel', JText::_('COM_JUDOWNLOAD_FIELD_SET_PARAMS'), 'params'); ?>
				<fieldset class="panelform">
					<ul class="adminformlist">
						<?php foreach ($fields AS $field) : ?>
							<li>
								<?php echo $field->label; ?>
								<?php echo $field->input; ?>
							</li>
						<?php endforeach; ?>
					</ul>
				</fieldset>
			<?php } ?>
			<?php
			if ($this->item->id)
			{
				echo JHtml::_('sliders.panel', JText::_('COM_JUDOWNLOAD_FIELD_SET_PREVIEW'), 'preview');
				?>
				<fieldset class="adminform">
					<ul class="adminformlist">
						<?php foreach ($this->form->getFieldset('preview') AS $key => $field): ?>
							<li>
								<?php echo $field->label; ?>
								<?php echo $field->input; ?>
							</li>
						<?php endforeach; ?>
					</ul>
				</fieldset>
			<?php } ?>
			<?php echo JHtml::_('sliders.panel', JText::_('COM_JUDOWNLOAD_FIELD_SET_METADATA'), 'metadata'); ?>

			<fieldset class="adminform">
				<ul class="adminformlist">
					<?php foreach ($this->form->getFieldset('metadata') AS $field): ?>
						<li>
							<?php echo $field->label; ?>
							<?php echo $field->input; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			</fieldset>
			<?php echo JHtml::_('sliders.end'); ?>

			<?php
			if ($this->item->xml)
			{
				if ($information = trim($this->item->xml->information))
				{
					echo JHtml::_('sliders.start', 'information-sliders-' . $this->item->id, array('useCookie' => 1));
					echo JHtml::_('sliders.panel', JText::_('COM_JUDOWNLOAD_INFORMATION'), 'information');
					?>
					<div class="information">
						<?php echo JText::_($information); ?>
					</div>
					<?php
					echo JHtml::_('sliders.end');
				}
			} ?>
		</div>

		<?php if ($this->canDo->get('core.admin'))
		{
			?>
			<div class="clr"></div>

			<div class="width-100 fltlft">
				<?php echo JHtml::_('sliders.start', 'field-permissions-sliders-' . $this->item->id, array('useCookie' => 1)); ?>
				<?php echo JHtml::_('sliders.panel', JText::_('COM_JUDOWNLOAD_FIELD_SET_PERMISSIONS'), 'permissions'); ?>
				<fieldset class="panelform">
					<ul class="adminformlist">
						<?php foreach ($this->form->getFieldset('permissions') AS $field): ?>
							<li>
								<?php echo $field->label; ?>
								<?php echo $field->input; ?>
							</li>
						<?php endforeach; ?>
					</ul>
				</fieldset>
				<?php echo JHtml::_('sliders.end'); ?>
			</div>
		<?php } ?>

		<div>
			<input type="hidden" name="task" value="" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>
</div>
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
?>
<script type="text/javascript">
	Joomla.submitbutton = function (task) {
		if (task == 'csvprocess.back' || document.formvalidator.isValid(document.id('adminForm'))) {
			Joomla.submitform(task, document.getElementById('adminForm'));
		}
	};
</script>

<div class="jubootstrap">
	<?php echo JUDownloadHelper::getMenu(JFactory::getApplication()->input->get('view')); ?>

	<div id="iframe-help"></div>

	<form action="#" method="post" name="adminForm" id="adminForm" enctype="multipart/form-data" class="form-validate">
		<fieldset class="adminform">
			<legend><?php echo JText::_("COM_JUDOWNLOAD_CSV_IMPORT_CONFIG"); ?></legend>
			<ul class="adminformlist">
				<?php
				echo "<li>";
				echo "<label>" . JText::_("COM_JUDOWNLOAD_IF_DOCUMENT_EXISTED") . "</label>";
				echo $this->save;
				echo "</li>";
				foreach ($this->form->getFieldset('details') AS $field)
				{
					echo "<li>";
					echo $field->label;
					echo $field->input;
					echo "</li>";
				}
				?>
			</ul>
		</fieldset>

		<div>
			<input type="hidden" name="task" value="" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>
</div>
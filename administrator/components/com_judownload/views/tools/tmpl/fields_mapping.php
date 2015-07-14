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
?>

<script type="text/javascript">
	jQuery(document).ready(function ($) {
		Joomla.submitbutton = function (task) {
			if (task == 'csvprocess.config') {
				var duplicate = false;
				var assigned = [];
				$('select').each(function () {
					var value = $(this).val();
					if (jQuery.inArray(value, assigned) > -1 && value != "ignore") {
						$("#duplicated_mapping").show();
						$(this).css('border-color', 'red');
						duplicate = true;
					} else {
						assigned.push(value);
						$(this).css('border-color', '');
					}
				});

				if (duplicate != true) {
					Joomla.submitform(task, document.getElementById('adminForm'));
				} else {
					return false;
				}
			} else {
				Joomla.submitform(task, document.getElementById('adminForm'));
			}
		};
	});
</script>

<div class="jubootstrap">
	<?php echo JUDownloadHelper::getMenu(JFactory::getApplication()->input->get('view')); ?>

	<div id="iframe-help"></div>

	<form action="#" method="post" name="adminForm" id="adminForm" class="form-validate">
		<fieldset class="adminform">
			<legend><?php echo JText::_("COM_JUDOWNLOAD_FIELDS_MAPPING"); ?></legend>
			<span id="duplicated_mapping" style="color: red;display: none"><?php echo JText::_("COM_JUDOWNLOAD_MORE_THAN_ONE_CSV_COLUMNS_IS_MAPPING_WITH_ONE_DOCUMENT_FIELD"); ?></span>
			<table class="table table-striped">
				<thead>
				<tr>
					<th><?php echo JText::_("COM_JUDOWNLOAD_CSV_COLUMNS"); ?></th>
					<th></th>
					<th><?php echo JText::_("COM_JUDOWNLOAD_DOCUMENT_FIELDS"); ?></th>
				</tr>
				</thead>

				<tbody>
				<?php
				if (isset($this->columns) && !empty($this->columns))
				{
					foreach ($this->columns AS $csvColumnName => $detectedColumnName)
					{
						echo "<tr><td>" . $csvColumnName . "</td><td><i class=\"icon-arrow-right-4\"></i></td><td>".JHTML::_('select.genericlist', $this->fieldsOption,'assigned[]','','value','text', $detectedColumnName)."</td></tr>";
					}
				}
				?>
				</tbody>
			</table>

			<div>
				<input type="hidden" name="task" value="<?php echo $this->task;?>" />
				<?php echo JHtml::_('form.token'); ?>
			</div>
		</fieldset>
	</form>
</div>
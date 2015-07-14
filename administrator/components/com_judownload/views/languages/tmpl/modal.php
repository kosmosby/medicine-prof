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
JHtml::_('behavior.multiselect');
?>
<form action="<?php echo JRoute::_('index.php?option=com_judownload&view=languages&layout=modal&tmpl=component'); ?>"
	method="post" name="adminForm" id="adminForm">
	<fieldset id="filter-bar">
		<div class="filter-search input-append pull-left">
			<select name="site" id="language" class="input-medium" onchange="this.form.submit()">
				<?php echo JHtml::_('select.options', $this->siteArr, 'value', 'text', $this->share_site); ?>
			</select>
		</div>

		<div class="filter-select">
			<div class="pull-right">
				<div class="button2-left">
					<div class="blank">
						<a href="#" class="btn btn-info"
							onclick="Joomla.submitbutton('languages.share')">
							<?php echo JText::_('COM_JUDOWNLOAD_SHARE'); ?>
						</a>
					</div>
				</div>
			</div>
		</div>
	</fieldset>

	<table class="table table-striped adminlist">
		<thead>
		<tr>
			<th style="width:4%" class="hidden-phone"><input type="checkbox"
					onclick="Joomla.checkAll(this)" title="<?php echo JText::_('COM_JUDOWNLOAD_CHECK_ALL'); ?>" value=""
					name="checkall-toggle" /></th>
			<th style="width: 40%; text-align: center;"><h3><?php echo JText::_('COM_JUDOWNLOAD_LANGUAGE_FILE'); ?></h3>
			</th>
			<th style="width: 30%; text-align: center;"><h3><?php echo JText::_('COM_JUDOWNLOAD_FOLDER'); ?></h3></th>
		</tr>
		</thead>

		<tbody>
		<?php
		if (count($this->arrLanguageFiles))
		{
			$i = 0;
			foreach ($this->arrLanguageFiles AS $folder => $fileArr)
			{
				if ($fileArr)
				{
					foreach ($fileArr AS $file)
					{
						$i++;
						?>
						<tr class="row<?php echo $i % 2 ?>">
							<td><?php echo JHtml::_('grid.id', $i, $folder . '/' . $file); ?></td>
							<td style="text-align: center;"><?php echo $file ?></td>
							<td style="text-align: center;"><?php echo $folder ?></td>
						</tr>
					<?php

					}
				}
			}
		}
		?>
		</tbody>
	</table>

	<div class="current" style="margin: 20px auto">
		<label><?php echo JText::_('COM_JUDOWNLOAD_SUBJECT_LABEL'); ?></label>
		<input type="text" name="subject" size="70" placeholder="<?php echo JText::_('COM_JUDOWNLOAD_SHARE_LANGUAGE_EMAIL_SUBJECT'); ?>" />

		<label><?php echo JText::_('COM_JUDOWNLOAD_MESSAGE_LABEL'); ?></label>
		<textarea name="messege" rows="10" cols="50" placeholder="<?php echo JText::_('COM_JUDOWNLOAD_SHARE_LANGUAGE_EMAIL_MESSAGE'); ?>"></textarea>
	</div>

	<div>
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</div>
</form>

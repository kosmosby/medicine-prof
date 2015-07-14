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
<div class="jubootstrap">
	<div id="iframe-help"></div>
	<form action="index.php"
		method="post" name="adminForm" id="adminForm">
		<fieldset>
			<legend style="margin-bottom: 5px;"><?php echo JText::_('COM_JUDOWNLOAD_FAST_ADD'); ?></legend>
			<div style="margin-bottom: 10px;"><?php echo JText::_('COM_JUDOWNLOAD_FAST_ADD_DESC'); ?></div>
			<div class="control-group">
				<label class="control-label" for="cat_names"></label>
				<div class="controls">
					<textarea id="cat_names" name="cat_names" cols="80" rows="10" style="width: 300px;"></textarea>
				</div>
			</div>
			<div class="control-group">
				<label class="control-label" for="published"><?php echo JText::_('COM_JUDOWNLOAD_FIELD_PUBLISHED'); ?></label>
				<div class="controls">
					<select name="published" id="published">
						<option value="0"><?php echo JText::_("JNO");?></option>
						<option value="1" selected="selected"><?php echo JText::_("JYES");?></option>
					</select>
				</div>
			</div>
			<div class="control-group">
				<div class="controls">
					<input type="submit" value="<?php echo JText::_('COM_JUDOWNLOAD_ADD_CATEGORIES'); ?>" class="btn btn-primary" />
				</div>
			</div>
		<div>
			<input type="hidden" name="task" value="categories.fastAdd" />
			<input type="hidden" name="option" value="com_judownload" />
			<input type="hidden" name="cat_id" value="<?php echo JFactory::getApplication()->input->getInt("cat_id", ""); ?>" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
		</fieldset>
	</form>
</div>
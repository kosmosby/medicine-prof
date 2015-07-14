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
JHtml::_('behavior.multiselect');

?>
<div class="jubootstrap">
	<?php echo JUDownloadHelper::getMenu(JFactory::getApplication()->input->get('view')); ?>

	<div id="iframe-help"></div>

	<form action="#" method="post" name="adminForm" id="adminForm" class="form-horizontal form-validate">
		<fieldset class="row-fluid">
			<legend><?php echo JText::_('COM_JUDOWNLOAD_RESIZE_IMAGES'); ?></legend>

			<div class="span6">
				<div class="progress progress-striped">
					<div class="bar center" style="width: 0%;"></div>
				</div>
			</div>

			<div class="span12">
				<div class="control-group ">
					<div class="control-label"><label><?php echo JText::_('COM_JUDOWNLOAD_NUM_OF_IMAGES_TO_RESIZE_EACH_TIME'); ?></label></div>
					<div class="controls"><?php echo JHtml::_('select.genericlist', $this->levelOptions, 'limit', 'class="input-mini"', 'value', 'text', '5', 'limit-img'); ?></div>
				</div>

				<div class="control-group ">
					<div class="control-label"><label><?php echo JText::_('COM_JUDOWNLOAD_SELECT_CATEGORIES'); ?></label></div>
					<div class="controls"><?php echo JHtml::_('select.genericlist', $this->categoryList, 'catlist[]', 'multiple style="height:150px;"', 'id', 'title', 1); ?></div>
				</div>

				<div class="control-group ">
					<div class="control-label"><label><?php echo JText::_('COM_JUDOWNLOAD_RESIZE_CATEGORY_IMAGES'); ?></label></div>
					<div class="controls"><?php echo JHtml::_('select.genericlist',$this->boolean, 'category', 'class="input-medium"', 'value', 'text', '1', 'category'); ?></div>
				</div>

				<div class="control-group ">
					<div class="control-label"><label><?php echo JText::_('COM_JUDOWNLOAD_RESIZE_DOCUMENT_IMAGES'); ?></label></div>
					<div class="controls"><?php echo JHtml::_('select.genericlist',$this->boolean, 'document', 'class="input-medium"', 'value', 'text', 1, 'document'); ?></div>
				</div>

				<div class="control-group ">
					<div class="control-label"><label><?php echo JText::_('COM_JUDOWNLOAD_AVATARS'); ?></label></div>
					<div class="controls"><?php echo JHtml::_('select.genericlist',$this->boolean, 'avatar', 'class="input-medium"', 'value', 'text', 1, 'avatar'); ?></div>
				</div>

				<div class="control-group ">
					<div class="control-label"><label><?php echo JText::_('COM_JUDOWNLOAD_DOCUMENT_ICONS'); ?></label></div>
					<div class="controls"><?php echo JHtml::_('select.genericlist',$this->boolean, 'docicon', 'class="input-medium"', 'value', 'text', 1, 'docicon'); ?></div>
				</div>

				<div class="control-group ">
					<div class="control-label"><label><?php echo JText::_('COM_JUDOWNLOAD_COLLECTION_ICONS'); ?></label></div>
					<div class="controls"><?php echo JHtml::_('select.genericlist',$this->boolean, 'collection', 'class="input-medium"', 'value', 'text', 1, 'collection'); ?></div>
				</div>
			</div>
		</fieldset>

		<div>
			<input type="hidden" name="option" value="com_judownload" />
			<input type="hidden" name="task" value="" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>
</div>
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
	<?php echo JUDownloadHelper::getMenu(JFactory::getApplication()->input->get('view')); ?>

	<div id="iframe-help"></div>

	<form action="#" method="post" name="adminForm" id="adminForm" class="form-horizontal form-validate">
		<fieldset class="row-fluid">
			<legend><?php echo JText::_('COM_JUDOWNLOAD_REBUILD_RATING'); ?></legend>

			<div class="span6">
				<div class="progress progress-striped" id="progress">
					<div class="bar" id="bar">
						<div id="process_info" style="display:none">
							<span id="processed"></span> /
							<span id="total_docs"></span> <?php echo JText::_("COM_JUDOWNLOAD_DOCUMENTS"); ?>
						</div>
					</div>
				</div>
			</div>

			<div class="span12">
				<div class="control-group ">
					<div class="control-label"><label><?php echo JText::_('COM_JUDOWNLOAD_NUM_OF_DOCUMENTS_TO_REBUILD_RATING_EACH_TIME'); ?></label></div>
					<div class="controls"><?php echo JHtml::_('select.genericlist', $this->levelOptions, 'limit', 'class="inputbox input-mini"', 'value', 'text', '20', 'limitimg'); ?></div>
				</div>

				<div class="control-group ">
					<div class="control-label"><label><?php echo JText::_('COM_JUDOWNLOAD_SELECT_CATEGORIES'); ?></label></div>
                    <?php $rootCategory = JUDownloadFrontHelperCategory::getRootCategory(); ?>
					<div class="controls"><?php echo JHtml::_('select.genericlist', $this->categoryList, 'catlist[]', 'multiple style="height:150px;"', 'id', 'title', $rootCategory->id); ?></div>
				</div>
				<?php
				if ($this->criteriaGroups)
				{
					?>
					<div class="control-group ">
						<div class="control-label"><label><?php echo JText::_('COM_JUDOWNLOAD_SELECT_CRITERIA_GROUPS'); ?></label></div>
						<div class="controls"><?php echo JHtml::_('select.genericlist', $this->criteriaGroups, 'criteriagroups[]', 'multiple style="height:150px;"', 'id', 'name', ''); ?></div>
					</div>
				<?php
				}
				?>
			</div>
		</fieldset>

		<div>
			<input type="hidden" name="task" value="tools.rebuildRating" />
			<input type="hidden" name="process" value="1" />
		</div>
	</form>
</div>
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
$app           = JFactory::getApplication();
$searchword    = $app->input->getString('searchword', '');
?>
<div id="judl-container" class="jubootstrap component judl-container view-search <?php echo $this->pageclass_sfx; ?>">
	<form name="frm_search" action="#" method="post" class="form-horizontal">
		<div class="form-group">
			<label for="inputSearchword" class="control-label col-xs-2"><?php echo JText::_('COM_JUDOWNLOAD_SEARCH_WORDS'); ?></label>
			<div class="col-xs-10">
				<input type="text" class="" name="searchword" id="inputSearchword"
				       placeholder="<?php echo JText::_('COM_JUDOWNLOAD_SEARCH_WORDS'); ?>"
				       value="<?php echo htmlspecialchars($this->searchword); ?>">
			</div>
		</div>

		<div class="form-group">
			<label for="cat_id" class="control-label col-xs-2"><?php echo JText::_('COM_JUDOWNLOAD_SEARCH_CATEGORY'); ?></label>
			<div class="col-xs-10">
				<?php echo $this->cat_select_list; ?>
			</div>
		</div>

		<div class="form-group">
			<label for="seach-sub-cat" class="control-label col-xs-2"><?php echo JText::_('COM_JUDOWNLOAD_SEARCH_SUB_CATEGORIES'); ?></label>
			<div class="col-xs-10">
				<input type="checkbox" value="1" name="sub_cat" <?php echo $this->sub_cat ? 'checked' : '';?> id="seach-sub-cat">
			</div>
		</div>

		<div class="form-group">
			<div class="col-xs-offset-2 col-xs-10">
				<button type="submit" class="btn btn-default btn-primary"><?php echo JText::_('COM_JUDOWNLOAD_SEARCH'); ?></button>
			</div>
		</div>
		<input type="hidden" value="" name="parent_cat_id"/>
		<input type="hidden" value="search.search" name="task"/>
		<?php echo JHtml::_('form.token'); ?>
	</form>

	<?php if (count($this->items))
	{
		?>
		<div class="results-counter">
			<?php echo $this->pagination->getResultsCounter(); ?>
		</div>
		<?php
		echo $this->loadTemplate('documents');
	}
	elseif($searchword)
	{
		?>
		<div class="alert alert-no-items"><?php echo JText::_('COM_JUDOWNLOAD_NO_ITEM_FOUND'); ?></div>
	<?php
	} ?>
</div>
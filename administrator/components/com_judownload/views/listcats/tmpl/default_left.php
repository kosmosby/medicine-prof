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
$rootCat = JUDownloadFrontHelperCategory::getRootCategory();
$cat_id = JFactory::getApplication()->input->getInt("cat_id", $rootCat->id);
$search_in = $this->state->get('filter.search_in');
?>

<ul class="manager-actions nav nav-list" style="margin-bottom: 20px;">
	<?php
	$actions = JUDownloadHelper::getActions('com_judownload', 'category', $cat_id);
	if ($actions->get("judl.document.create"))
	{
		if ($this->docGroupCanDoManage && $this->allowAddDoc)
		{
			echo "<li><a class='add-document' href='index.php?option=com_judownload&task=document.add&cat_id=$cat_id'><i class='icon-file-add'></i>" . JText::_('COM_JUDOWNLOAD_ADD_DOCUMENT') . "</a></li>";
		}
	}
	if ($actions->get("judl.category.create"))
	{
		if ($this->catGroupCanDoManage)
		{
			echo "<li><a class='add-category' href='index.php?option=com_judownload&task=category.add&parent_id=$cat_id'><i class='icon-folder-plus'></i>" . JText::_('COM_JUDOWNLOAD_ADD_CATEGORY') . "</a></li>";
		}
	}
	if (JUDownloadHelper::checkGroupPermission(null, "pendingdocuments") && JUDLPROVERSION)
	{
		echo "<li><a class='approved' href='index.php?option=com_judownload&view=pendingdocuments'><i class='icon-clock'></i>" . JText::sprintf('COM_JUDOWNLOAD_PENDING_DOCUMENTS_N', JUDownloadHelper::getTotalPendingDocuments()) . "</a></li>";
	}
	?>
</ul>

<div class="category-tree">
	<?php echo JUDownloadHelper::getCategoryDTree($cat_id); ?>
</div>

<div id="judl-search" style="margin-top: 15px;">
	<form name="search-form" id="search-form" action="index.php?option=com_judownload" method="POST">
		<fieldset>
			<div class="input-append">
				<input type="text" name="searchword" class="input-medium" size="20" maxlength="250" value="" placeholder="<?php echo JText::_('COM_JUDOWNLOAD_SEARCH'); ?>" />
				<button type="submit" name="submit_simple_search" class="btn"><i class="icon-search"></i>&nbsp;</button>
			</div>
		</fieldset>

		<div class="clearfix">
			<select name="view" id="search-in" class="input-medium">
				<option value="searchdocuments" selected><?php echo JText::_('COM_JUDOWNLOAD_SEARCH_DOCUMENTS'); ?></option>
				<option value="searchcategories"><?php echo JText::_('COM_JUDOWNLOAD_SEARCH_CATEGORIES'); ?></option>
			</select>
			<?php
			if(JUDLPROVERSION)
			{ ?>
				<a class="btn btn-mini"
				   href="index.php?option=com_judownload&amp;task=advsearch.search"><?php echo JText::_('COM_JUDOWNLOAD_SEARCH_MORE'); ?></a>
			<?php
			} ?>
		</div>
	</form>
</div>
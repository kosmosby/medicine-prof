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
<div class="category-tree">
	<?php echo JUDownloadHelper::getCategoryDTree(); ?>
</div>

<div id="judl-search" style="margin-top: 15px;">
	<form name="search-form" id="search-form" action="index.php?option=com_judownload" method="POST">
		<fieldset>
			<div class="input-append">
				<input type="text" name="searchword" class="input-medium" size="20" maxlength="250" value="<?php echo $this->searchword; ?>" placeholder="<?php echo JText::_('COM_JUDOWNLOAD_SEARCH'); ?>" />
				<button type="submit" name="submit_simple_search" class="btn"><i class="icon-search"></i>&nbsp;</button>
			</div>
		</fieldset>

		<div class="clearfix">
			<select name="view" id="search-in" class="input-medium">
				<option value="searchdocuments"><?php echo JText::_('COM_JUDOWNLOAD_SEARCH_DOCUMENTS'); ?></option>
				<option value="searchcategories" selected><?php echo JText::_('COM_JUDOWNLOAD_SEARCH_CATEGORIES'); ?></option>
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
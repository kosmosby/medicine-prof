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
<h3 class="doc-related-caption">
	<?php echo JText::_('COM_JUDOWNLOAD_RELATED_DOCUMENTS'); ?>
</h3>

<div class="related-docs clearfix">
	<?php foreach ($this->item->related_documents AS $relatedDocument)
	{
		?>
		<div class="related-doc">
			<div class="related-doc-icon">
				<a href="<?php echo $relatedDocument->link; ?>" title="<?php echo $relatedDocument->title; ?>">
					<img src="<?php echo $relatedDocument->icon; ?>" alt="<?php echo $relatedDocument->title; ?>"/>
				</a>
			</div>
			<div class="related-doc-title">
				<a href="<?php echo $relatedDocument->link; ?>" title="<?php echo $relatedDocument->title; ?>">
					<?php echo $relatedDocument->title; ?>
				</a>
			</div>
		</div>
	<?php
	} ?>
</div>
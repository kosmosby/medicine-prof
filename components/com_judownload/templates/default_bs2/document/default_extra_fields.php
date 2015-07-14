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
<div class="field-box">
	<h3 class="field-box-title">
		<?php echo JText::_('COM_JUDOWNLOAD_INFORMATION'); ?>
	</h3>
	<ul class="fields list-striped">
		<?php
		// Ignore these fields from summary fields
		$ignoredFields = array("title", "description", "icon", "publish_up", "created_by", "featured", "cat_id",
								"downloads", "comments", "rating", "hits", "tags");
		$i = 1;
		foreach ($this->item->fields AS $field)
		{
			if (is_object($field) && !in_array($field->field_name, $ignoredFields) && $field->canView())
			{
				$row = 'odd';
				if ($i % 2)
				{
					$row = 'even';
				}
				$i++;
				?>
				<li class="field field-<?php echo $field->id . ' ' . $row; ?>">
					<?php
					if($field->hasCaption())
					{?>
						<div class="caption"><?php echo $field->getCaption(); ?></div>
					<?php
					}
					?>
					<div class="value">
						<?php echo $field->getDisplayPrefixText() . ' ' . $field->getOutput(array("view" => "details", "template" => $this->template)) . ' ' . $field->getDisplaySuffixText(); ?>
					</div>
				</li>
			<?php
			}
		} ?>
	</ul>
</div>
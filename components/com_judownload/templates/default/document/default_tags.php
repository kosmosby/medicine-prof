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

if (isset($this->item->fields['tags']) && $this->item->fields['tags']->canView())
{
	?>
	<div class="doc-tags">
		<div class="caption">
			<?php echo $this->item->fields['tags']->getCaption(); ?>
		</div>
		<div class="value clearfix">
			<?php
			echo $this->item->fields['tags']->getDisplayPrefixText() . ' ' .
				$this->item->fields['tags']->getOutput(array("view" => "details", "template" => $this->template)) . ' ' .
				$this->item->fields['tags']->getDisplaySuffixText();
			?>
		</div>
	</div>
<?php
} ?>
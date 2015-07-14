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

// Row counter
$this->row_counter = 0;

// Column counter
$this->col_counter = 0;
?>
<form name="judl-form-collections" id="judl-form-collections" class="judl-form-collections" method="post"
      action="#">
	<?php
		// Load header
		echo $this->loadTemplate('header');
	?>

	<?php
		$collection_list_attr = '';
		if ($this->allow_user_select_view_mode)
		{
			$collection_list_attr .= 'id="view-mode-switch" ';
		}

		$collection_list_attr .= 'class="judl-collection-list ' . ($this->view_mode == 2 ? 'judl-view-grid' : 'judl-view-list') . '"';
	?>
	<!-- Collection list -->
	<div <?php echo $collection_list_attr; ?>>
		<div
			class="judl-collection-row judl-collection-row-<?php echo $this->row_counter + 1; ?> <?php echo $this->collection_row_class; ?> row">
			<?php
			foreach ($this->items AS $index => $item)
			{
				$this->index = $index;
				$this->item  = $item;
				echo $this->loadTemplate('collection');
			}
			?>
		</div>
	</div>

	<?php
		// Load footer
		echo $this->loadTemplate('footer');
	?>

	<input type="hidden" id="token" value="<?php echo $this->token ?>">
</form>
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
<form name="judl-form-documents" id="judl-form-documents" class="judl-form-documents" method="post" action="#">
	<?php
		// Load header
		echo $this->loadTemplate('header');
	?>

	<?php
		$doc_list_attr = '';
		if ($this->allow_user_select_view_mode)
		{
			$doc_list_attr .= 'id="view-mode-switch" ';
		}

		$doc_list_attr .= 'class="judl-doc-list ' . ($this->view_mode == 2 ? 'judl-view-grid' : 'judl-view-list') . '"';
	?>
	<!-- Document list -->
	<div <?php echo $doc_list_attr; ?>>
		<div
			class="judl-doc-row <?php echo $this->document_row_class; ?> judl-doc-row-<?php echo $this->row_counter + 1; ?> row">
		<?php
		foreach ($this->items AS $index => $item)
		{
			$this->index = $index;
			$this->item  = $item;
			echo $this->loadTemplate('document');
		}
		?>
		</div>
	</div>

	<?php
		// Load footer
		echo $this->loadTemplate('footer');
	?>
</form>

<?php
	// Load modal password form outside of document list form
	echo $this->loadTemplate('password_form');
?>
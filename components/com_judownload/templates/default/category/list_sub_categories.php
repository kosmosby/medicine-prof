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

// Number row
$row_counter = 0;
// Number column
$col_counter = 0;

?>
<div class="judl-subcat clearfix">
	<h2 class="subcat-title"><?php echo JText::_('COM_JUDOWNLOAD_SUBCATEGORIES'); ?></h2>

	<div
		class="subcat-row subcat-row-<?php echo $row_counter + 1; ?> <?php echo $this->subcategory_row_class; ?> row">
		<?php
		foreach ($this->subcategories AS $key => $subCategory)
		{
			$col_counter++;
			$class_item = "subcat-item subcat-col-" . $col_counter;
			if ($subCategory->featured)
			{
				$class_item .= " featured";
			}
			$class_item .= $this->subcategory_column_class ? " " . $this->subcategory_column_class : "";
			$class_item .= ' col-md-' . $this->subcategory_bootstrap_columns[$col_counter - 1];

			?>
			<div class="<?php echo $class_item; ?> <?php if ($subCategory->featured)
			{
				echo 'cat-featured';
			} ?>">
				<?php if ($this->params->get('subcategory_show_intro_image', 1) && !empty($subCategory->images->intro_image_src))
				{
					?>
					<div class="cat-image">
						<a href="<?php echo $subCategory->link; ?>">
							<img src="<?php echo $subCategory->images->intro_image_src; ?>"
							     width="<?php echo $subCategory->images->intro_image_width; ?>px"
							     height="<?php echo $subCategory->images->intro_image_height; ?>px"
							     title="<?php echo $subCategory->images->intro_image_caption; ?>"
							     alt="<?php echo $subCategory->images->intro_image_alt; ?>"/>
						</a>
					</div>
				<?php
				} ?>

				<h3 class="cat-title">
					<a href="<?php echo $subCategory->link; ?>"><?php echo $subCategory->title; ?></a>
					<?php
					if($this->params->get('show_total_subcats_of_subcat',0) && !$this->params->get('show_total_docs_of_subcat',0)){
						?>
						<small><?php echo '(<span class="subcat-count"><span>' . $subCategory->total_nested_categories . '</span> ' . JText::_('COM_JUDOWNLOAD_SUB_CATEGORIES') . '</span>)'; ?></small>
					<?php
					}elseif(!$this->params->get('show_total_subcats_of_subcat',0) && $this->params->get('show_total_docs_of_subcat',0)){
						?>
						<small><?php echo '(<span class="document-count"><span>' . $subCategory->total_documents . '</span> ' . JText::_('COM_JUDOWNLOAD_DOCUMENTS') . '</span>)'; ?></small>
					<?php
					}elseif($this->params->get('show_total_subcats_of_subcat',0) && $this->params->get('show_total_docs_of_subcat',0)){
						?>
						<small><?php echo '(<span class="subcat-count"><span>' . $subCategory->total_nested_categories . '</span> ' . JText::_('COM_JUDOWNLOAD_SUB_CATEGORIES') . '</span> / <span class="document-count"><span>' . $subCategory->total_documents . '</span> ' . JText::_('COM_JUDOWNLOAD_DOCUMENTS') . '</span>)'; ?></small>
					<?php
					}
					?>
				</h3>

				<?php if ($subCategory->introtext)
				{
					?>
					<div class="cat-desc">
						<?php echo $subCategory->introtext; ?>
					</div>
				<?php
				} ?>
			</div>
			<?php
			if ((($col_counter % $this->subcategory_columns) == 0) && (($key + 1) < count($this->subcategories)))
			{
			$row_counter += 1;
			$col_counter = 0;
			?>
	</div>

	<div class="subcat-row subcat-row-<?php echo $row_counter; ?> <?php echo $this->subcategory_row_class; ?> row">
			<?php
			}
		}
		?>
	</div>
</div>
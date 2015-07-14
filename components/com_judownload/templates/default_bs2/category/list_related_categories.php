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
<div class="judl-relcat clearfix">
	<h2 class="relcat-title"><?php echo JText::_('COM_JUDOWNLOAD_RELATED_CATEGORIES'); ?></h2>

	<div
		class="relcat-row <?php echo $this->related_category_row_class; ?> relcat-row-<?php echo $row_counter + 1; ?> row-fluid">
		<?php
		foreach ($this->related_cats AS $key => $relatedCategory)
		{
			$col_counter++;
			$class_item = "relcat-item relcat-col-" . $col_counter;
			if ($relatedCategory->featured)
			{
				$class_item .= " featured";
			}
			$class_item .= $this->related_category_column_class ? " " . $this->related_category_column_class : "";
			$class_item .= ' span' . $this->related_category_bootstrap_columns[$col_counter - 1];
			?>
			<div class="<?php echo $class_item; ?>">
				<?php if ($this->params->get('related_category_show_intro_image', 1) && !empty($relatedCategory->images->intro_image_src))
				{
					?>
					<div class="cat-image">
						<a href="<?php echo $relatedCategory->link; ?>">
							<img src="<?php echo $relatedCategory->images->intro_image_src; ?>"
							     width="<?php echo $relatedCategory->images->intro_image_width; ?>px"
							     height="<?php echo $relatedCategory->images->intro_image_height; ?>px"
							     title="<?php echo $relatedCategory->images->intro_image_caption; ?>"
							     alt="<?php echo $relatedCategory->images->intro_image_alt; ?>"/>
						</a>
					</div>
				<?php
				} ?>

				<h3 class="cat-title">
					<a href="<?php echo $relatedCategory->link; ?>"><?php echo $relatedCategory->title; ?></a>
					<?php
					if($this->params->get('show_total_subcats_of_relcat',0) && !$this->params->get('show_total_docs_of_relcat',0)){
					?>
						<small><?php echo '(<span class="subcat-count"><span>' . $relatedCategory->total_nested_categories . '</span> ' . JText::_('COM_JUDOWNLOAD_SUB_CATEGORIES') . '</span>)'; ?></small>
					<?php
					}elseif(!$this->params->get('show_total_subcats_of_relcat',0) && $this->params->get('show_total_docs_of_relcat',0)){
					?>
						<small><?php echo '(<span class="document-count"><span>' . $relatedCategory->total_documents . '</span> ' . JText::_('COM_JUDOWNLOAD_DOCUMENTS') . '</span>)'; ?></small>
					<?php
					}elseif($this->params->get('show_total_subcats_of_relcat',0) && $this->params->get('show_total_docs_of_relcat',0)){
					?>
						<small><?php echo '(<span class="subcat-count"><span>' . $relatedCategory->total_nested_categories . '</span> ' . JText::_('COM_JUDOWNLOAD_SUB_CATEGORIES') . '</span> / <span class="document-count"><span>' . $relatedCategory->total_documents . '</span> ' . JText::_('COM_JUDOWNLOAD_DOCUMENTS') . '</span>)'; ?></small>
					<?php
					}
					?>
				</h3>

				<?php if ($relatedCategory->introtext)
				{
					?>
					<div class="cat-desc">
						<?php echo $relatedCategory->introtext; ?>
					</div>
				<?php
				} ?>
			</div>

			<?php
			if (($col_counter % $this->related_category_columns) == 0 && ($key + 1) < count($this->related_cats))
			{
			$row_counter += 1;
			$col_counter = 0;
			?>
	</div>
	<div class="relcat-row relcat-row-<?php echo $row_counter; ?> <?php echo $this->related_category_row_class; ?> row-fluid">
			<?php
			}
		}
		?>
	</div>
</div>
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

<div id="judl-container" class="jubootstrap component judl-container view-categories <?php echo $this->pageclass_sfx; ?>">
	<?php if ($this->params->get('show_page_heading'))
	{
		?>
		<h1><?php echo $this->escape($this->params->get('page_heading')); ?></h1>
	<?php
	} ?>

	<?php if ($this->params->get('all_categories_show_category_title', 1))
	{
		?>
		<h2 class="cat-title"><?php echo $this->category->title; ?></h2>
	<?php
	} ?>

	<?php
	// Total category level 1 (virtual level)
	$totalCategoryLevel1 = 0;
	foreach ($this->all_categories AS $category)
	{
		if ($category->level == ($this->category->level + 1))
		{
			$totalCategoryLevel1++;
		}
	}

	// Get total columns
	$columns = (int) $this->params->get('all_categories_columns', 2);
	if (!is_numeric($columns) || ($columns <= 0))
	{
		$columns = 1;
	}
	$this->subcategory_bootstrap_columns = JUDownloadFrontHelper::getBootstrapColumns($columns);

	// Row and column class
	$rows_class = htmlspecialchars($this->params->get('all_categories_row_class', ''));
	$columns_class = htmlspecialchars($this->params->get('all_categories_column_class', ''));

	// Index row
	$indexRow = 0;

	// Index column
	$indexColumn = 0;

	// Index element in array
	$indexElementArray = 0;
	?>
	<div
		class="categories-row <?php echo $rows_class; ?> categories-row-<?php echo $indexRow + 1; ?> clearfix row">
		<?php
		foreach ($this->all_categories AS $category)
		{
		if ($category->level == ($this->category->level + 1))
		{
		$indexElementArray++;
		?>
		<div
			class="categories-col <?php echo $columns_class; ?> categories-col-<?php echo $indexColumn + 1; ?> col-md-<?php echo $this->subcategory_bootstrap_columns[$indexColumn]; ?>">

			<div class="panel panel-default">
				<div class="panel-heading category-title">
					<a href="<?php echo JRoute::_(JUDownloadHelperRoute::getCategoryRoute($category->id)); ?>">
						<?php echo $category->title; ?></a>
				</div>

				<?php
				if ($category->total_childs > 0)
				{
					$this->new_parent_id = $category->id;
					echo $this->loadTemplate('categories');
				}
				else
				{
					?>
					<div class="panel-body">

					</div>
				<?php
				}
				?>
			</div>
			<!-- /.panel -->
		</div>
		<!-- /.categories-col -->
		<?php
		$indexColumn++;
		if ((($indexColumn % $columns) == 0) && ($indexElementArray < $totalCategoryLevel1))
		{
		$indexRow++;
		// Reset index column
		$indexColumn = 0;
		?>
	</div>
	<div class="categories-row <?php echo $rows_class; ?> categories-row-<?php echo $indexRow; ?> clearfix row">
		<?php
		} // end if column
		} // end if level + 1
		} // end foreach
		?>
	</div>
	<!-- /.categories-row -->
</div> <!-- /#judl-container -->

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
JHtml::_('behavior.modal');

$app = JFactory::getApplication();
$db = JFactory::getDbo();
$user = JFactory::getUser();
$userId = $user->id;
$rootCat = JUDownloadFrontHelperCategory::getRootCategory();
$cat_id = $app->input->getInt('cat_id', $rootCat->id);
$listOrder_cat = $this->escape($this->state->get('filter.ordering_cat'));
$listDirn_cat = $this->escape($this->state->get('filter.direction_cat'));
$search_cat = $this->escape($this->state->get('filter.search_cat'));
$saveOrder_cat = ($listOrder_cat == 'lft' && $this->canDoCat->get('judl.category.edit.state'));
$model = $this->getModel();
$list_cat = $model->getListCategory($cat_id, $listOrder_cat, $listDirn_cat);
$ordering = ($listOrder_cat == 'lft');
$this->ordering = array();
$intro_image_path = JUri::root(true) . "/" . JUDownloadFrontHelper::getDirectory("category_intro_image_directory", "media/com_judownload/images/category/intro/");
$detail_image_path = JUri::root(true) . "/" . JUDownloadFrontHelper::getDirectory("category_detail_image_directory", "media/com_judownload/images/category/detail/");
foreach ($list_cat AS $cat)
{
	$this->ordering[] = $cat->id;
}
$originalOrders = array();
?>
<div class="clearfix">
	<div class="input-append pull-left">
		<label for="filter_search" class="filter-search-lbl element-invisible"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
		<input type="text" size="40" name="filter_search_cat" id="filter_search_cat"
			placeholder="<?php echo JText::_('COM_JUDOWNLOAD_SEARCH_BY_CATEGORY_NAME'); ?>"
			value="<?php echo $this->escape($this->state->get('filter.search_cat')); ?>"
			title="<?php echo JText::_('COM_JUDOWNLOAD_SEARCH_BY_CATEGORY_NAME'); ?>" />
		<button class="btn" type="submit" title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>
		<button class="btn" type="button"
			title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"
			onclick="document.id('filter_search_cat').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
	</div>

	<div class="pull-right">
		<select title="<?php echo JText::_('COM_JUDOWNLOAD_SORT_BY'); ?>" name="sortTable_cat" id="sortTable_cat" class="input-medium" onchange="Joomla.orderTable('cat')">
			<option value=""><?php echo JText::_('COM_JUDOWNLOAD_SORT_BY'); ?></option>
			<?php echo JHtml::_('select.options', $model->cat_dropdown_fields_selected, 'value', 'text', $listOrder_cat); ?>
		</select>
		<select title="<?php echo JText::_('JFIELD_ORDERING_DESC'); ?>" name="directionTable_cat" id="directionTable_cat"
			class="input-medium" onchange="Joomla.orderTable('cat')">
			<option value=""><?php echo JText::_('JFIELD_ORDERING_DESC'); ?></option>
			<option value="asc"
				<?php if ($listDirn_cat == 'asc')
				{
					echo 'selected="selected"';
				} ?>>
				<?php echo JText::_('COM_JUDOWNLOAD_ASC'); ?></option>
			<option value="desc"
				<?php if ($listDirn_cat == 'desc')
				{
					echo 'selected="selected"';
				} ?>>
				<?php echo JText::_('COM_JUDOWNLOAD_DESC'); ?></option>
		</select>
	</div>
</div>

<div class="clearfix">
	<div class="custom-layout pull-left">
		<select multiple="multiple" size="5" id="category-fields" class="pull-left" name="category_fields[]" title="<?php echo JText::_('COM_JUDOWNLOAD_DISPLAYED_FIELDS'); ?>">
			<?php echo JHtml::_('select.options', $model->cat_dropdown_fields, 'value', 'text', $model->cat_fields_use); ?>
		</select>

		<div class="pull-left">
			<button type="button" class="btn btn-mini" onclick="document.getElementById('apply_cat_layout').value = 1; Joomla.submitform();">
				<i class="icon-ok"></i> <?php echo JText::_('COM_JUDOWNLOAD_APPLY'); ?>
			</button>
			<button type="button" class="btn btn-mini" onclick="document.getElementById('reset_cat_layout').value = 1; Joomla.submitform();">
				<i class="icon-undo"></i> <?php echo JText::_('COM_JUDOWNLOAD_RESET'); ?>
			</button>
			<input type="hidden" name="apply_cat_layout" value="0" id="apply_cat_layout" />
			<input type="hidden" name="reset_cat_layout" value="0" id="reset_cat_layout" />
		</div>
	</div>

	<?php if ($this->groupCanDoCatManage)
	{
		?>
		<div class="fastadd pull-right">
			<a rel="{handler: 'iframe', size: {x: 400, y: 450}, onClose: function() {}}" href="index.php?option=com_judownload&amp;view=categories&amp;layout=fastadd&amp;cat_id=<?php echo $cat_id; ?>&amp;tmpl=component" class="modal">
				<button class="btn btn-mini"><i class="icon-new"></i> <?php echo JText::_('COM_JUDOWNLOAD_FAST_ADD'); ?>
				</button>
			</a>
		</div>
	<?php
	} ?>
</div>

<div id="cat-table-wrapper" style="overflow: auto;">
	<table class="table table-striped adminlist">
		<thead>
			<tr>
				<th style="width: 40px !important;" class="hidden-phone">
					<input type="checkbox" onclick="Joomla.checkAll(this)" title="<?php echo JText::_('COM_JUDOWNLOAD_CHECK_ALL'); ?>" value="" name="checkall-toggle" />
				</th>
				<?php
				foreach ($model->cat_dropdown_fields_selected AS $value)
				{
                    $field = $value['value'];
                    $label = $value['text'];
                    $prefixCat = "cat.";
                    switch ($field)
                    {
                        case "id":
                            echo '<th style="width: 50px !important;" class="nowrap">';
                            echo JHtml::_('grid.sort', $label, $prefixCat.$field, $listDirn_cat, $prefixCat.$listOrder_cat);
                            echo '</th>';
                            break;
                        case "title":
                            echo '<th style="min-width: 250px !important;" class="nowrap">';
                            echo JHtml::_('grid.sort', $label, $prefixCat.$field, $listDirn_cat, $prefixCat.$listOrder_cat);
                            echo '</th>';
                            break;
                        case "lft":
                            echo '<th style="min-width: 100px !important;" class="nowrap">';
                            echo JHtml::_('grid.sort', $label, $prefixCat.$field, $listDirn_cat, $prefixCat.$listOrder_cat);
                            if ($saveOrder_cat)
                            {
                                echo JHtml::_('grid.order', $list_cat, 'filesave.png', 'categories.saveorder');
                            }
                            echo '</th>';
                            break;
                        default:
                            echo '<th style="min-width: 100px !important;" class="nowrap">';
                            echo JHtml::_('grid.sort', $label, $prefixCat.$field, $listDirn_cat, $prefixCat.$listOrder_cat);
                            echo '</th>';
                            break;
                    }
				}
				?>
			</tr>
		</thead>

		<tbody>
		<?php
		foreach ($list_cat AS $i => $item) :
			$orderkey   = array_search($item->id, $this->ordering);
			$canEdit    = $user->authorise('judl.category.edit', 'com_judownload.category.' . $item->id) && $this->catGroupCanDoManage;
			$canCheckin = $user->authorise('core.manage', 'com_checkin') || $item->checked_out == $userId || $item->checked_out == 0;
			$canEditOwn = $user->authorise('judl.category.edit.own', 'com_judownload.category.' . $item->id) && $item->created_by == $userId && $this->catGroupCanDoManage;
			$canChange  = $user->authorise('judl.category.edit.state', 'com_judownload.category.' . $item->id) && $canCheckin && $this->catGroupCanDoManage;
			?>
			<tr class="row<?php echo $i % 2; ?>">
				<td class="hidden-phone">
					<input type="checkbox" onclick="Joomla.isChecked(this.checked);" value="<?php echo $item->id; ?>" name="cid[]" id="cb<?php echo $i; ?>" />
					<a href="index.php?option=com_judownload&view=listcats&cat_id=<?php echo $item->id; ?>" title="<?php echo JText::_('COM_JUDOWNLOAD_OPEN_THIS_CATEGORY'); ?>" style="float: right; margin-top: 3px;">
						<img width="18" height="18" border="0" onmouseout="this.src='<?php echo JUri::root(); ?>components/com_judownload/assets/dtree/img/folder.gif'" onmouseover="this.src='<?php echo JUri::root(); ?>components/com_judownload/assets/dtree/img/folderopen.gif'" name="img0" src="<?php echo JUri::root(); ?>components/com_judownload/assets/dtree/img/folder.gif" style="float:left" />
					</a>
				</td>
				<?php
				foreach ($model->cat_dropdown_fields_selected AS $value)
				{
					$field = $value['value'];
					$label = $value['text'];
					switch ($field)
					{
						case "title":
							echo '<td>';
							if ($item->checked_out)
							{
								echo JHtml::_('jgrid.checkedout', $i, $item->checked_out_name, $item->checked_out_time, 'categories.', $canCheckin || $user->authorise('core.manage', 'com_checkin'));
							}
							if ($canEdit || $canEditOwn)
							{
								?>
								<a href="<?php echo JRoute::_('index.php?option=com_judownload&amp;task=category.edit&amp;id=' . $item->id); ?>">
									<?php echo $item->title; ?>
								</a>
							<?php
							}
							else
							{
								echo $item->title;
							}
							?>
							<p class="<?php echo JUDownloadHelper::isJoomla3x() ? "small" : "smallsub";?>"><?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?></p>
							<?php
							echo '</td>';
							break;
						case "lft":
							echo '<td class="order">';
							if ($canChange)
							{
								if ($saveOrder_cat)
								{
									$prevNextElement = isset($list_cat[$i - 1]->parent_id) ? $list_cat[$i - 1]->parent_id : 0;
									$parentNextElement = isset($list_cat[$i + 1]->parent_id) ? $list_cat[$i + 1]->parent_id : 0;
									if ($listDirn_cat == 'asc')
									{
										?>
										<span><?php echo $this->pagination->orderUpIcon($i, ($item->parent_id == $prevNextElement), 'categories.orderup', 'JLIB_HTML_MOVE_UP', $ordering); ?></span>
										<span><?php echo $this->pagination->orderDownIcon($i, count($list_cat), ($item->parent_id == $parentNextElement), 'categories.orderdown', 'JLIB_HTML_MOVE_DOWN', $ordering); ?></span>
									<?php
									}
									elseif ($listDirn_cat == 'desc')
									{
										?>
										<span><?php echo $this->pagination->orderUpIcon($i, ($item->parent_id == $prevNextElement), 'categories.orderdown', 'JLIB_HTML_MOVE_UP', $ordering); ?></span>
										<span><?php echo $this->pagination->orderDownIcon($i, count($list_cat), ($item->parent_id == $parentNextElement), 'categories.orderup', 'JLIB_HTML_MOVE_DOWN', $ordering); ?></span>
									<?php
									}
								}
								$disabled = $saveOrder_cat ? '' : 'disabled="disabled"';
								$originalOrders[] = $orderkey + 1;
								?>
								<input type="text" name="order[]" size="5" value="<?php echo $orderkey + 1; ?>" <?php echo $disabled ?> class="input-mini" />
							<?php
							}
							else
							{
								echo $item->ordering;
							}
							echo '</td>';
							break;
						case "published":
							echo '<td>';
							echo JHtml::_('jgrid.published', $item->published, $i, 'categories.', $canChange, 'cb', $item->publish_up, $item->publish_down);
							echo '</td>';
							break;
						case "featured":
							echo '<td>';
							echo JHtml::_('judownloadadministrator.featured', $item->featured, $i, $canChange, 'categories');
							echo '</td>';
							break;
						case "rel_cats":
							echo '<td>';
							$rel_categories = $model->getRelatedCategories($item->id);
							if ($rel_categories)
							{
								echo implode(", ", $rel_categories);
							}
							echo '</td>';
							break;
						case "parent_id":
							echo '<td>';
							$_category = JUDownloadHelper::getCategoryById($item->parent_id);
							if ($_category)
							{
								echo $_category->title;
							}
							echo '</td>';
							break;
						case "intro_image":
						case "detail_image":
							echo '<td>';
							if($item->images){
								$imgObj = json_decode($item->images);
								if(isset($imgObj->$field) && $imgObj->$field){
									$image_path = $field == "intro_image" ? $intro_image_path : $detail_image_path;
									echo '<a class="modal" href="'.$image_path.$imgObj->$field.'"><img src="'.$image_path.$imgObj->$field.'" style="max-width:30px; max-height:30px" /></a>';
								}else{
									echo  ' ';
								}
							}
							echo '</td>';
							break;
						default:
							echo '<td>';
							echo $item->$field;
							echo '</td>';
							break;
					}
				}
				?>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>

	<input type="hidden" name="original_order_values" value="<?php echo implode($originalOrders, ','); ?>" />
</div>
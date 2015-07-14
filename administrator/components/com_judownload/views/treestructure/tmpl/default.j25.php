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

JHtml::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_judownload/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.multiselect');

$user = JFactory::getUser();
$userId = $user->get('id');
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
?>
<script type="text/javascript">
	Joomla.orderTable = function () {
		table = document.getElementById("sortTable");
		direction = document.getElementById("directionTable");
		order = table.options[table.selectedIndex].value;
		if (order != '<?php echo $listOrder; ?>') {
			dirn = 'asc';
		} else {
			dirn = direction.options[direction.selectedIndex].value;
		}
		Joomla.tableOrdering(order, dirn, '');
	}
</script>

<div class="jubootstrap">
	<?php echo JUDownloadHelper::getMenu(JFactory::getApplication()->input->get('view')); ?>

	<div id="iframe-help"></div>

	<form action="<?php echo JRoute::_('index.php?option=com_judownload&view=treestructure'); ?>" method="post" name="adminForm" id="adminForm">
		<fieldset id="filter-bar">
			<div class="filter-search input-append pull-left">
				<label class="filter-search-lbl element-invisible" for="filter_search"><?php echo JText::_('JSEARCH_FILTER_LABEL'); ?></label>
				<input type="text" name="filter_search" id="filter_search" class="input-medium"
					placeholder="<?php echo JText::_('COM_JUDOWNLOAD_FILTER_SEARCH'); ?>"
					value="<?php echo $this->escape($this->state->get('filter.search')); ?>"
					title="<?php echo JText::_('COM_JUDOWNLOAD_FILTER_SEARCH_DESC'); ?>" />
				<button class="btn" rel="tooltip" type="submit"
					title="<?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?>"><?php echo JText::_('JSEARCH_FILTER_SUBMIT'); ?></button>

				<button class="btn" rel="tooltip" type="button"
					title="<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>"
					onclick="document.id('filter_search').value='';this.form.submit();"><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?></button>
			</div>

			<div class="filter-select">
				<div class="pull-right hidden-phone">
                    <select name="filter_level" class="inputbox" onchange="this.form.submit()">
                        <option value=""><?php echo JText::_('JOPTION_SELECT_MAX_LEVELS');?></option>
                        <?php echo JHtml::_('select.options', $this->f_levels, 'value', 'text', $this->state->get('filter.level'));?>
                    </select>
                    <select name="filter_published" class="inputbox" onchange="this.form.submit()">
                        <option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
                        <?php echo JHtml::_('select.options', $this->published_options, 'value', 'text', $this->state->get('filter.published'), true);?>
                    </select>
                    <select name="filter_access" class="inputbox" onchange="this.form.submit()">
                        <option value=""><?php echo JText::_('JOPTION_SELECT_ACCESS');?></option>
                        <?php echo JHtml::_('select.options', JHtml::_('access.assetgroups'), 'value', 'text', $this->state->get('filter.access'));?>
                    </select>

                    <select name="filter_language" class="inputbox" onchange="this.form.submit()">
                        <option value=""><?php echo JText::_('JOPTION_SELECT_LANGUAGE');?></option>
                        <?php echo JHtml::_('select.options', JHtml::_('contentlanguage.existing', true, true), 'value', 'text', $this->state->get('filter.language'));?>
                    </select>
				</div>
			</div>
		</fieldset>

		<div class="clearfix"></div>

		<table class="table table-striped adminlist">
			<thead>
			<tr>
				<th style="width:2%" class="center hidden-phone">
					<input type="checkbox" onclick="Joomla.checkAll(this)" title="<?php echo JText::_('COM_JUDOWNLOAD_CHECK_ALL'); ?>" value="" name="checkall-toggle" />
				</th>
				<th>
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_TITLE', 'tbl_cat.title', $listDirn, $listOrder); ?>
				</th>
				<th width="1%" class="center">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_PUBLISHED', 'tbl_cat.published', $listDirn, $listOrder); ?>
				</th>
				<th width="5%">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_GROUP_SETTING', 'tbl_cat.selected_fieldgroup', $listDirn, $listOrder); ?>
				</th>
				<th width="5%">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_GROUP', 'tbl_cat.fieldgroup_id', $listDirn, $listOrder); ?>
				</th>
				<?php
				if(JUDownloadHelper::hasMultiRating())
				{
					?>
					<th width="5%">
						<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_CRITERIA_GROUP_SETTING', 'tbl_cat.selected_criteriagroup', $listDirn, $listOrder); ?>
					</th>
					<th width="5%">
						<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_CRITERIA_GROUP', 'tbl_cat.criteriagroup_id', $listDirn, $listOrder); ?>
					</th>
				<?php
				}
				?>
				<th width="5%">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_STYLE_SETTING', 'tbl_cat.style_id', $listDirn, $listOrder); ?>
				</th>
				<th width="5%">
					<?php echo JText::_('COM_JUDOWNLOAD_STYLE'); ?>
				</th>
				<th width="5%">
					<?php echo JText::_('COM_JUDOWNLOAD_TEMPLATE'); ?>
				</th>
				<th width="5%">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_ACCESS', 'tbl_cat.access', $listDirn, $listOrder); ?>
				</th>
				<th width="1%">
					<?php echo JHtml::_('grid.sort', 'COM_JUDOWNLOAD_FIELD_ID', 'tbl_cat.id', $listDirn, $listOrder); ?>
				</th>
			</tr>
			</thead>

			<tfoot>
			<tr>
				<td colspan="15"><?php echo $this->pagination->getListFooter(); ?></td>
			</tr>
			</tfoot>

			<tbody>
			<?php

			foreach ($this->items AS $i => $item):
				$canEdit    = $user->authorise('core.edit',       'com_judownload');
				$canCheckin = $user->authorise('core.manage',     'com_checkin') || $item->checked_out == $user->id || $item->checked_out == 0;
				$canEditOwn = $user->authorise('core.edit.own',   'com_judownload') && $item->created_by == $user->id;
				$canChange  = $user->authorise('core.edit.state', 'com_judownload') && $canCheckin;
                $canChange = ($item->level == 0) ? false : $canChange;
				?>
				<tr class="row<?php echo $i % 2; ?>">
					<td class="center hidden-phone">
						<?php echo JHtml::_('grid.id', $i, $item->id); ?>
					</td>
					<td class="">
						<?php if ($item->checked_out) : ?>
							<?php
							echo JHtml::_('jgrid.checkedout', $i, $item->checked_out_name, $item->checked_out_time, 'categories.', $canCheckin || $user->authorise('core.manage', 'com_checkin'));
							?>
						<?php endif; ?>
						<?php if (($canEdit || $canEditOwn) && $item->level > 0)
						{
							?>
                            <?php echo str_repeat('<span class="gi">|&mdash;</span>', $item->level) ?>
							<a href="<?php echo JRoute::_('index.php?option=com_judownload&amp;task=category.edit&amp;id=' . $item->id); ?>">
								<?php echo $item->title; ?>
							</a>
						<?php
						}
						else
						{
							?>
                            <?php echo str_repeat('<span class="gi">|&mdash;</span>', $item->level) ?>
							<?php echo $item->title; ?>
						<?php
						} ?>

                        <?php
                        if ($item->level == 1 && JUDownloadHelperRoute::findItemId(array('tree' => array($item->id))))
                        {
                            ?>
                            <span class="btn btn-mini"><i class="icon-home"></i></span>
                        <?php
                        }
                        if ($item->level == 1 && $item->config_params)
                        {
                            ?>
                            <span class="btn btn-mini"><i class="icon-cog hasTooltip" title="<?php echo JText::_('COM_JUDOWNLOAD_OVERRIDE_CONFIG'); ?>"></i></span>
                        <?php
                        }
                        ?>

						<p class="smallsub"><?php echo JText::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($item->alias)); ?></p>
					</td>
					<td class="center">
						<?php echo JHtml::_('jgrid.published', $item->published, $i, 'categories.', $canChange, 'cb'); ?>
					</td>

                    <td>
                        <?php
                        if($item->selected_fieldgroup > 0)
                        {
                            ?>
                            <a href="<?php echo JRoute::_('index.php?option=com_judownload&amp;task=fieldgroup.edit&amp;id=' . $item->selected_fieldgroup); ?>">
                                <?php echo $item->selected_fieldgroup_title; ?>
                            </a>
                        <?php
                        }
                        else
                        {
                            echo $item->selected_fieldgroup_title;
                        }
                        ?>
                    </td>
                    <td>
                        <?php
                        if($item->fieldgroup_id > 0)
                        { ?>
                            <a href="<?php echo JRoute::_('index.php?option=com_judownload&amp;task=fieldgroup.edit&amp;id=' . $item->fieldgroup_id); ?>">
                                <?php echo $item->field_group_title; ?>
                            </a>
                        <?php
                        }
                        else
                        {
                            echo $item->field_group_title;
                        }
                        ?>
                    </td>
					<?php
					if(JUDownloadHelper::hasMultiRating())
					{
						?>
						<td>
							<?php
							if ($item->selected_criteriagroup > 0)
							{
								?>
								<a href="<?php echo JRoute::_('index.php?option=com_judownload&amp;task=criteriagroup.edit&amp;id=' . $item->selected_criteriagroup); ?>">
									<?php echo $item->selected_criteriagroup_title; ?>
								</a>
							<?php
							}
							else
							{
								echo $item->selected_criteriagroup_title;
							}
							?>
						</td>
						<td>
							<?php
							if ($item->criteriagroup_id > 0)
							{ ?>
								<a href="<?php echo JRoute::_('index.php?option=com_judownload&amp;task=criteriagroup.edit&amp;id=' . $item->selected_criteriagroup); ?>">
									<?php echo $item->criteria_group_title; ?>
								</a>
							<?php
							}
							else
							{
								echo $item->criteria_group_title;
							}
							?>
						</td>
					<?php
					}
					?>
                    <td>
                        <?php
                        echo $item->selected_style_title;
                        ?>
                    </td>
                    <td>
                        <a href="<?php echo JRoute::_('index.php?option=com_judownload&amp;task=style.edit&amp;id=' . $item->real_style_id); ?>">
                            <?php echo $item->style_title; ?></a>
                    </td>
                    <td>
                        <a href="<?php echo JRoute::_('index.php?option=com_judownload&amp;task=template.edit&amp;id=' . $item->template_id . '&amp;file=' . base64_encode('home')); ?>">
                            <?php echo $item->template_title; ?></a>
                    </td>
                    <td>
                        <?php echo $item->access_level; ?>
                    </td>
                    <td>
                        <?php echo $item->id; ?>
                    </td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>

		<div>
			<input type="hidden" name="task" value="" />
			<input type="hidden" name="boxchecked" value="0" />
			<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
			<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>
</div>